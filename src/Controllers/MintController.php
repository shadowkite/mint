<?php

use Mint\Slp;
use Mint\Sanitizer;

class MintController extends \Controller {
    private $slp;
    public function __construct() {
        $this->slp = new Slp();
    }
    public function indexAction() {
        if(isset($_POST['submit'])) {
            $seedParts = ['seed'];
            $seedParts[] = Sanitizer::network($_POST['network']);
            $seedParts[] = Sanitizer::seed($_POST['seed']);
            $seedParts[] = Sanitizer::derivationPath($_POST['derivationPath']);
            $this->slp->setWalletId(implode(':', $seedParts));
        }
        $this->view->slp = $this->slp;
    }

    public function collectionsAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->view->slp = $this->slp;
        $this->view->tokens = $this->slp->getParentTokens();
    }

    public function forgetAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->slp->forgetWallet();
        $this->redirect('/mint/index');
    }

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
                $this->slp->mintChild(
                    Sanitizer::hex($_POST['parent']),
                    Sanitizer::tokenName($_POST['name']),
                    Sanitizer::tokenName($_POST['ticker']),
                    Sanitizer::url($_POST['docUrl']),
                    Sanitizer::hex($_POST['docHash']),
                    Sanitizer::address($_POST['receiver']));
            } catch(\Exception $e) {
                $this->view->error = $e->getMessage();
            }
        }
    }

    public function parentAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        if(isset($_POST['submit'])) {
            try {
                $this->slp->mintParent(
                    Sanitizer::tokenName($_POST['name']),
                    Sanitizer::tokenName($_POST['ticker']),
                    Sanitizer::url($_POST['docUrl']),
                    Sanitizer::hex($_POST['docHash']));
            } catch(\Exception $e) {
                $this->view->error = $e->getMessage();
            }
        }
    }
}