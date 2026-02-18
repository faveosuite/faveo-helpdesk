<?php

namespace App\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
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
        $this->reportToBugsNag($e);

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
            case $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException:
                return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
            case $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException:
                return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
            case $e instanceof TokenMismatchException:
                if ($request->ajax() || $request->wantsJson()) {
                    $result = ['fails' => \Lang::get('lang.session-expired')];

                    return response()->json(compact('result'), 402);
                }

                return redirect()->back()->with('fails', \Lang::get('lang.session-expired'));
            default:
                return $this->common($request, $e);
        }
    }

    /**
     * Function to render 500 error page.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
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
            return parent::render($request, $e);
        } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
            return parent::render($request, $e);
        }

        return response()->view('errors.500');
        //return redirect()->route('error500', []);
    }

    /**
     * Function to render 404 error page.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function render404($request, $e)
    {
        $seg = $request->segments();
        if (in_array('api', $seg)) {
            return response()->json(['success' => false, 'message' => 'not-found'], 404);
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
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function renderDB($request, $e)
    {
        $seg = $request->segments();
        if (in_array('api', $seg)) {
            return response()->json(['status' => '404']);
        }
        if (config('app.debug') == true) {
            return parent::render($request, $e);
        }

        return redirect()->route('error404', []);
    }

    /**
     * Common finction to render both types of codes.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function common($request, $e)
    {
        switch ($e) {
            case $e instanceof HttpException:
                return $this->render404($request, $e);
            case $e instanceof NotFoundHttpException:
                return $this->render404($request, $e);
            case $e instanceof PDOException:
                if (strpos('1045', $e->getMessage()) == true) {
                    return $this->renderDB($request, $e);
                } else {
                    return $this->render500($request, $e);
                }
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

    protected function reportToBugsNag(\Throwable $e)
    {
        if (Config::get('app.bugsnag_reporting') && env('APP_ENV') == 'production' && $this->shouldReportBugsnag($e)) {
            Bugsnag::notifyException($e);
        }
    }

    public function shouldReportBugsnag($e)
    {
        foreach ($this->dontReport as $report) {
            if ($e instanceof $report) {
                return false;
            }
        }

        return true;
    }
}
