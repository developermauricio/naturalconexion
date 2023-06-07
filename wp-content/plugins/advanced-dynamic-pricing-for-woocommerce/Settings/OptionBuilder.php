<?php

namespace ADP\Settings;

use ADP\Settings\Varieties\Option\BooleanOption;
use ADP\Settings\Varieties\Option\IntegerNumberOption;
use ADP\Settings\Varieties\Option\ShortTextOption;
use ADP\Settings\Exceptions\OptionValueFilterFailed;
use ADP\Settings\Varieties\SelectiveOption\SelectiveOption;

class OptionBuilder
{
    /**
     * @param string $id
     * @param string $default
     * @param null $title
     *
     * @return ShortTextOption|null
     */
    public static function shortText($id, $default = "", $title = null)
    {
        if ( ! $id) {
            return null;
        }

        $option = new ShortTextOption($id);
        $option->setDefault($default);
        $option->setTitle($title);


        return $option;
    }

    /**
     * @param string $id
     * @param string $default
     * @param null $title
     *
     * @return BooleanOption|null
     */
    public static function boolean($id, $default = "", $title = null)
    {
        if ( ! $id) {
            return null;
        }

        $option = new BooleanOption($id);
        $option->setDefault($default);
        $option->setTitle($title);


        return $option;
    }

    /**
     * @param string $id
     * @param string $default
     * @param null $title
     *
     * @return IntegerNumberOption|null
     */
    public static function integer($id, $default = "", $title = null)
    {
        if ( ! $id) {
            return null;
        }

        $option = new IntegerNumberOption($id);
        $option->setDefault($default);
        $option->setTitle($title);


        return $option;
    }

    /**
     * @param string $id
     * @param string $default
     * @param null $title
     *
     * @return ShortTextOption|null
     */
    public static function htmlText($id, $default = "", $title = null)
    {
        if ( ! $id) {
            return null;
        }

        $option = new ShortTextOption($id);
        $option->setSanitizeCallback(function ($value) {
            $value = stripslashes($value);
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($value === false) {
                throw new OptionValueFilterFailed();
            }

            return (string)$value;
        });

        $option->setDefault($default);
        $option->setTitle($title);


        return $option;
    }

    /**
     * @param string $id
     * @param string $title
     * @param mixed[] $selections
     * @param mixed $default
     *
     * @return SelectiveOption
     */
    public static function selective($id, $title, $selections, $default = null)
    {
        if ( ! $id || ! $title || ! $selections) {
            return null;
        }

        $option = new SelectiveOption($id);
        $option->setTitle($title);

        foreach ($selections as $value => $title) {
            $option->addSelection($value, $value);
        }

        if (isset($default)) {
            $option->setDefault($default);
        }

        return $option;
    }

}
