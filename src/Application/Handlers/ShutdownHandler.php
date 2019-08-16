<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

class ShutdownHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var HttpErrorHandler
     */
    private $errorHandler;

    /**
     * @var bool
     */
    private $displayErrorDetails;

    /**
     * ShutdownHandler constructor.
     *
     * @param Request       $request
     * @param $errorHandler $errorHandler
     * @param bool          $displayErrorDetails
     */
    public function __construct(
        Request $request,
        HttpErrorHandler $errorHandler,
        bool $displayErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke()
    {
        $error = error_get_last();
        if ($error) {
            $errorFile = $error['file'];
            $errorLine = $error['line'];
            $errorMessage = $error['message'];
            $errorType = $error['type'];

            switch ($errorType) {
                case E_USER_ERROR:
                    $message = "FATAL ERROR: {$errorMessage}. ";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;

                case E_WARNING:
                    return;

                case E_NOTICE:
                    return;

                case E_USER_WARNING:
                    //$message = "WARNING: {$errorMessage}";
                    //break;
                    return;

                case E_USER_NOTICE:
                    //$message = "NOTICE: {$errorMessage}";
                    //break;
                    return;

                default:
                    $message = "ERROR: {$errorMessage}";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;
            }
            if ($this->displayErrorDetails) {
                //
            } else {
                $message = 'An error while processing your request. Please try again later.';
            }

            $exception = new HttpInternalServerErrorException($this->request, $message);
            $response = $this->errorHandler->__invoke($this->request, $exception, $this->displayErrorDetails, false, false);

            $responseEmitter = new ResponseEmitter();
            $responseEmitter->emit($response);
        }
    }
}
