#!/usr/bin/env php
<?php

/**
 * A loopback TLS terminator for the per-build QA instance.
 *
 *   php ci/qa/tls-proxy.php <listen-port> <upstream-port> <cert.pem> [pid-file]
 *
 * WHY THIS EXISTS
 *
 * app/Providers/AppServiceProvider.php does this unconditionally, on every boot:
 *
 *     URL::forceScheme('https');
 *     $this->app['request']->server->set('HTTPS', true);
 *
 * So every URL the application generates is https, whatever scheme the request
 * arrived on. `php artisan serve` speaks only http. Point a browser at the plain
 * port and the HTML comes back referencing https://127.0.0.1:<port>/... for every
 * stylesheet, entry script and redirect — against a port with no TLS. Chrome
 * reports ERR_CONNECTION_CLOSED for each one, Vue never boots, and a Dusk test
 * waiting on a Vue-rendered selector times out looking like a licence failure.
 * That is exactly how license-verify failed: 20s waiting for #first, with the
 * browser console full of ERR_CONNECTION_CLOSED on https asset URLs.
 *
 * The rest of the harness already expects TLS — probes honour PROBE_INSECURE=1
 * (curl -k), DuskTestCase passes --ignore-certificate-errors and
 * --allow-insecure-localhost, and the tests call bypassSslWarning(). Serving
 * plain http was the one piece that disagreed.
 *
 * It must listen on the SAME port the application advertises in APP_URL,
 * because that is the port baked into every generated URL. The application
 * itself moves to a neighbouring port and only this proxy talks to it.
 *
 * Deliberately dependency-free: no socat, stunnel, nginx or caddy, none of which
 * is installed on the QA box or guaranteed on a Jenkins agent. PHP with openssl
 * is present by definition — it is what runs the application.
 *
 * WHY IT IS WRITTEN THIS CAREFULLY
 *
 * The first version copied bytes with a bare `fwrite($peer, fread($sock, 65536))`
 * and accepted connections off an `ssl://` listener. Both are wrong the moment
 * more than one transfer is in flight, and both produced findings that looked
 * like application defects:
 *
 *   Discarded short writes. Both sockets are non-blocking, so fwrite() writes
 *   only what fits in the socket and TLS buffers right now and RETURNS A SHORT
 *   COUNT. The remainder was thrown away. Small pages never noticed. The panel
 *   loads chunks of 500kB to 3.6MB, so its responses arrived truncated: Chrome
 *   reported "Failed to fetch dynamically imported module" ~56 times followed by
 *   ERR_TOO_MANY_RETRIES, and UI-05 failed as a blocking defect against a PR that
 *   had nothing to do with it. Sequential curl never reproduced it — one transfer
 *   at a time never fills the buffer. Every byte is now held in a per-direction
 *   queue and retried on write-readiness until the socket accepts it.
 *
 *   A blocking handshake inside the event loop. stream_socket_accept() on an
 *   ssl:// listener performs the TLS handshake inline, blocking the whole process
 *   for up to its timeout. A browser opens six connections at once, so five of
 *   them waited on the first one's handshake. The listener is now plain tcp:// and
 *   the handshake runs non-blocking through the same select loop as everything
 *   else, one slice at a time.
 *
 * Backpressure matters for the same reason: without it, a fast upstream on a
 * 3.6MB chunk queues the whole file in this process while the client drains it
 * slowly. A socket is left out of the read set while the queue for its peer is
 * over the high-water mark, so the kernel applies the backpressure for us.
 */
$listenPort = (int) ($argv[1] ?? 0);
$upstreamPort = (int) ($argv[2] ?? 0);
$certFile = $argv[3] ?? '';
$pidFile = $argv[4] ?? '';

if (!$listenPort || !$upstreamPort || !is_file($certFile)) {
    fwrite(STDERR, "usage: tls-proxy.php <listen-port> <upstream-port> <cert.pem> [pid-file]\n");
    exit(2);
}

/** Read at most this much from a socket per pass. */
const CHUNK = 65536;

