<?php

# Definir ruta de acceso permitida
define('API_INTERFACE', '../');

# Cargadores principales
require '../Ocrend/vendor/autoload.php';
require '../Ocrend/autoload.php';
require '../Ocrend/Kernel/Config/Start.php';

# Preparar la API
$app = new Silex\Application();

# Verbos HTTP
require 'http/get.php';
require 'http/post.php';
require 'http/put.php';
require 'http/delete.php';

# Arrancar
$app->run();
