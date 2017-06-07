<?php

namespace app\controllers;

use app\models;
use Ocrend\Kernel\Router\RouterInterface;
use Ocrend\Kernel\Controllers\Controllers;
use Ocrend\Kernel\Controllers\ControllersInterface;
use Ocrend\Kernel\Controllers\ControllersException;

class homeController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router);

        echo $router->getController();
    }

}