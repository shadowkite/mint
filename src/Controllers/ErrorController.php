<?php

class ErrorController extends Controller{
    public function errorAction() {
        $this->view->error = $this->getParam('exception');
    }
}