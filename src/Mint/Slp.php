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

    const WALLET_GROUP_RECEIVE = 0;
    const WALLET_GROUP_CHANGE = 1;
    const WALLET_GROUP_SALE = 2;
    const WALLET_GROUP_BUY = 3;

    /**
     * @var Wallet
     */
    private Wallet $wallet;

    /**
     * @var bool
     */
    private bool $testnet = true;

    /**
     * @var array
     */
    private array $tokenBalance;

    /**
     * @var int seed group
     */
    private int $group = 0;

    /**
     * @var int seed address index
     */
    private int $addressIndex = 0;

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
     * @param null $testnet
     * @return mixed
     * @throws \Exception
     */
    public function generateWallet($testnet = null) {
        if($testnet === null) {
            $testnet = $this->testnet;
        }
        $result = $this->send('wallet/slp/create', ['network' => ($testnet?Slp::NETWORK_TEST:Slp::NETWORK_MAIN)]);
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
        $this->wallet = $wallet;
    }

    /**
     * @return string|null
     */
    public function getWalletId() {
        if(!$this->wallet) {
            return null;
        }
        /** @var Wallet $wallet */
        return $this->wallet->generateWalletId($this->group, $this->addressIndex);
    }

    /**
     * @param string $type
     * @return mixed
     * @throws \Exception
     */
    public function getBalance($type = 'sat') {
        $result = $this->send('wallet/max_amount_to_send', ['walletId' => $this->getWalletId(), 'slpAware' => true]);
        switch($type) {
            case 'sat': return $result->sat;
            case 'usd': return $result->usd;
            case 'bch': return $result->bch;
        }
        return null;
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
    public function getChildTokens($parent = null) {
        $children = [];
        foreach($this->getSlpBalance() as $token) {
            if($token->type == 65
                AND ($parent === null OR ($parent !== null AND $token->parentTokenId === $parent))
            ) {
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
     * @param $receiver
     * @return mixed
     * @throws \Exception
     */
    public function sendAll($receiver) {
        return $this->send('wallet/send_max', [
            'walletId' => $this->getWalletId(),
            'cashaddr' => $receiver
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
        return $this->wallet;
    }

    public function sendFunds($receiver, $sats) {
        return $this->send('wallet/send', [
            'walletId' => $this->getWalletId(),
            'to' => [
                'cashaddr' => $receiver,
                'value' => $sats,
                'unit' => 'sat',
            ],
            'options' => [
                'slpAware' => true
            ]
        ]);
    }

    public function checkFunds($addressIndex, $amount, $tokenId = null) {
        if($tokenId) {
            $slpTokens = $this->getSlpBalance();
            foreach($slpTokens as $tokens) {
                if($tokenId == $tokens->tokenId) {
                    if($tokens->value >= $amount) {
                        return true;
                    }
                }
            }
            return false;
        } else {
            $balance = $this->getBalance();
            if($balance >= $amount) {
                return true;
            }
            return false;
        }
    }

    public function sendToken($tokenId, $receiver, $amount = 1) {
        $data = [];
        $data['walletId'] = $this->getWalletId();
        $data['to'] = [];
        $data['to'][] = [
            'slpaddr' => $receiver,
            'value' => $amount,
            'tokenId' => $tokenId
        ];
        $data['options'] = [
            'slpAware' => true
        ];
        return $this->send('wallet/slp/send', $data);
    }

    /**
     * @return int
     */
    public function getGroup(): int
    {
        return $this->group;
    }

    /**
     * @param int $group
     */
    public function setGroup(int $group): void
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getAddressIndex(): int
    {
        return $this->addressIndex;
    }

    /**
     * @param int $addressIndex
     */
    public function setAddressIndex(int $addressIndex): void
    {
        $this->addressIndex = $addressIndex;
    }

    public function getNewSLP($group = 0, $addrIndex = 0) {
        $slp = new Slp;
        $wallet = clone $this->getWallet();
        $slp->setWallet($wallet);
        $slp->setGroup($group);
        $slp->setAddressIndex($addrIndex);

        return $slp;
    }
}