<?php

namespace Mint;

/**
 * Class Wallet
 * @package Mint
 */
class Wallet {

    /**
     * @var string
     */
    private $network;

    /**
     * @var string
     */
    private $seed;

    /**
     * @var string
     */
    private $dp;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * Wallet constructor.
     * @param $network
     * @param $seed
     * @param $dp
     */
    public function __construct($network, $seed, $dp) {
        $this->network = $network;
        $this->seed = $seed;
        $this->dp = $dp;
    }

    /**
     * @param int $group
     * @param null $index
     * @return string
     */
    public function generateWalletId($group = 0, $index = null) {
        if($index === null) {
            $index = $this->index;
        }
        return 'seed:' . $this->network . ':' . $this->seed . ':' . $this->dp . '/' . $group . '/' . $index;
    }

    /**
     * @param $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * @param null|string $net
     */
    public function setNetwork($net = null) {
        if($net == null) {
            switch($this->network) {
                case 'mainnet': $this->network = 'testnet'; break;
                case 'testnet': $this->network = 'mainnet'; break;
                default: $this->network = 'testnet';
            }
        } else {
            $this->network = $net;
        }
    }

    public function getNetwork() {
        return $this->network;
    }

    public function getDerivationPath() {
        return $this->dp;
    }

    public function getSeed() {
        return $this->seed;
    }
}