/**
 * Stop reading from a socket while more than this is queued for its peer. One
 * megabyte is comfortably more than a TLS record and far less than the largest
 * asset, so a big chunk streams in pieces instead of being buffered whole.
 */
const HIGH_WATER = 1048576;

/** A handshake that has not finished by then is a client that went away. */
const HANDSHAKE_TIMEOUT = 15.0;

$ctx = stream_context_create(['ssl' => [
    'local_cert'          => $certFile,
    'allow_self_signed'   => true,
    'verify_peer'         => false,
    'verify_peer_name'    => false,
    // A test harness that fails because of a protocol-version mismatch would be
    // indistinguishable from the application being broken.
    'crypto_method'       => STREAM_CRYPTO_METHOD_TLS_SERVER,
    'disable_compression' => true,
]]);

// tcp://, not ssl://. Accepted sockets inherit this context, so enable_crypto
// below still finds local_cert — but the handshake happens where we can drive it
// without blocking the loop. See the header.
$listener = @stream_socket_server(
    "tcp://127.0.0.1:{$listenPort}",
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    $ctx
);

if (!$listener) {
    fwrite(STDERR, "tls-proxy: cannot listen on {$listenPort}: {$errstr} ({$errno})\n");
    exit(1);
}
stream_set_blocking($listener, false);

if ($pidFile !== '') {
    file_put_contents($pidFile, (string) getmypid()."\n");
}

fwrite(STDERR, "tls-proxy: https://127.0.0.1:{$listenPort} -> http://127.0.0.1:{$upstreamPort}\n");

/**
 * One entry per connection pair:
 *
 *   client/upstream  the two sockets
 *   toClient         bytes read from upstream, not yet accepted by the client
 *   toUpstream       bytes read from the client, not yet accepted by upstream
 *   clientEof        the client has stopped sending
 *   upstreamEof      upstream has stopped sending — the response is complete
 *   tls              the handshake has finished
 *   deadline         when to give up on an unfinished handshake
 */
$pairs = [];
$nextId = 0;

function closePair(array &$pairs, int $id): void
{
    if (!isset($pairs[$id])) {
        return;
    }
    foreach (['client', 'upstream'] as $side) {
        if (is_resource($pairs[$id][$side])) {
            @fclose($pairs[$id][$side]);
        }
    }
    unset($pairs[$id]);
}

/**
 * Write as much of the queue as the socket will take and keep the rest. A short
 * write is the normal case on a non-blocking socket, not an error — losing the
 * remainder is what truncated every large asset in the first version.
 *
 * Returns false only when the socket is genuinely gone.
 */
function drain(&$queue, $sock): bool
{
    while ($queue !== '') {
        $written = @fwrite($sock, $queue);
        if ($written === false) {
            return false;
        }
        if ($written === 0) {
            // Buffer full: wait for write-readiness and resume from here.
            return true;
        }
        $queue = substr($queue, $written);
    }

    return true;
}

