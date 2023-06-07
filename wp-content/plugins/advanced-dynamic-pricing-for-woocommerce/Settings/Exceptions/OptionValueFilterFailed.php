<?php

namespace ADP\Settings\Exceptions;

class OptionValueFilterFailed extends \Exception
{
    public function errorMessage()
    {
        return 'Option value filter failed'; // TODO localize
    }
}
