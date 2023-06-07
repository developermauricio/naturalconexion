<?php

namespace ADP\Settings\Exceptions;

class WrongKeyType extends \Exception
{
    public function errorMessage()
    {
        return 'Wrong key type'; // TODO localize
    }
}
