<?php


namespace Manifesto\Exceptions;


class ErrorResponseException extends \Exception {

    /**
     * @param string $message [optional] Manifesto error message
     * @param string $code [optional] Manifesto error code
     * @param \Exception $previous [optional] The previous exception used for the exception chaining.
     */
    public function __construct($message = "", $code = "", \Exception $previous=null)
    {
        $this->code = $code;
        parent::__construct($message, null, $previous);
    }
}