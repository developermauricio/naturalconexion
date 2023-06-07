<?php

namespace ADP\BaseVersion\Includes\PriceDisplay\PriceFormatters;

use ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class Formatter
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string[]
     */
    protected $availableReplacements;

    /**
     * @param null $deprecated
     */
    public function __construct($deprecated = null)
    {
        $this->context               = adp_context();
        $this->template              = "";
        $this->availableReplacements = array();
    }

    public function withContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        if ( ! is_string($template)) {
            return;
        }

        $this->template              = $template;
        $this->availableReplacements = array();
        if (preg_match_all("/{{([^ {}]+)}}/", $template, $matches) !== false) {
            if (isset($matches[1]) && is_array($matches[1])) {
                $this->availableReplacements = $matches[1];
            }
        }
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array<int,string>
     */
    public function getAvailableReplacements()
    {
        return $this->availableReplacements;
    }

    /**
     * @param array<int,string> $replacements
     *
     * @return string
     */
    public function applyReplacements($replacements)
    {
        if ( ! is_array($replacements)) {
            return "";
        }

        $newReplacements = array();
        foreach ($this->availableReplacements as $key) {
            if ( ! isset($replacements[$key])) {
                $replacements[$key] = "";
            }

            $newReplacements["{{" . $key . "}}"] = $replacements[$key];
        }

        return str_replace(array_keys($newReplacements), array_values($newReplacements), $this->template);
    }

}
