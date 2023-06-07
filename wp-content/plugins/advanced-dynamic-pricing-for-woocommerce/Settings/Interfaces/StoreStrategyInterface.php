<?php

namespace ADP\Settings\Interfaces;

use ADP\Settings\OptionsList;

interface StoreStrategyInterface
{
    /**
     * @param OptionsList $optionsList
     */
    public function save($optionsList);

    /**
     * @param OptionsList $optionsList
     */
    public function load($optionsList);

    public function drop();

    public function truncate();
}
