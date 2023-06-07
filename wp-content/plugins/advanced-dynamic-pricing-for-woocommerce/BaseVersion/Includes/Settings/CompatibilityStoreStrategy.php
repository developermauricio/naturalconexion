<?php

namespace ADP\BaseVersion\Includes\Settings;

use ADP\Settings\Exceptions\KeyNotFound;
use ADP\Settings\Interfaces\StoreStrategyInterface;
use ADP\Settings\OptionsList;

defined('ABSPATH') or exit;

class CompatibilityStoreStrategy implements StoreStrategyInterface
{
    const OPTION_KEY = 'wdp_settings_compatibility';

    /**
     * @param OptionsList $optionsList
     */
    public function save($optionsList)
    {
        if ($optionsList->getOptionsArray()) {
            update_option(self::OPTION_KEY, $optionsList->getOptionsArray());
            wp_cache_flush();
        }
    }

    /**
     * @param OptionsList $optionsList
     */
    public function load($optionsList)
    {
        $options = get_option(self::OPTION_KEY, array());

        foreach ($options as $key => $value) {
            try {
                $option = $optionsList->getByKey($key);
                $option->set($value);
            } catch (KeyNotFound $exception) {

            }
        }
    }

    public function drop()
    {
        if (function_exists("delete_option")) {
            delete_option(self::OPTION_KEY);
        }
    }

    public function truncate()
    {
        if (function_exists("update_option")) {
            update_option(self::OPTION_KEY, []);
        }
    }
}
