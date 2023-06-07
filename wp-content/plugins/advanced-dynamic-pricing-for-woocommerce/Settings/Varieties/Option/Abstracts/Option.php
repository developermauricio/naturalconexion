<?php

namespace ADP\Settings\Varieties\Option\Abstracts;

use ADP\Settings\Abstracts\OriginOption;
use ADP\Settings\Exceptions\OptionValueFilterFailed;

use ADP\Settings\Varieties\Option\Exceptions\SanitizeCallbackIsNotCallable;
use ADP\Settings\Varieties\Option\Exceptions\ValidateCallbackIsNotCallable;
use ADP\Settings\Varieties\Option\Interfaces\OptionInterface;


abstract class Option extends OriginOption implements OptionInterface
{
    /**
     * @var callable
     */
    protected $validateCallback;

    /**
     * @var callable
     */
    protected $sanitizeCallback;

    /**
     * Option constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->validateCallback = array($this, 'validate');
        $this->sanitizeCallback = array($this, 'sanitize');
    }

    /**
     * Must be override
     *
     * @param mixed $value
     *
     * @return mixed
     * @throws OptionValueFilterFailed
     */
    protected function sanitize($value)
    {
        return $value;
    }

    /**
     * Must be override
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validate($value)
    {
        return true;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws OptionValueFilterFailed
     * @throws SanitizeCallbackIsNotCallable
     * @throws ValidateCallbackIsNotCallable
     */
    protected function filter($value)
    {
        if ( ! is_callable($this->sanitizeCallback)) {
            throw new SanitizeCallbackIsNotCallable(); // TODO if in production LOG IT!
        }

        $value = call_user_func($this->sanitizeCallback, $value);

        if ( ! is_callable($this->validateCallback)) {
            throw new ValidateCallbackIsNotCallable(); // TODO if in production LOG IT!
        }

        if ( ! call_user_func($this->validateCallback, $value)) {
            throw new OptionValueFilterFailed(); // TODO if in production LOG IT!
        }

        return $value;
    }

    public function setSanitizeCallback($callback): Option
    {
        $this->sanitizeCallback = $callback;

        return $this;
    }

    public function setValidateCallback($callback): Option
    {
        $this->validateCallback = $callback;

        return $this;
    }
}
