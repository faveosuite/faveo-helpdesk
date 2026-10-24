<?php

namespace App\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiExceptAuthController extends Controller
{
    public $api_controller;

    public function __construct(Request $request)
    {
        $this->request = $request;

        // This controller's name says it's exempt from the usual auth, but
        // checkUrl() below makes the server fetch a caller-supplied URL - that's
        // not something to leave open. 'api' alone would have been too weak to
        // rely on anyway: App\Http\Middleware\ApiKey lets every request through
        // untouched whenever settings_system.api_key_mandatory is off (the
        // default), so a commented-out `$this->middleware('api')` was never a
        // real gate. jwt.authOveride is the same bearer-token check every other
        // /api/v1/helpdesk/* route behind ApiController requires.
        $this->middleware('jwt.authOveride');
    }

    /**
     * Check the url is valid or not.
     *
     * @return json
     */
    public function checkUrl()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                'url' => 'required|url',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $url = $this->request->input('url');
            if (!Str::is('*/', $url)) {
                $url = Str::finish($url, '/');
            }

            // required|url above only checks the string is a well-formed URL -
            // http://169.254.169.254/ and http://10.0.0.5/ both pass that just
            // fine. Resolve the host ourselves and refuse anything but a public
            // address before cURL ever touches it.
            $target = $this->resolvePublicTarget($url);
            if ($target === null) {
                return response()->json(['error' => 'The supplied url could not be validated or points to a disallowed address.'], 422);
            }

            $checkUrl = $url.'api/v1/helpdesk/check-url';
            $response = $this->callGetApiPinned($checkUrl, $target);

            // The remote response body used to be reflected back to the caller
            // verbatim - a *non-blind* SSRF, since whatever an internal service
            // said back was readable here. Collapsing it to a bare
            // success/fail removes that read primitive even if every check
            // above were somehow bypassed.
            $result = (is_string($response) && str_contains($response, '"status":"success"')) ? 'success' : 'fail';

            return response()->json(compact('result'));
        } catch (\Exception $ex) {
            $error = $ex->getMessage();

            return response()->json(compact('error'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $ex) {
            return response()->json(['status' => 'fails', 'code' => $ex->getStatusCode()]);
        }
    }

    /**
     * Resolve $url's host and refuse anything that isn't a public address.
     *
     * Returns the data callGetApi() needs to pin its cURL connection to the
     * exact address validated here (host, port, scheme, the resolved IP), or
     * null when the host doesn't resolve to a public address at all. Pinning
     * matters as much as the check itself: without it, an attacker's DNS could
     * answer this validation with a public IP and switch to a private one (or
     * 169.254.169.254) by the time cURL actually connects - a classic
     * check-then-use / DNS-rebinding gap.
     *
     * @param string $url
     *
     * @return array{host: string, port: int, scheme: string, ip: string}|null
     */
    private function resolvePublicTarget($url)
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? null;

        if ($host === null || !in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // A literal IP in the URL (http://127.0.0.1/...) never goes through DNS,
        // so it's checked directly instead of being looked up.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
        } else {
            $addresses = @gethostbynamel($host);
            if (!$addresses) {
                return null;
            }
            // Every address this host resolves to must be public: picking only
            // the first would let an attacker publish both a public and a
            // private A record and rely on this check validating the former.
            foreach ($addresses as $address) {
                if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    return null;
                }
            }
            $ip = $addresses[0];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            // Covers RFC1918 private ranges, loopback (127.0.0.0/8, ::1),
            // link-local (169.254.0.0/16 - which includes the AWS/GCP/Azure
            // metadata address 169.254.169.254), and the other IANA-reserved
            // ranges PHP's filter flags recognise.
            return null;
        }

        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        return ['host' => $host, 'port' => (int) $port, 'scheme' => $scheme, 'ip' => $ip];
    }

    /**
     * Success for currect url.
     *
     * @return string
     */
    public function urlResult()
    {
        try {
            $result = ['status' => 'success'];

            return $result;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $ex) {
            return ['status' => 'fails', 'code' => $ex->getStatusCode()];
        }
    }

    /**
     * Call curl function for Get Method.
     *
     * @param type $url
     *
     * @return type int|string|json
     */
    public function callGetApi($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            //echo 'error:' . curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * SSRF-hardened GET, used only by checkUrl() above.
     *
     * callGetApi() is left exactly as it was for whatever else may still call
     * it, since it's a public method with no callers found in this codebase but
     * no proof there isn't an external one either; this is a separate method
     * rather than a change to callGetApi() itself so checkUrl() can't
     * accidentally regress back to the unpinned, cert-bypassing behaviour.
     *
     * CURLOPT_RESOLVE pins the connection to $target['ip'], the exact address
     * resolvePublicTarget() already validated as public - cURL is never asked
     * to resolve $target['host'] itself, which is what closes the
     * DNS-rebinding gap between that check and this request.
     *
     * @param string                                                     $url
     * @param array{host: string, port: int, scheme: string, ip: string} $target
     *
     * @return string|bool
     */
    private function callGetApiPinned($url, array $target)
    {
        if ($url === '') {
            return false;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RESOLVE, [$target['host'].':'.$target['port'].':'.$target['ip']]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Call curl function for POST Method.
     *
     * @param type $url
     * @param type $data
     *
     * @return type int|string|json
     */
    public function callPostApi($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:'.curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }
}
