<?php

namespace ADP\Settings\Varieties\Option;

use ADP\Settings\Exceptions\OptionValueFilterFailed;

use ADP\Settings\Varieties\Option\Abstracts\Option;

class IntegerNumberOption extends Option
{
    /**
     * @param mixed $value
     *
     * @return string
     * @throws OptionValueFilterFailed
     */
    protected function sanitize($value)
    {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        if ($value === false) {
            throw new OptionValueFilterFailed();
        }

        return (string)$value;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate($value)
    {
        return is_numeric($value);
    }
}
