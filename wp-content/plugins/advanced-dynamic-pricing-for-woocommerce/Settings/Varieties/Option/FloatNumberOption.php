<?php

namespace ADP\Settings\Varieties\Option;

use ADP\Settings\Exceptions\OptionValueFilterFailed;

use ADP\Settings\Varieties\Option\Abstracts\Option;

class FloatNumberOption extends Option
{
    /**
     * @param mixed $value
     *
     * @return string
     * @throws OptionValueFilterFailed
     */
    protected function sanitize($value)
    {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);

        if ($value === false) {
            throw new OptionValueFilterFailed();
        }

        return floatval($value);
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
