<?php

use Ocrend\Kernel\Router\Router;

# Definir ruta de acceso permitida
define('API_INTERFACE', '');

# Cargadores principales
require 'Ocrend/vendor/autoload.php';
require 'Ocrend/autoload.php';
require 'Ocrend/Kernel/Config/Start.php';

# Ejecutar controlador solicitado
(new Router)->executeController();