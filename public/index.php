<?php

// if(!isset($_GET['testmode'])) { die("Down for maintenance, please try later."); }
error_reporting(E_ALL ^ E_WARNING);
// ini_set('display_errors', 'on');

require_once __DIR__ . '/../vendor/autoload.php';
session_start();
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
