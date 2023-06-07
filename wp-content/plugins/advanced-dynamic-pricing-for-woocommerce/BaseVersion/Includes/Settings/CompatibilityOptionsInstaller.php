<?php

namespace ADP\BaseVersion\Includes\Settings;

use ADP\Settings\OptionBuilder;
use ADP\Settings\OptionsList;
use ADP\Settings\OptionsManager;

defined('ABSPATH') or exit;

class CompatibilityOptionsInstaller
{
    public static function install()
    {
        $settings = new OptionsManager(new CompatibilityStoreStrategy());
        $optionsList = new OptionsList();

        static::registerSettings($optionsList);

        $settings->installOptions($optionsList);
        $settings->load();

        return $settings;
    }

    /**
     * @param OptionsList $optionsList
     */
    public static function registerSettings(&$optionsList)
    {
        $builder = new OptionBuilder();
    }
}
