<?php

use Mint\Slp;
use Mint\Sanitizer;

class MintController extends \Controller {

    /**
     * @var Slp
     */
    private $slp;

    /**
     * MintController constructor.
     */
    public function __construct() {
        $this->slp = new Slp();
    }

    /**
     * Index action
     */
    public function indexAction() {
        if(isset($_POST['submit'])) {
            $wallet = new \Mint\Wallet(
                Sanitizer::network($_POST['network']),
                Sanitizer::seed($_POST['seed']),
                Sanitizer::derivationPath($_POST['derivationPath'])
            );
            $this->slp->setWallet($wallet);
        }
        $this->view->slp = $this->slp;
    }

    /**
     * Display collections
     * @throws Exception
     */
    public function collectionsAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->view->slp = $this->slp;
        $this->view->tokens = $this->slp->getParentTokens();
    }

    /**
     * Forget the wallet and redirect
     */
    public function forgetAction() {
        $this->slp->forgetWallet();
        $this->redirect('/mint/index');
    }

    /**
     * Mint NFT
     */
    public function childAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }

        $this->view->slp = $this->slp;
        $this->view->collection = null;
        if(isset($_GET['collection'])) {
            $this->view->collection = Sanitizer::hex($_GET['collection']);
        }

        if(isset($_POST['submit'])) {
            try {
                $result = $this->slp->mintChild(
                    Sanitizer::hex($_POST['parent']),
                    Sanitizer::tokenName($_POST['name']),
                    Sanitizer::tokenName($_POST['ticker']),
                    Sanitizer::url($_POST['docUrl']),
                    Sanitizer::hex($_POST['docHash']),
                    Sanitizer::address($_POST['receiver']));

                $this->view->success = true;
                $this->view->tokenId = $result->tokenId;
            } catch(\Exception $e) {
                $this->view->error = $e->getMessage();
            }
        }
    }

    /**
     * Mint collection
     */
    public function parentAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }

        $this->view->slp = $this->slp;
        if(isset($_POST['submit'])) {
            try {
                $result = $this->slp->mintParent(
                    Sanitizer::tokenName($_POST['name']),
                    Sanitizer::tokenName($_POST['ticker']),
                    Sanitizer::url($_POST['docUrl']),
                    Sanitizer::hex($_POST['docHash']));

                $this->view->success = true;
                $this->view->tokenId = $result->tokenId;
            } catch(\Exception $e) {
                $this->view->error = $e->getMessage();
            }
        }
    }

    public function plusAction() {
        $wallet = $this->slp->getWallet();
        $wallet->setIndex($wallet->getIndex() + 1);
        $this->redirect('/mint/index');
    }

    public function networkAction() {
        $this->slp->getWallet()->setNetwork();
        $this->redirect('/mint/index');
    }
}