<?php

namespace Balfour\WhatsApp\Bot\Triggers;

use Balfour\WhatsApp\Bot\Actions\ActionInterface;

class StartsWithTrigger extends BaseTrigger
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
            return mb_substr($message, 0, mb_strlen($this->string)) === $this->string;
        } else {
            $message = mb_strtolower($message);
            $string = mb_strtolower($this->string);

            return mb_substr($message, 0, mb_strlen($string)) === $string;
        }
    }
}
