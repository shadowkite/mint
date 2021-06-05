<?php

/**
 * Class IndexController
 */
class IndexController extends \Controller {

    /**
     * Index action
     */
    public function indexAction() {
        $this->redirect('mint/index');
    }
}