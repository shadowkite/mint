<?php

/**
 * Class IndexController
 */
class IndexController extends \Controller {

    /**
     * Index action
     */
    public function indexAction() {
        $this->redirect('/mint/index');
    }

    public function saleaccountAction() {
        $selling = (isset($_SESSION['selling']) && $_SESSION['selling']);
        $_SESSION['selling'] = (!$selling);
        $this->redirect('/mint/index');
    }
}