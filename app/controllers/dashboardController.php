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
 * Controlador home/
 *
 * @author Brayan Narváez <prinick@ocrend.com>
*/

class dashboardController extends Controllers implements ControllersInterface {

    public function __construct(RouterInterface $router) {
        parent::__construct($router,array(
        	'users_logged' => true
        ));   

        $d = new Model\Dashboard;
        
        echo $this->template->render('dashboard/dashboard',array(
        	'count_admins' => $d->getEntity('count(id_user)', 'users'),
        	'count_categories' => $d->getEntity('count(id_categorias)', 'categorias'),
        	'projects' => $d->getEntity('id_proyectos,titulo,short_desc_es,short_desc_en,portada,logo', 'proyectos', '1=1 ORDER BY id_proyectos DESC', 'LIMIT 4'),
        	'count_home' => $d->getEntity('count(id_home_slider)', 'home_sliders'),
            'visitas' => $d->getEntity('contador', 'visitas', "id_visita = '1'", 'LIMIT 1')
        ));
    }

}