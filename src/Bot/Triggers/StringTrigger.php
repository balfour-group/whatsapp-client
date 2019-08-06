<?php

namespace Balfour\WhatsApp\Bot\Triggers;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

class StringTrigger extends BaseTrigger
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var bool
     */
    protected $isCaseSensitive;

    /**
     * @param string $string
     * @param ActionInterface $action
     * @param bool $isCaseSensitive
     */
    public function __construct(
        string $string,
        ActionInterface $action,
        bool $isCaseSensitive = false
    ) {
        $this->string = $string;
        $this->isCaseSensitive = $isCaseSensitive;

        parent::__construct($action);
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->isCaseSensitive;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function matches(string $message): bool
    {
        if ($this->isCaseSensitive) {
            return $message === $this->string;
        } else {
            return strcasecmp($message, $this->string) === 0;
        }
    }
}
