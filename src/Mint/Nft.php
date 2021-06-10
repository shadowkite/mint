<?php

namespace Mint;

use Mint\Models\Sale;

class Nft {
    private $tokenId;

    private $tokenName;

    private $parentTokenId;

    private $tokenTicker;

    private static $groupIcons;

    const GROUP_ICON_FILE = __DIR__ . '/../../cache/group_icon_repos.json';

    /**
     * @var Sale
     */
    private $sale;

    public function __construct() {
        Nft::loadGroupIcons();
    }

    public static function factory($tokenId, Slp $slp = null) {
        if(!$slp) {
            $slp = new Slp;
        }

        $info = $slp->getTokenInfo($tokenId);

        $nft = new Nft();
        $nft->setTokenId($tokenId);
        $nft->setParentTokenId($info->parentTokenId);
        $nft->setTokenName($info->name);
        $nft->setTokenTicker($info->ticker);
        return $nft;
    }

    private static function loadGroupIcons() {
        if(!file_exists(self::GROUP_ICON_FILE)) {
            touch(self::GROUP_ICON_FILE);
            $content = file_get_contents('https://simpleledger.info/group_icon_repos.json');
            if($content) {
                file_put_contents(self::GROUP_ICON_FILE, $content);
            }
        }
        self::$groupIcons = json_decode(file_get_contents(self::GROUP_ICON_FILE));
    }

    /**
     * @return mixed
     */
    public function getTokenId()
    {
        return $this->tokenId;
    }

    /**
     * @param mixed $tokenId
     */
    public function setTokenId($tokenId): void
    {
        $this->tokenId = $tokenId;
    }

    /**
     * @return mixed
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * @param mixed $tokenName
     */
    public function setTokenName($tokenName): void
    {
        $this->tokenName = $tokenName;
    }

    /**
     * @return mixed
     */
    public function getParentTokenId()
    {
        return $this->parentTokenId;
    }

    /**
     * @param mixed $parentTokenId
     */
    public function setParentTokenId($parentTokenId): void
    {
        $this->parentTokenId = $parentTokenId;
    }

    public function getImageUrl() {
        if(!isset(self::$groupIcons->{$this->getParentTokenId()})) {
            return null;
        }
        return self::$groupIcons->{$this->getParentTokenId()} . '/original/' . $this->getTokenId() . '.png';
    }

    /**
     * @return Sale
     */
    public function getSale(): Sale
    {
        return $this->sale;
    }

    /**
     * @param Sale $sale
     */
    public function setSale(Sale $sale): void
    {
        $this->sale = $sale;
    }

    /**
     * @return mixed
     */
    public function getTokenTicker()
    {
        return $this->tokenTicker;
    }

    /**
     * @param mixed $tokenTicker
     */
    public function setTokenTicker($tokenTicker): void
    {
        $this->tokenTicker = $tokenTicker;
    }

}