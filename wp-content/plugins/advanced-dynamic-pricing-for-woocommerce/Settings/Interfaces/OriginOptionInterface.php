<?php

namespace ADP\Settings\Interfaces;

interface OriginOptionInterface
{
    /**
     * @return mixed
     */
    public function get();

    /**
     * @param $value
     *
     * @return bool
     */
    public function set($value);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return bool
     */
    public function isValueInstalled();
}
