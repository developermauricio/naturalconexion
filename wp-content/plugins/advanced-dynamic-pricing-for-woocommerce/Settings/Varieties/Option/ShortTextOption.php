<?php

namespace ADP\Settings\Varieties\Option;

use ADP\Settings\Exceptions\OptionValueFilterFailed;

use ADP\Settings\Varieties\Option\Abstracts\Option;

class ShortTextOption extends Option
{
    /**
     * @param mixed $value
     *
     * @return string
     * @throws OptionValueFilterFailed
     */
    protected function sanitize($value)
    {
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);

        if ($value === false) {
            throw new OptionValueFilterFailed();
        }

        return (string)$value;
    }
}
