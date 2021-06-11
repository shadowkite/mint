<?php

namespace Mint\Models;

/**
 * Class PurchaseHold
 * @package Mint\Models
 * @Entity
 */
class PurchaseHold {

    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $timestamp;

    /**
     * @var int
     * @Column(type="integer")
     */
    private $sale;

    /**
     * @var string
     * @Column(type="string")
     */
    private $tokenReceiver;

    /**
     * @var bool
     * @Column(type="boolean", nullable="true", options={"default": false})
     */
    private $funded;

    /**
     * @var bool
     * @Column(type="boolean", nullable="true", options={"default": false})
     */
    private $expired;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getSale(): int
    {
        return $this->sale;
    }

    /**
     * @param int $sale
     */
    public function setSale(int $sale): void
    {
        $this->sale = $sale;
    }

    /**
     * @return string
     */
    public function getTokenReceiver(): string
    {
        return $this->tokenReceiver;
    }

    /**
     * @param string $tokenReceiver
     */
    public function setTokenReceiver(string $tokenReceiver): void
    {
        $this->tokenReceiver = $tokenReceiver;
    }

    /**
     * @return bool
     */
    public function isFunded(): bool
    {
        return $this->funded;
    }

    /**
     * @param bool $funded
     */
    public function setFunded(bool $funded): void
    {
        $this->funded = $funded;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @param bool $expired
     */
    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }
}