<?php

use Mint\Controller;

/**
 * Class OfferController
 */
class OfferController extends Controller {

    const TOKENS = [
        'MNC' => ['network' => 'testnet', 'tokenId' => '132731d90ac4c88a79d55eae2ad92709b415de886329e958cf35fdd81ba34c15'],
        'ASHGOLD' => ['network' => 'mainnet', 'tokenId' => '27944ef5b92eee4ca8891541c9c8085bc859aa3c6a2c08ba6ecd46bb7e285a27'],
        'ANFTR' => ['network' => 'mainnet', 'tokenId' => 'a15ee1eb4e03ff41013b5db3c0b82a0da568b6607d5a2750fadcb57f2afd4f01'],
        '😈' => ['network' => 'mainnet', 'tokenId' => 'd7fdda8351e9466d9a51c8b2c1a7ee5aa47b122e2116ae4efcef8f698bc4ec60'],
    ];

    public function __construct() {
        parent::__construct();
    }

    public function indexAction() {
        if($this->slp->getWallet()) {
            $this->view->tokens = $this->slp->getChildNfts();
        } else {
            $this->redirect('/');
        }
    }

    public function listAction() {
        $salesRepository = $this->em->getRepository(\Mint\Models\Sale::class);
        /** @var \Mint\Models\Sale[] $sales */
        $sales = $salesRepository->findBy([
            'sold' => false
        ]);

        $nfts = [];
        foreach($sales as $sale) {
            $nft = \Mint\Nft::factory($sale->getOfferNft());
            $nft->setSale($sale);
            $nfts[] = $nft;
        }
        $this->view->nfts = $nfts;
    }

    private function resolve($purchaseHoldId, $checkFunds = false) {
        $purchaseHoldRepository = $this->em->getRepository(\Mint\Models\PurchaseHold::class);
        $saleRepository = $this->em->getRepository(\Mint\Models\Sale::class);

        /** @var \Mint\Models\PurchaseHold $purchaseHold */
        $purchaseHold = $purchaseHoldRepository->find($purchaseHoldId);
        if(!$purchaseHold) {
            throw new \Exception('Hold does not exist');
        }

        /** @var \Mint\Models\Sale $sale */
        $sale = $saleRepository->find($purchaseHold->getSale());

        $buyerSlp = \Mint\SaleHelper::getBuyerSlp($sale, $purchaseHold);
        $sellerSlp = \Mint\SaleHelper::getSellerSlp($sale);

        $nft = \Mint\Nft::factory($sale->getOfferNft());
        $nft->setSale($sale);

        $this->view->nft = $nft;
        $this->view->sale = $sale;
        $this->view->purchaseHold = $purchaseHold;
        $this->view->buyerSlp = $buyerSlp;
        $this->view->sellerSlp = $sellerSlp;
        $this->view->qr = \Mint\SaleHelper::getQR($sale, $purchaseHold);
        $this->view->qr_invoice = \Mint\SaleHelper::getQRInvoice($sale, $purchaseHold);

        $_5m = (5*60);
        $secondsPassed = ($purchaseHold->getTimestamp() + $_5m) - time();
        $this->view->percentageLeft = max(($secondsPassed / $_5m) * 100, 0);
        $this->view->secondsLeft = $secondsPassed;
        $this->view->purchaseHoldEnd = ($purchaseHold->getTimestamp() + $_5m);

        $this->view->paid = false;
        if($checkFunds && $buyerSlp->checkFunds($purchaseHold->getId(), $sale->getCostAmount(), $sale->getCostTokenId())) {
            try {
                $sellerSlp->sendToken($sale->getOfferNFT(), $purchaseHold->getTokenReceiver(), 1);
                $purchaseHold->setFunded(true);
                $sale->setSold(true);

                $this->em->persist($purchaseHold);
                $this->em->persist($sale);
                $this->em->flush();

                $this->view->paid = true;
            } catch(\Exception $e) {
                echo "Sale error " . $e->getMessage();
            }
        }
    }

    public function resolveJsonAction() {
        header('Content-type: application/json');
        $this->disableLayout();
        $this->resolve($_GET['hold'], true);
    }

    public function resolveAction() {
        try {
            $this->resolve($_GET['hold']);
        } catch(\Exception $e) {
            var_dump($e);
        }
    }

