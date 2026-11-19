<?php

namespace App\Exceptions;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpResponseException::class,
        AuthorizationException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        ValidationException::class,
        Breadcrumbs::class,
    ];

    /**
     * @param \Throwable $e
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function report(\Throwable $e)
    {
        $this->reportToSentry($e);

        return parent::report($e);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param \Illuminate\Http\Request                   $request
     * @param \Illuminate\Validation\ValidationException $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json(['success' => false, 'errors' => $exception->errors()], $exception->status);
    }

    /**
     * @param            $request
     * @param \Throwable $e
     *
     * @throws \Throwable
     *
     * @return type|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, \Throwable $e)
    {
        switch ($e) {
            case $e instanceof HttpResponseException:
                return parent::render($request, $e);
            case $e instanceof \Illuminate\Auth\AuthenticationException:
                // Raised by the 'auth:api' (Passport) middleware when the bearer
                // token is missing, expired, revoked or otherwise invalid - the
                // replacement for the old Tymon JWT token exceptions.
                return response()->json(['message' => $e->getMessage(), 'code' => 401], 401);
            case $e instanceof TokenMismatchException:
                if ($request->ajax() || $request->wantsJson()) {
                    $result = ['fails' => \Lang::get('lang.session-expired')];

                    return response()->json(compact('result'), 402);
                }

                return redirect()->back()->with('fails', \Lang::get('lang.session-expired'));
            case $e instanceof PostTooLargeException:
                $message = \Lang::get('lang.attachment-too-large', ['size' => (new \App\Http\Controllers\Common\FileuploadController())->file_upload_max_size()[1]]);
                if ($request->ajax() || $request->wantsJson()) {
                    $result = ['fails' => $message];

                    return response()->json(compact('result'), 413);
                }

                return redirect()->back()->with('fails', $message);
            default:
                return $this->common($request, $e);
        }
    }

    /**
     * Function to render 500 error page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render500($request, $e)
    {
        $seg = $request->segments();
        if (in_array('api', $seg)) {
            if ($e instanceof ValidationException) {
                return $this->invalidJson($request, $e);
            }

            return response()->json(['error' => $e->getMessage()], 500);
        }
        if (config('app.debug') == true) {
            return parent::render($request, $e);
        } elseif ($e instanceof ValidationException) {
            // Note: the import at the top of this file is already
            // Illuminate\Validation\ValidationException, so a second branch
            // spelling out the FQCN was unreachable and has been removed.
            return parent::render($request, $e);
        }

        // response()->view() defaults to HTTP 200, so every unhandled server error
        // was served as a success with the error page as its body. Uptime checks,
        // caches and tests all read that as "fine". The status must say 500.
        return $this->brandedErrorView('errors.500', 500);
        //return redirect()->route('error500', []);
    }

    /**
     * Render one of the branded error pages, falling back to the self-contained page
     * when the branded one cannot be built.
     *
     * The branded pages extend the client layout, which queries settings_system,
     * widgets, kb pages... Before installation (no DB credentials yet) or with the
     * database down, rendering them throws from inside the exception handler, the
     * original error is lost and PHP logs an extra "Uncaught PDOException".
     */
    public function brandedErrorView(string $view, int $status): \Symfony\Component\HttpFoundation\Response
    {
        if (!isInstall()) {
            return $this->standaloneErrorView($status);
        }

        try {
            return response()->view($view, [], $status);
        } catch (\Throwable $renderError) {
            return $this->standaloneErrorView($status);
        }
    }

    /**
     * The database-free error page (resources/views/errors/standalone.blade.php).
     */
    public function standaloneErrorView(int $status): \Symfony\Component\HttpFoundation\Response
    {
        return response()->view('errors.standalone', ['status' => $status], $status);
    }

    /**
     * Whether $e (or anything it wraps, e.g. a ViewException around a QueryException)
     * is a failure to connect to the database rather than a bad query.
     *
     * QueryException extends PDOException and carries the driver code: 1044/1045
     * access denied, 1049 unknown database, 2002/2003/2005 server unreachable,
     * 2006 server has gone away.
     */
    protected function isDatabaseConnectionError(\Throwable $e): bool
    {
        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof \PDOException
                && in_array((int) $current->getCode(), [1044, 1045, 1049, 2002, 2003, 2005, 2006], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Function to render 404 error page.
     *
     * @param \Illuminate\Http\Request $request
     * @param HttpException            $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render404($request, $e)
    {
        $seg = $request->segments();
        if (in_array('api', $seg)) {
            return response()->json(['success' => false, 'message' => 'not-found'], 404);
        }
        // Not installed yet: the /404 route's page needs the database, so the redirect
        // below would turn every missing asset the installer requests into an error.
        if (!isInstall()) {
            return $this->standaloneErrorView(404);
        }
        if (config('app.debug') == true) {
            if ($e->getStatusCode() == '404') {
                return redirect()->route('error404', []);
            }

            return parent::render($request, $e);
        }

        return redirect()->route('error404', []);
    }

    /**
     * Function to render database connection failed.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderDB($request, $e)
    {
        $seg = $request->segments();
        if (in_array('api', $seg)) {
            return response()->json(['success' => false, 'message' => 'database-connection-failed'], 503);
        }
        if (config('app.debug') == true) {
            return parent::render($request, $e);
        }

        // Not a redirect to /404 or a view on the client layout: both need the very
        // database that just refused the connection.
        return $this->standaloneErrorView(503);
    }

    /**
     * Common function to render both types of codes.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function common($request, $e)
    {
        switch ($e) {
            case $e instanceof NotFoundHttpException:
                return $this->render404($request, $e);
            case $e instanceof HttpException:
                // Only a genuine 404 belongs on the not-found page. Every other
                // HttpException — abort(403), abort(401), abort(405) — used to be
                // funnelled here too and came back as a 500, so an authorisation
                // refusal was indistinguishable from a server fault.
                return $e->getStatusCode() === 404
                    ? $this->render404($request, $e)
                    : parent::render($request, $e);
            // Was `$e instanceof PDOException` (never imported, so it named the
            // non-existent App\Exceptions\PDOException) with strpos()'s arguments
            // reversed: connection failures never reached renderDB().
            case $this->isDatabaseConnectionError($e):
                return $this->renderDB($request, $e);
//            case $e instanceof ErrorException:
//                if($e->getMessage() == 'Breadcrumb not found with name "" ') {
//                    return $this->render404($request, $e);
//                } else {
//                    return parent::render($request, $e);
//                }
            case $e instanceof TokenMismatchException:
                if ($request->ajax() || $request->wantsJson()) {
                    $result = ['fails' => \Lang::get('lang.session-expired')];

                    return response()->json(compact('result'), 402);
                }

                return redirect()->back()->with('fails', \Lang::get('lang.session-expired'));
            case $e instanceof AuthorizationException:
                return redirect('/')->with('fails', \Lang::get('lang.access-denied'));
            case $e instanceof MethodNotAllowedHttpException:
                if (stripos($request->url(), 'api')) {
                    $result = ['message' => \Lang::get('lang.methon_not_allowed'), 'success' => false];

                    return response()->json($result, 405);
                }
                $this->render500($request, $e);
            default:
                return $this->render500($request, $e);
        }

        return parent::render($request, $e);
    }

    protected function reportToSentry(\Throwable $e): void
    {
        if (Config::get('app.sentry_reporting') && app()->environment('production') && $this->shouldReportToSentry($e) && app()->bound('sentry')) {
            \Sentry\Laravel\Integration::captureUnhandledException($e);
        }
    }

    public function shouldReportToSentry(\Throwable $e): bool
    {
        foreach ($this->dontReport as $report) {
            if ($e instanceof $report) {
                return false;
            }
        }

        return true;
    }
}
