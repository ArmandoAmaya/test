<?php

namespace Ocrend\Kernel\Controllers;

use Ocrend\Kernel\Router\RouterInterface;

abstract class Controllers {
    
    public function __construct(RouterInterface $router) {

    }

    public function __destruct() {

    }

}