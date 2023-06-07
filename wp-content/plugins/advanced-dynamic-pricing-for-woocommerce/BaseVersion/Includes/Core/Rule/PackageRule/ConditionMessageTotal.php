<?php

namespace ADP\BaseVersion\Includes\Core\Rule\PackageRule;

defined('ABSPATH') or exit;

class ConditionMessageTotal
{
    /**
     * Beginging message
     *
     * @var string
     */
    protected $beginningMessage;

    /**
     * Message
     *
     * @var string
     */
    protected $message;

    /**
     * End message
     *
     * @var string
     */
    protected $endMessage;

    /**
     * @param string $beginningMessage
     * @param string $message
     * @param string $endMessage
     */
    public function __construct($beginningMessage, $message, $endMessage)
    {
        $this->beginningMessage     = $beginningMessage;
        $this->message              = $message;
        $this->endMessage           = $endMessage;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setBeginningMessage($message)
    {
        $this->beginningMessage = (string)$message;
    }

    /**
     * @param string $message
     */
    public function setEndMessage($message)
    {
        $this->endMessage = (string)$message;
    }

    /**
     * @return string
     */
    public function getBeginningMessage()
    {
        return $this->beginningMessage;
    }

    /**
     * @return string
     */
    public function getEndMessage()
    {
        return $this->endMessage;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->message !== '' || $this->beginningMessage !== '' || $this->endMessage !== '';
    }

}
