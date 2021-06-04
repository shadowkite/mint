<?php

session_start();
//if(!isset($_GET['testmode'])) { die("Down for maintenance, please try later."); }
error_reporting(E_ALL ^ E_WARNING);

require_once __DIR__ . '/../vendor/autoload.php';
$app = new \Application();
$app->setControllerPath(__DIR__ . '/../src/Controllers');
$app->bootstrap("view", function(){
    $view = new \View();
    $view->addViewPath(__DIR__ . '/../src/Views');
    return $view;
});
$app->bootstrap("layout", function(){
    $layout = new \Layout();
    $layout->addViewPath(__DIR__ . '/../src/Layouts');
    return $layout;
});
$app->run();
