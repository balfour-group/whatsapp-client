<?php

namespace Balfour\WhatsApp;

use Carbon\CarbonInterface;

class Message
{
    /**
     * @var string
     */
    protected $chatId;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $phoneNumber;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $mediaUrl;

    /**
     * @var CarbonInterface
     */
    protected $date;

    /**
     * @var bool
     */
    protected $isOutbound;

    /**
     * @var int
     */
    protected $messageNumber;

    /**
     * @param string $chatId
     * @param string $messageId
     * @param string $phoneNumber
     * @param string $type
     * @param string|null $message
     * @param string|null $mediaUrl
     * @param CarbonInterface $date
     * @param bool $isOutbound
     * @param int $messageNumber
     */
    public function __construct(
        string $chatId,
        string $messageId,
        string $phoneNumber,
        string $type,
        ?string $message,
        ?string $mediaUrl,
        CarbonInterface $date,
        bool $isOutbound,
        int $messageNumber
    ) {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->phoneNumber = $phoneNumber;
        $this->type = $type;
        $this->message = $message;
        $this->mediaUrl = $mediaUrl;
        $this->date = $date;
        $this->isOutbound = $isOutbound;
        $this->messageNumber = $messageNumber;
    }

    /**
     * @return string
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    /**
     * @return CarbonInterface
     */
    public function getDate(): CarbonInterface
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function isOutbound(): bool
    {
        return $this->isOutbound;
    }

    /**
     * @return bool
     */
    public function isInbound(): bool
    {
        return !$this->isOutbound;
    }

    /**
     * @return int
     */
    public function getMessageNumber(): int
    {
        return $this->messageNumber;
    }
}
