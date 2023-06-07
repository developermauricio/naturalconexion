<?php

namespace ADP\Settings;

use ADP\Settings\Exceptions\EnvironmentKeyNotFound;
use ADP\Settings\Exceptions\WrongKeyType;

class Environment
{
    /**
     * @var array
     */
    protected $props = array();

    /**
     * @var array
     */
    protected $defaultProps = array();

    public function __construct()
    {
        $this->defaultProps = array(
            'debug' => true,
        );

        $this->props = $this->defaultProps;
    }

    public function isDebug()
    {
        return $this->getProp('debug');
    }

    /**
     * @param string $prop
     *
     * @return mixed
     * @throws EnvironmentKeyNotFound
     * @throws WrongKeyType
     */
    protected function getProp($prop)
    {
        if ( ! is_string($prop)) {
            throw new WrongKeyType();
        }

        if ( ! isset($this->props[$prop])) {
            throw new EnvironmentKeyNotFound();
        }

        return $this->props[$prop];
    }


}
