<?php

namespace Balfour\WhatsApp;

class File
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param string $url
     * @param string $filename
     */
    public function __construct(string $url, string $filename)
    {
        $this->url = $url;
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
