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
 * Controlador categorias/
 *
 * @author DevSystemVzla <prinick@ocrend.com>
*/
  
class categoriasController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router,array(
            'users_logged' => true
        ));   
        global $config;
        
        $c = new Model\Categorias($router);

        switch($this->method) {
          case 'crear':
            echo $this->template->render('categorias/crear');
          break;
          case 'editar':
            if($this->isset_id and false !== ($data = $c->get(false))) {
              echo $this->template->render('categorias/editar', array(
                'data' => $data[0]
              ));
            } else {
              $this->functions->redir($config['site']['url'] . 'categorias/&error=true');
            }
          break;
          case 'eliminar':
            $c->delete();
          break;
          default:
            echo $this->template->render('categorias/categorias',array(
              'data' => $c->get()
            ));
          break;
        }
    }

}