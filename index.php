<?php
//FRONT CONTROLLER

//1. Общие настройки
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

//2. Подключение файлов системы
    define('ROOT', dirname(__FILE__));
    define('APP', ROOT."/application");
    session_start();
    require_once(APP.'/components/Autoload.php');

//3. Общие данные
mb_internal_encoding("UTF-8");


//4. Выов Router
    $router = new Router;
    $router->run();