while (true) {
    $read = [$listener];
    $write = [];

    $now = microtime(true);

    foreach ($pairs as $id => $pair) {
        if (!$pair['tls']) {
            if ($now > $pair['deadline']) {
                closePair($pairs, $id);
                continue;
            }
            // OpenSSL may want to read or to write at any point in a handshake.
            $read[] = $pair['client'];
            $write[] = $pair['client'];
            continue;
        }

        // Read only while the queue for the other side has room. That is the
        // backpressure: the kernel stops acknowledging data we are not ready for.
        if (!$pair['clientEof'] && strlen($pair['toUpstream']) < HIGH_WATER) {
            $read[] = $pair['client'];
        }
        if (!$pair['upstreamEof'] && strlen($pair['toClient']) < HIGH_WATER) {
            $read[] = $pair['upstream'];
        }
        if ($pair['toClient'] !== '') {
            $write[] = $pair['client'];
        }
        if ($pair['toUpstream'] !== '') {
            $write[] = $pair['upstream'];
        }
    }

    $except = [];
    // A timeout rather than a block, so handshake deadlines are still reaped when
    // nothing at all is happening.
    if (@stream_select($read, $write, $except, 1) === false) {
        continue;
    }

    // stream_select rewrites its arrays, so index the results by resource id and
    // then walk the pairs — a socket can be both readable and writable in one pass.
    $readable = $writable = [];
    foreach ($read as $sock) {
        $readable[(int) $sock] = true;
    }
    foreach ($write as $sock) {
        $writable[(int) $sock] = true;
    }

    if (isset($readable[(int) $listener])) {
        // Accept every connection queued in this pass, not just one: a browser
        // opens six at a time and the rest would wait a whole select interval.
        while (($client = @stream_socket_accept($listener, 0)) !== false) {
            $upstream = @stream_socket_client(
                "tcp://127.0.0.1:{$upstreamPort}",
                $uerrno,
                $uerrstr,
                5
            );
            if (!$upstream) {
                fwrite(STDERR, "tls-proxy: upstream {$upstreamPort} refused: {$uerrstr}\n");
                @fclose($client);
                continue;
            }
            stream_set_blocking($client, false);
            stream_set_blocking($upstream, false);
            $pairs[$nextId++] = [
                'client'      => $client,
                'upstream'    => $upstream,
                'toClient'    => '',
                'toUpstream'  => '',
                'clientEof'   => false,
                'upstreamEof' => false,
                'tls'         => false,
                'deadline'    => microtime(true) + HANDSHAKE_TIMEOUT,
            ];
        }
    }

    foreach ($pairs as $id => $pair) {
        $client = $pair['client'];
        $upstream = $pair['upstream'];
        $cid = (int) $client;
        $uid = (int) $upstream;

        // ---- handshake -----------------------------------------------------
        if (!$pair['tls']) {
            if (!isset($readable[$cid]) && !isset($writable[$cid])) {
                continue;
            }
            $ok = @stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_TLS_SERVER);
            if ($ok === true) {
                $pairs[$id]['tls'] = true;
            } elseif ($ok === false) {
                // A browser probing TLS and hanging up mid-handshake is normal
                // traffic, not something to log on every connection.
                closePair($pairs, $id);
            }
            // 0 means "needs more I/O" — leave it for the next pass.
            continue;
        }

        // ---- reads ---------------------------------------------------------
        if (isset($readable[$cid]) && !$pair['clientEof']) {
            $data = @fread($client, CHUNK);
            if ($data === false || ($data === '' && feof($client))) {
                $pairs[$id]['clientEof'] = true;
            } elseif ($data !== '') {
                $pairs[$id]['toUpstream'] .= $data;
            }
        }

        if (isset($readable[$uid]) && !$pair['upstreamEof']) {
            $data = @fread($upstream, CHUNK);
            if ($data === false || ($data === '' && feof($upstream))) {
                $pairs[$id]['upstreamEof'] = true;
            } elseif ($data !== '') {
                $pairs[$id]['toClient'] .= $data;
            }
        }

        // ---- writes --------------------------------------------------------
        // Attempted whenever there is something queued: after a read above there
        // may be new bytes to send, and a socket that was not in this pass's write
        // set is simply not ready yet, which drain() handles.
        if ($pairs[$id]['toUpstream'] !== '' && !drain($pairs[$id]['toUpstream'], $upstream)) {
            closePair($pairs, $id);
            continue;
        }
        if ($pairs[$id]['toClient'] !== '' && !drain($pairs[$id]['toClient'], $client)) {
            closePair($pairs, $id);
            continue;
        }

        // ---- teardown ------------------------------------------------------
        // Upstream finishing means the response is complete: hold the pair open
        // until the last queued byte has actually been accepted by the client.
        // Closing on EOF alone is what would truncate the tail of a large asset.
        if ($pairs[$id]['upstreamEof'] && $pairs[$id]['toClient'] === '') {
            closePair($pairs, $id);
            continue;
        }
        // The client hung up and everything it sent has been forwarded, with no
        // response left to deliver.
        if ($pairs[$id]['clientEof']
            && $pairs[$id]['toUpstream'] === ''
            && $pairs[$id]['toClient'] === ''
            && $pairs[$id]['upstreamEof']) {
            closePair($pairs, $id);
        }
    }
}
