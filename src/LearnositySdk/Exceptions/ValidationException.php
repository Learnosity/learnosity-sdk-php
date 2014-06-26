<?php
namespace LearnositySdk\Exceptions;

class ValidationException extends \Exception
{
    public $data = null;

    /**
     * @param string        $message  exception message
     * @param integer       $code     user defined exception code
     * @param Exception     $previous previous exception if nested exception
     * @param mixed         $data     data which caused this exception to raise
     */
    public function __construct($message = '', $code = 0, $previous = null, $data = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }
}
