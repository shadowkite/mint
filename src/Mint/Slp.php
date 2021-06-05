<?php

namespace Mint;

/**
 * Class Slp
 * @package Mint
 */
class Slp {

    const NETWORK_MAIN = 'mainnet';
    const NETWORK_TEST = 'testnet';

    const FUNDS_WARNING_LEVEL = 2000;

    /**
     * @var string
     */
    private $cookieName = 'walletId';

    /**
     * @var bool
     */
    private $testnet = true;

    /**
     * @var array
     */
    private $tokenBalance;

    /**
     * @param $url
     * @param $info
     * @return mixed
     * @throws \Exception
     */
    private function send($url, $info) {
        $dev = isset($_GET['dev']);

        $port = 3000;
        if($dev) {
            $port = 3001;
        }

        $json = str_replace("\"", "\\\"", json_encode($info));
        $command = "curl -s -X POST http://127.0.0.1:".$port."/" . $url . " -H \"Content-Type: application/json\" -d \"" . $json ."\"";
        exec($command, $output);

        $result = json_decode($output[0]);
        if(isset($result->message)) {
            throw new \Exception('Wallet communication error: ' . $result->message);
        }
        return $result;
    }

    /**
     * Resets wallet storage data
     */
    public function forgetWallet() {
        $_SESSION[$this->cookieName] = null;
    }

    /**
     * @param null $testnet
     * @return mixed
     * @throws \Exception
     */
    public function generateWallet($testnet = null) {
        if($testnet === null) {
            $testnet = $this->testnet;
        }
        $result = $this->send('wallet/slp/create', ['network' => ($testnet?'testnet':'mainnet')]);
        return $result;
    }

    /**
     * @param $tokenId
     * @return mixed
     * @throws \Exception
     */
    public function getTokenInfo($tokenId) {
        return $this->send('wallet/slp/token_info', ['walletId' => $this->getWalletId(), 'tokenId' => $tokenId]);
    }

    /**
     * @param Wallet $wallet
     */
    public function setWallet(Wallet $wallet) {
        $_SESSION[$this->cookieName] = $wallet;
    }

    /**
     * @return string|null
     */
    public function getWalletId() {
        if(!isset($_SESSION[$this->cookieName])) {
            return null;
        }
        /** @var Wallet $wallet */
        $wallet = $_SESSION[$this->cookieName];
        if($wallet instanceof Wallet) {
            return $wallet->generateWalletId();
        }

        return null;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getBalance() {
        $result = $this->send('wallet/balance', ['walletId' => $this->getWalletId()]);
        return $result->bch;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getSlpBalance() {
        if(!isset($this->tokenBalance)) {
            $this->tokenBalance = $this->send('wallet/slp/all_balances', ['walletId' => $this->getWalletId()]);
        }
        return $this->tokenBalance;
    }

    /**
     * @return string Base64 encoded image - QR code
     * @throws \Exception
     */
    public function getAddrQR() {
        $result = $this->send('wallet/slp/deposit_qr', ['walletId' => $this->getWalletId()]);
        return $result->src;
    }

    /**
     * @param boolean $slp
     * @return mixed
     * @throws \Exception
     */
    public function getAddr($slp = false) {
        $result = $this->send('wallet/' . ($slp?'slp/':'') . 'deposit_address', ['walletId' => $this->getWalletId()]);
        if($slp) {
            return $result->slpaddr;
        } else {
            return $result->cashaddr;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getParentTokens() {
        $parentTokens = [];
        foreach($this->getSlpBalance() as $token) {
            if($token->type == 129) {
                $parentTokens[] = $token;
            }
        }
        return $parentTokens;
    }

    /**
     * @param $parent
     * @return array
     * @throws \Exception
     */
    public function getChildTokens($parent) {
        $children = [];
        foreach($this->getSlpBalance() as $token) {
            if($token->type == 65 AND $token->parentTokenId === $parent) {
                $children[] = $token;
            }
        }
        return $children;
    }

    /**
     * @param $name
     * @param $ticker
     * @param $docUrl
     * @param $docHash
     * @throws \Exception
     */
    public function mintParent($name, $ticker, $docUrl, $docHash) {
        return $this->send('wallet/slp/nft_parent_genesis', [
            'walletId' => $this->getWalletId(),
            'name' => $name,
            'ticker' => $ticker,
            'initialAmount' => "100000",
            'decimals' => 0,
            'documentUrl' => $docUrl,
            'documentHash' => $docHash,
            'endBaton' => false,
            'tokenReceiverSlpAddr' => $this->getAddr(true),
            'batonReceiverSlpAddr' => $this->getAddr(true),
        ]);
    }

    /**
     * @param $parentToken
     * @param $name
     * @param $ticker
     * @param $docUrl
     * @param $docHash
     * @param $tokenReceiver
     * @throws \Exception
     */
    public function mintChild($parentToken, $name, $ticker, $docUrl, $docHash, $tokenReceiver) {
        return $this->send('wallet/slp/nft_child_genesis', [
            'walletId' => $this->getWalletId(),
            'name' => $name,
            'ticker' => $ticker,
            'initialAmount' => 1,
            'decimals' => 0,
            'documentUrl' => $docUrl,
            'documentHash' => $docHash,
            'endBaton' => false,
            'parentTokenId' => $parentToken,
            'tokenReceiverSlpAddr' => $tokenReceiver,
            'batonReceiverSlpAddr' => $this->getAddr(true),
        ]);
    }

    /**
     * @return Wallet
     */
    public function getWallet() {
        return $_SESSION[$this->cookieName];
    }
}