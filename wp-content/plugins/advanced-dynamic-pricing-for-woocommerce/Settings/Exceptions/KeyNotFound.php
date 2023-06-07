<?php

namespace ADP\Settings\Exceptions;

class KeyNotFound extends \Exception
{
    private $key;

    public function __construct($key, $code = 0, \Throwable $previous = null)
    {
        $this->key = $key;
        parent::__construct('', $code, $previous);
    }

    public function errorMessage()
    {
        return "Key '{$this->key}' not found"; // TODO localize
    }
}
