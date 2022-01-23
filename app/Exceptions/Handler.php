<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Session;
use Illuminate\Validation\ValidationException;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // custom error message
        if ($exception instanceof ErrorException) {
            return response()->view('backend.layout.partials.errors', 
            [
                'url'=>'/', 
                'code'=>'500', 
                'exception' => $exception
            ], 500);
        } 
        else if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }
        else if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return response()->view('backend.layout.partials.errors', 
            [
                'url'=>'/login', 
                'code'=> '419', 
                'exception' => $exception
            ], 404);
        }
        else {
            return response()->view('backend.layout.partials.errors', 
            [
                'url'=>'/', 
                'code'=> '404', 
                'exception' => $exception
            ], 404);
        }
        return parent::render($request, $exception);
    }
}
