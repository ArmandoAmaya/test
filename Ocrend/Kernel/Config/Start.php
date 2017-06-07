<?php

use Ocrend\Kernel\Config\Config;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

# Obtener la configuración
$config = (new Config)->readConfig();

# Iniciar las sesiones
($session = new Session)->start();

# Iniciamos el request 
$request = Request::createFromGlobals();