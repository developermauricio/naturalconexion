<?php

namespace ADP\BaseVersion\Includes\Context;

defined('ABSPATH') or exit;

class Geolocation
{
    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $postcode;

   public function __construct(string $country = "", string $state = "", string $city = "", string $postcode = "")
    {
        $this->country  = $country;
        $this->state    = $state;
        $this->city     = $city;
        $this->postcode = $postcode;
    }
}
