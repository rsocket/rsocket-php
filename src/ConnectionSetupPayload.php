<?php

namespace RSocket;

class ConnectionSetupPayload extends Payload
{
    private string $metadataMimeType="message/x.rsocket.composite-metadata.v0";
    private string $dataMimeType="application/json";
    private int $keepAliveInterval=20000;
    private int $keepAliveMaxLifetime=90000;
    private int $flags=0;
    private bool $resumeEnabled = false;
    private ?string $resumeToken=null;

    /**
     * @return string
     */
    public function getMetadataMimeType(): string
    {
        return $this->metadataMimeType;
    }

    /**
     * @param string $metadataMimeType
     */
    public function setMetadataMimeType(string $metadataMimeType): void
    {
        $this->metadataMimeType = $metadataMimeType;
    }

    /**
     * @return string
     */
    public function getDataMimeType(): string
    {
        return $this->dataMimeType;
    }

    /**
     * @param string $dataMimeType
     */
    public function setDataMimeType(string $dataMimeType): void
    {
        $this->dataMimeType = $dataMimeType;
    }

    /**
     * @return int
     */
    public function getKeepAliveInterval(): int
    {
        return $this->keepAliveInterval;
    }

    /**
     * @param int $keepAliveInterval
     */
    public function setKeepAliveInterval(int $keepAliveInterval): void
    {
        $this->keepAliveInterval = $keepAliveInterval;
    }

    /**
     * @return int
     */
    public function getKeepAliveMaxLifetime(): int
    {
        return $this->keepAliveMaxLifetime;
    }

    /**
     * @param int $keepAliveMaxLifetime
     */
    public function setKeepAliveMaxLifetime(int $keepAliveMaxLifetime): void
    {
        $this->keepAliveMaxLifetime = $keepAliveMaxLifetime;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }

    /**
     * @return bool
     */
    public function isResumeEnabled(): bool
    {
        return $this->resumeEnabled;
    }

    /**
     * @param bool $resumeEnabled
     */
    public function setResumeEnabled(bool $resumeEnabled): void
    {
        $this->resumeEnabled = $resumeEnabled;
    }

    /**
     * @return string
     */
    public function getResumeToken(): string
    {
        return $this->resumeToken;
    }

    /**
     * @param string $resumeToken
     */
    public function setResumeToken(string $resumeToken): void
    {
        $this->resumeToken = $resumeToken;
    }

}