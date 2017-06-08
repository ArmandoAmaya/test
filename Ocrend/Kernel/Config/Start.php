<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 * @author Brayan Narváez <prinick@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Ocrend\Kernel\Config\Config;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

/**
  * Obtiene la configuración inicial del sistema, conexión a la base de datos,
  * constantes de phpmailer, credenciales de la api de paypal, etc.
*/
$config = (new Config)->readConfig();

/**
  * Capa orientada a objetos para el uso de sesiones más seguras en PHP.
*/
($session = new Session)->start();

/**
  * Capa orientada a objetos para reemplazar las peticiones Http $_GET, $_POST, $_FILES, $_COOKIES, $_SERVER
*/
$http = Request::createFromGlobals();