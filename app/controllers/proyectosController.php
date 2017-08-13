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
 * Controlador proyectos/
 *
 * @author DevSystemVzla <prinick@ocrend.com>
*/
  
class proyectosController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router,array(
          'users_logged' => true
        ));   
        global $config;
        
        $p = new Model\Proyectos($router);

        # Limpiamos el directorio temporal
        $p->clearTmpDir();

        switch($this->method) {
          case 'crear':
            echo $this->template->render('proyectos/crear',array(
              'categorias' => $p->getCategories(),
              'tmp' => $p->createTmpDir()
            ));
          break;
          case 'editar':
            if($this->isset_id and false !== ($data = $p->get(false))) {
            
              echo $this->template->render('proyectos/editar', array(
                'data' => $data[0],
                'categorias' => $p->getCategories(),
                'cat_comp' => $p->categories_convert_array($data[0]['categorias']),
                'gallery' => $p->getGallery(),
                'tmp' => $p->createTmpDir()
              ));
            } else {
              $this->functions->redir($config['site']['url'] . 'proyectos/&error=true');
            }
          break;
          case 'eliminar':
            $p->delete();
          break;
          default:
            echo $this->template->render('proyectos/proyectos',array(
              'data' => $p->get()
            ));
          break;
        }
    }

}