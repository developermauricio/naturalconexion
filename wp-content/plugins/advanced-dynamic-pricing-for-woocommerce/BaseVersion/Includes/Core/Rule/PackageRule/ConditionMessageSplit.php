<?php

namespace ADP\BaseVersion\Includes\Core\Rule\PackageRule;

defined('ABSPATH') or exit;

class ConditionMessageSplit
{
    /**
     * Beginging message
     *
     * @var string
     */
    protected $beginningMessage;

    /**
     * Messages list
     *
     * @var string[]
     */
    protected $messages;

    /**
     * End message
     *
     * @var string
     */
    protected $endMessage;

    /**
     * @param string $beginningMessage
     * @param array<int,string> $messages
     * @param string $endMessage
     */
    public function __construct($beginningMessage, $messages, $endMessage)
    {
        $this->beginningMessage     = $beginningMessage;
        $this->messages             = $messages;
        $this->endMessage           = $endMessage;
    }

    /**
     * @param array<int,string> $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param int $key
     *
     * @return string|null
     */
    public function getMessage($key)
    {
        return $this->messages[$key] ?? reset($this->messages);
    }

    /**
     * @return array<int,string>
     */
    public function getMessages()
    {
        return $this->messages;
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
        return count(array_filter($this->messages)) > 0 || $this->beginningMessage !== '' || $this->endMessage !== '';
    }
}
