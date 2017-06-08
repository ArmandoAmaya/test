<?php

namespace app\models;

use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsInterface;
use Ocrend\Kernel\Router\RouterInterface;

class Ejemplo extends Models implements ModelsInterface {

    public function __construct(RouterInterface $router = null) {
        parent::__construct($router);

        echo 'ja weno';
    }

    public function __destruct() {
        parent::__destruct();
    }

}