<?php

namespace Mint\Models;

/**
 * Class Sale
 * @Entity
 */
class Sale {

    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @Column(type="string");
     */
    private $costTokenId;

    /**
     * @var string
     * @Column(type="string", options={"default": ""});
     */
    private $seller;

    /**
     * @var string
     * @Column(type="string")
     */
    private $costTokenTicker;

    /**
     * @var float
     * @Column(type="float", precision=8);
     */
    private $costAmount;

    /**
     * @var string
     * @Column(type="string")
     */
    private $offerWallet;

    /**
     * @var string
     * @Column(type="string")
     */
    private $offerNft;

    /**
     * @var bool
     * @Column(type="boolean", nullable="true", options={"default": false})
     */
    private $sold;

    /**
     * @var bool
     * @Column(type="boolean", nullable="true", options={"default": false})
     */
    private $claimed;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCostTokenId(): string
    {
        return $this->costTokenId;
    }

    /**
     * @param string $costTokenId
     */
    public function setCostTokenId(string $costTokenId): void
    {
        $this->costTokenId = $costTokenId;
    }

    /**
     * @return string
     */
    public function getCostTokenTicker(): string
    {
        return $this->costTokenTicker;
    }

    /**
     * @param string $costTokenTicker
     */
    public function setCostTokenTicker(string $costTokenTicker): void
    {
        $this->costTokenTicker = $costTokenTicker;
    }

    /**
     * @return float
     */
    public function getCostAmount(): float
    {
        return $this->costAmount;
    }

    /**
     * @param float $costAmount
     */
    public function setCostAmount(float $costAmount): void
    {
        $this->costAmount = $costAmount;
    }

    /**
     * @return string
     */
    public function getOfferWallet(): string
    {
        return $this->offerWallet;
    }

    /**
     * @param string $offerWallet
     */
    public function setOfferWallet(string $offerWallet): void
    {
        $this->offerWallet = $offerWallet;
    }

    /**
     * @return string
     */
    public function getOfferNft(): string
    {
        return $this->offerNft;
    }

    /**
     * @param string $offerNft
     */
    public function setOfferNft(string $offerNft): void
    {
        $this->offerNft = $offerNft;
    }

    /**
     * @return string
     */
    public function getSeller(): string
    {
        return $this->seller;
    }

    /**
     * @param string $seller
     */
    public function setSeller(string $seller): void
    {
        $this->seller = $seller;
    }

    /**
     * @return bool
     */
    public function isSold(): bool
    {
        return $this->sold;
    }

    /**
     * @param bool $sold
     */
    public function setSold(bool $sold): void
    {
        $this->sold = $sold;
    }

    /**
     * @return bool
     */
    public function isClaimed()
    {
        return $this->claimed;
    }

    /**
     * @param bool $claimed
     */
    public function setClaimed(bool $claimed): void
    {
        $this->claimed = $claimed;
    }
}