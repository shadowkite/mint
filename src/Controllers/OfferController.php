<?php

use Mint\Controller;

/**
 * Class OfferController
 */
class OfferController extends Controller {

    const MNC_TOKEN = '132731d90ac4c88a79d55eae2ad92709b415de886329e958cf35fdd81ba34c15';

    public function __construct() {
        parent::__construct();
    }

    public function indexAction() {
        $this->view->tokens = $this->slp->getChildTokens();
    }

    public function listAction() {
        $salesRepository = $this->em->getRepository(\Mint\Models\Sale::class);
        /** @var \Mint\Models\Sale[] $sales */
        $sales = $salesRepository->findBy([
            'sold' => false
        ]);
        $this->view->sales = $sales;
    }

    private function resolve($purchaseHoldId) {
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

        $this->view->sale = $sale;
        $this->view->purchaseHold = $purchaseHold;
        $this->view->buyerSlp = $buyerSlp;
        $this->view->sellerSlp = $sellerSlp;
        $this->view->qr = \Mint\SaleHelper::getQR($sale, $purchaseHold);

        $this->view->paid = false;
        if($buyerSlp->checkFunds($purchaseHold->getId(), $sale->getCostAmount(), $sale->getCostTokenId())) {
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
        $this->resolve($_GET['hold']);
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
                $am = $purchaseHoldRepository->findBy(['sale' => $_POST['sale']]);
                if(count($am)) {
                    die('Hold already exists');
                }

                $purchaseHold = new \Mint\Models\PurchaseHold();
                $purchaseHold->setSale($_POST['sale']);
                $purchaseHold->setTimestamp(time());
                $purchaseHold->setTokenReceiver($_POST['tokenReceiver']);
                $purchaseHold->setFunded(false);
                $this->em->persist($purchaseHold);
                $this->em->flush();
                $this->redirect('/offer/resolve?hold=' . $purchaseHold->getId());
            }

            $repository = $this->em->getRepository(\Mint\Models\Sale::class);
            /** @var \Mint\Models\Sale $sale */
            $sale = $repository->find($_GET['sale']);
            $this->view->sale = $sale;
        } catch (\Exception $e) {
            var_dump($e);
            $this->view->error = $e->getMessage();
        }
    }

    public function newAction() {
        $tokenOffer = \Mint\Sanitizer::hex($_GET['nft']);
        $this->view->nft = $tokenOffer;
        if(!$this->slp->checkFunds(0, 1, $tokenOffer)) {
            $this->view->error = "You do not own this asset";
            return;
        }
        if($this->slp->getBalance('sat') < 3000) {
            $this->view->error = "To create a sale you need at least 3.000 satoshi";
            return;
        }

        $saleSlp = $this->slp->getNewSLP();
        try {
            if (isset($_POST['submit'])) {
                $tokenInfo = $saleSlp->getTokenInfo(\Mint\Sanitizer::hex($_POST['tokenId']));

                $sale = new \Mint\Models\Sale();
                $sale->setCostAmount((float) $_POST['amount']);
                $sale->setCostTokenId($tokenInfo->tokenId);
                $sale->setCostTokenTicker($tokenInfo->ticker);
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
            $this->em->rollback();
            $this->view->error = $e->getMessage();
        }
    }

    public function salesAction() {
        $saleRepository = $this->em->getRepository(\Mint\Models\Sale::class);
        $sales = $saleRepository->findBy([
            'seller' => hash('sha256', $this->slp->getWalletId()),
            'sold' => true
        ]);
        $this->view->sales = $sales;
    }
}