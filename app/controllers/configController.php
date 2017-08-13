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
 * Controlador config/
 *
 * @author DevSystemVzla <prinick@ocrend.com>
*/
  
class configController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router,array(
        	'users_logged' => true
        ));   
        
        switch ($this->method) {
        	case 'redes':
        		echo $this->template->render('config/redes',array(
                    'redes' => (new Model\Redes)->get()
                ));
        	break;
        	case 'contact':
                
        		echo $this->template->render('config/contact',array(
                    'contact' => (new Model\Contact)->get()
                ));
                
        	break;
        	
        	default:
        		echo $this->template->render('config/home',array(
        			'sliders' => (new Model\Home)->get()
        		));
        	break;
        }
    }

}