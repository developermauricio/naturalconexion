<?php

namespace ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class Language
{
    /**
     * @var string
     */
    protected $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return Language
     */
    public static function buildAsDefault()
    {
        return new self('en_US');
    }
}
