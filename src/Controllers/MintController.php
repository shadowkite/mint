<?php

use Mint\Slp;

class MintController extends \Controller {
    private $slp;
    public function __construct() {
        $this->slp = new Slp();
    }
    public function indexAction() {
        if(isset($_POST['submit'])) {
            $this->slp->setWalletId('seed:' . $_POST['network'] . ":" . $_POST['seed'] . ":" . $_POST['derivationPath']);
        }
        $this->getResource('layout')->setScriptName('mint.phtml');
        $this->view->slp = $this->slp;
    }

    public function collectionsAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->getResource('layout')->setScriptName('mint.phtml');
        $this->view->slp = $this->slp;
        $this->view->tokens = $this->slp->getParentTokens();
    }

    public function forgetAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->getResource('layout')->setScriptName('mint.phtml');
        $this->slp->forgetWallet();
        $this->redirect('/mint/index');
    }

    public function childAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->getResource('layout')->setScriptName('mint.phtml');
        $this->view->slp = $this->slp;

        $this->view->collection = null;
        if(isset($_GET['collection'])) {
            $this->view->collection = $_GET['collection']; // @TODO safe check
        }
        if(isset($_POST['submit'])) {
            $this->slp->mintChild(
                $_POST['parent'],
                $_POST['name'],
                $_POST['ticker'],
                $_POST['docUrl'],
                $_POST['docHash']);
        }
    }

    public function parentAction() {
        if (!$this->slp->getWalletId()) {
            $this->redirect('/mint/index');
            return;
        }
        $this->getResource('layout')->setScriptName('mint.phtml');
        try {
            if(isset($_POST['submit'])) {
                $this->slp->mintParent(
                    $_POST['name'],
                    $_POST['ticker'],
                    $_POST['docUrl'],
                    $_POST['docHash']);
            }

        } catch(\Exception $e) {
            var_dump($e);
        }
    }
}