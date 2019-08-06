<?php

namespace Balfour\WhatsApp\Bot\Menu;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

class Option
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @param string $key
     * @param string $text
     * @param ActionInterface $action
     */
    public function __construct(string $key, string $text, ActionInterface $action)
    {
        $this->key = $key;
        $this->text = $text;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return ActionInterface
     */
    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}
