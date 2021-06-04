<?php

namespace Mint;

class Slp {

    private $cookieName = 'walletId';
    private $testnet = true;
    private $tokenBalance;

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
            var_dump($result);
            die();
            //throw new \Exception('Wallet communication error: ' . $result->message);
        }
        return $result;
    }

    public function forgetWallet() {
        $_SESSION[$this->cookieName] = null;
    }

    public function generateWallet($testnet = null) {
        if($testnet === null) {
            $testnet = $this->testnet;
        }
        $result = $this->send('wallet/slp/create', ['network' => ($testnet?'testnet':'mainnet')]);
        return $result;
    }

    public function getTokenInfo($tokenId) {
        return $this->send('wallet/slp/token_info', ['walletId' => $this->getWalletId(), 'tokenId' => $tokenId]);
    }

    public function setWalletId($walletId) {
        $_SESSION[$this->cookieName] = $walletId;
    }

    public function getWalletId() {
        return $_SESSION[$this->cookieName];
    }

    public function getBalance() {
        $result = $this->send('wallet/balance', ['walletId' => $this->getWalletId()]);
        return $result->bch;
    }

    public function getSlpBalance() {
        if(!isset($this->tokenBalance)) {
            $this->tokenBalance = $this->send('wallet/slp/all_balances', ['walletId' => $this->getWalletId()]);
        }
        return $this->tokenBalance;
    }

    public function getAddrQR() {
        $result = $this->send('wallet/slp/deposit_qr', ['walletId' => $this->getWalletId()]);
        return $result->src;
    }

    public function getAddr($slp = false) {
        $result = $this->send('wallet/' . ($slp?'slp/':'') . 'deposit_address', ['walletId' => $this->getWalletId()]);
        if($slp) {
            return $result->slpaddr;
        } else {
            return $result->cashaddr;
        }
    }

    public function getParentTokens() {
        $parentTokens = [];
        foreach($this->getSlpBalance() as $token) {
            if($token->type == 129) {
                $parentTokens[] = $token;
            }
        }
        return $parentTokens;
    }

    public function getChildTokens($parent) {
        $children = [];
        foreach($this->getSlpBalance() as $token) {
            if($token->type == 65 AND $token->parentTokenId === $parent) {
                $children[] = $token;
            }
        }
        return $children;
    }

    public function mintParent($name, $ticker, $docUrl, $docHash) {
        $result = $this->send('wallet/slp/nft_parent_genesis', [
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
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
    }

    public function mintChild($parentToken, $name, $ticker, $docUrl, $docHash) {
        $result = $this->send('wallet/slp/nft_child_genesis', [
            'walletId' => $this->getWalletId(),
            'name' => $name,
            'ticker' => $ticker,
            'initialAmount' => 1,
            'decimals' => 0,
            'documentUrl' => $docUrl,
            'documentHash' => $docHash,
            'endBaton' => false,
            'parentTokenId' => $parentToken,
            'tokenReceiverSlpAddr' => $this->getAddr(true),
            'batonReceiverSlpAddr' => $this->getAddr(true),
        ]);
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
    }
}