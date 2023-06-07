<?php

namespace ADP\BaseVersion\Includes\AdminExtensions;

use ADP\BaseVersion\Includes\AdminExtensions\MetaBoxes\OrderAppliedDiscountRules;

defined('ABSPATH') or exit;

class MetaBoxes
{
    public function register()
    {
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'), 30);
    }

    public function addMetaBoxes()
    {
        OrderAppliedDiscountRules::init();
    }
}
