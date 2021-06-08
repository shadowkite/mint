<?php

namespace Mint;

use chillerlan\QRCode\QRCode;
use Mint\Models\PurchaseHold;
use Mint\Models\Sale;

class SaleHelper {
    public static function getBuyerSlp(Sale $sale, PurchaseHold $purchaseHold) {
        $buyerSlp = new Slp();
        $buyerSlp->setWallet(unserialize($sale->getOfferWallet()));
        return $buyerSlp->getNewSLP(Slp::WALLET_GROUP_BUY, $purchaseHold->getId());
    }

    public static function getSellerSlp(Sale $sale) {
        $sellerSlp = new Slp();
        $sellerSlp->setWallet(unserialize($sale->getOfferWallet()));
        return $sellerSlp->getNewSLP(Slp::WALLET_GROUP_SALE, $sale->getId());
    }

    public static function getQR(Sale $sale, PurchaseHold $hold) {
        $qr = new QRCode();
        $data = self::getBuyerSlp($sale, $hold)->getAddr(true) . "?amount1=" . $sale->getCostAmount() . "-" . $sale->getCostTokenId();
        return $qr->render($data);
    }

    public static function getResolvedPurchaseHolds(Sale $sale) {
        $em = Db::getEM();
        $repository = $em->getRepository(PurchaseHold::class);
        $purchaseHolds = $repository->findBy([
            'sale' => $sale->getId(),
            'funded' => true
        ]);
        return $purchaseHolds;
    }
}