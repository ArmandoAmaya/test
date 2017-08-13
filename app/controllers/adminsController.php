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
 * Controlador admins/
 *
 * @author DevSystemVzla <prinick@ocrend.com>
*/
  
class adminsController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        global $config;
        parent::__construct($router,array(
            'users_logged' => true
        ));   

        # Instancia del modelo
        $a = new Model\Admins($router);

        # Evaluación el método
        switch ($this->method) {
            # Acción Crear
        	case 'crear':
        		echo $this->template->render('admins/crear');
        	break;
            # Acción editar
        	case 'editar':
	        	if ($this->isset_id && false != ($item = $a->leer(false))) {
	        		echo $this->template->render('admins/editar',array(
                        'data' => $item[0],
                        'avatar' => $a->getUserImage($item[0]['avatar'], $item[0]['id_user'])
                    ));
	        	}else{
	        		$this->functions->redir($config['site']['url'].'admins/');
	        	}
        		
        	break;
            # Acción eliminar
        	case 'eliminar':
        	   $a->borrar();
        	break;
        	# Por defecto, mostrar
        	default:
        		echo $this->template->render('admins/admins',array(
                    'data' => $a->leer()
                ));
        	break;
        }
		

    }

}