    public function viewAction() {
        try {
            if(isset($_POST['submit'])) {
                $purchaseHoldRepository = $this->em->getRepository(\Mint\Models\PurchaseHold::class);

                /** @var \Mint\Models\PurchaseHold[] $holds */
                $holds = $purchaseHoldRepository->findBy(['sale' => $_POST['sale']]);
                $verifiedHolds = [];
                foreach($holds as $hold) {
                    if($hold->getTimestamp() + (5*60) < time()) {
                        if(!$hold->isExpired()) {
                            $hold->setFunded(false);
                            $hold->setExpired(true);
                            $this->em->persist($hold);
                            $this->em->flush();
                        }
                    } else {
                        $verifiedHolds[] = $hold;
                    }
                }

                if(count($verifiedHolds) > 0) {
                    $this->view->error = "Purchase hold already exists.";
                } else {
                    $purchaseHold = new \Mint\Models\PurchaseHold();
                    $purchaseHold->setSale($_POST['sale']);
                    $purchaseHold->setTimestamp(time());
                    $purchaseHold->setTokenReceiver($_POST['tokenReceiver']);
                    $purchaseHold->setFunded(false);
                    $this->em->persist($purchaseHold);
                    $this->em->flush();
                    $this->redirect('/offer/resolve?hold=' . $purchaseHold->getId());
                }
            }

            $saleId = $_GET['sale'];
            $repository = $this->em->getRepository(\Mint\Models\Sale::class);
            /** @var \Mint\Models\Sale $sale */
            $sale = $repository->find($saleId);
            $nft = \Mint\Nft::factory($sale->getOfferNft());
            $nft->setSale($sale);

            $this->view->nft = $nft;
        } catch (\Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    public function newAction() {
        $tokenOffer = \Mint\Sanitizer::hex($_GET['nft']);
        $saleSlp = $this->slp->getNewSLP();
        $nft = \Mint\Nft::factory($tokenOffer);

        $this->view->nft = $nft;
        if(!$this->slp->checkFunds(0, 1, $tokenOffer)) {
            $this->view->error = "You do not own this asset";
            return;
        }
        if($this->slp->getBalance('sat') < 3000) {
            $this->view->error = "To create a sale you need at least 3.000 satoshi";
            return;
        }
        $this->view->slp = $this->slp;

        try {
            if (isset($_POST['submit'])) {

                $costTokenInfo = $saleSlp->getTokenInfo(\Mint\Sanitizer::hex($_POST['tokenId']));

                $sale = new \Mint\Models\Sale();
                $sale->setCostAmount((float) $_POST['amount']);
                $sale->setCostTokenId($costTokenInfo->tokenId);
                $sale->setCostTokenTicker($costTokenInfo->ticker);
                $sale->setOfferNft($tokenOffer);
                $sale->setSeller(hash('sha256', $saleSlp->getWalletId()));

                $saleSlp->setGroup(\Mint\Slp::WALLET_GROUP_SALE);
                $sale->setOfferWallet(serialize($saleSlp->getWallet()));
                $sale->setSold(false);

                $this->em->persist($sale);
                $this->em->flush();

                $saleSlp->setAddressIndex($sale->getId());

                // @TODO Check balance here
                $this->slp->sendFunds($saleSlp->getAddr(true), 1500);
                $this->slp->sendToken($tokenOffer, $saleSlp->getAddr(true), 1);

                $this->view->success = true;
                $this->view->sale = $sale;

                $this->redirect('/offer/view?sale=' . $sale->getId());
            }
        } catch(\Exception $e) {
            $this->view->error = $e->getMessage();
        }
    }

    /**
     *
     */
    public function salesAction() {
        $saleRepository = $this->em->getRepository(\Mint\Models\Sale::class);
        $sales = $saleRepository->findBy([
            'seller' => hash('sha256', $this->slp->getWalletId()),
            'sold' => true
        ]);
        $nfts = [];
        foreach($sales as $sale) {
            $nfts[] = $nft = \Mint\Nft::factory($sale->getOfferNFT());
            $nft->setSale($sale);
        }

        $this->view->nfts = $nfts;
    }

    /**
     *
     */
    public function claimAction() {
        try {
            /** @var \Mint\Models\Sale $sale */
            $sale = $this->em
                ->getRepository(\Mint\Models\Sale::class)
                ->find($_GET['sale']);

            /** @var \Mint\Models\PurchaseHold[] $holds */
            $holds = $this->em
                ->getRepository(\Mint\Models\PurchaseHold::class)
                ->findBy([
                    'sale' => $sale->getId(),
                    'funded' => true
                ]);

            $sellerSlp = \Mint\SaleHelper::getSellerSlp($sale);
            foreach ($holds as $hold) {
                $slp = \Mint\SaleHelper::getBuyerSlp($sale, $hold);
                $sellerSlp->sendAll($slp->getAddr());
                $slp->sendToken(
                    $sale->getCostTokenId(),
                    $this->slp->getAddr(true),
                    $sale->getCostAmount()
                );
                $sale->setClaimed(true);
            }
            $this->em->persist($sale);
            $this->em->flush();
        } catch(\Exception $e) {

        }
        $this->redirect('/offer/sales');
    }
}