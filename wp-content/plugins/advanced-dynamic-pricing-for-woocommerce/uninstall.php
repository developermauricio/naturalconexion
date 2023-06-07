<?php

namespace ADP;

use ADP\BaseVersion\Includes\Database\Database;
use ADP\BaseVersion\Includes\Context;

if (defined('WP_UNINSTALL_PLUGIN')) {
    if ( ! class_exists("\ADP\AutoLoader")) {
        include_once "AutoLoader.php";
    }

    if ( ! class_exists("\ADP\Factory")) {
        include_once "Factory.php";
    };

    \ADP\AutoLoader::register();

    $path = trailingslashit(dirname(__FILE__));

    $context = new Context();
    // delete tables  only if have value in settings
    if ($context->getOption('uninstall_remove_data')) {
        Database::deleteDatabase();
    }

    $extension_file = $path . 'ProVersion/uninstall.php';
    if (file_exists($extension_file)) {
        include_once $extension_file;
    }
}
