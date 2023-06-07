<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

class ThemeModificationsRepository implements ThemeModificationsRepositoryInterface
{
    const OPTION_NAME = 'woocommerce_wdp_bulk_table';

    public function getModifications()
    {
        return function_exists("get_theme_mod") ? get_theme_mod(self::OPTION_NAME) : [];
    }

    public function drop()
    {
        if (function_exists("remove_theme_mod")) {
            remove_theme_mod(self::OPTION_NAME);
        }
    }

    public function truncate()
    {
        if (function_exists("set_theme_mod")) {
            set_theme_mod(self::OPTION_NAME, []);
        }
    }
}
