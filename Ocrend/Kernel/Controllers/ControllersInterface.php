<?php

namespace Ocrend\Kernel\Controllers;

use Ocrend\Kernel\Router\RouterInterface;

interface ControllersInterface {
    public function __construct(RouterInterface $router);
}