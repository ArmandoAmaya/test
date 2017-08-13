<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace app\controllers;

use app\models as Model;
use Ocrend\Kernel\Router\RouterInterface;
use Ocrend\Kernel\Controllers\Controllers;
use Ocrend\Kernel\Controllers\ControllersInterface;
  
/**
 * Controlador login/
 *
 * @author DevSystemVzla <prinick@ocrend.com>
*/
  
class loginController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router, array(
        	'users_not_logged'=> true
        	));   
        // Contenido del controlador... 
		echo $this->template->render('login/login');

    }

}