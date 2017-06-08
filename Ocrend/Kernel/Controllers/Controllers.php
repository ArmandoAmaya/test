<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ocrend\Kernel\Controllers;

use Ocrend\Kernel\Router\RouterInterface;

/**
 * Clase para conectar todos los controladores del sistema y compartir la configuración.
 * Inicializa aspectos importantes de una página, como el sistema de plantillas twig.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
 */

abstract class Controllers {
    
    /**
      * Obtiene el objeto del template 
      *
      * @var Twig_Environment
    */
    protected $template;

    /**
      * Verifica si está definida la ruta /id como un integer >= 1
      *
      * @var bool
    */
    protected $isset_id = false;

    /**
      * Tiene el valor de la ruta /método
      *
      * @var string|null
    */
    protected $method;

     /**
      * Tiene el valor de la ruta /método
      *
      * @param RouterInterface $router: Instancia de un Router
      * @param bool $twig_reload: true activa la recarga de caché estricto de twig
      *                           false desactiva la recarga de caché estricto de twig    
    */
    public function __construct(RouterInterface $router, bool $twig_reload = true) {
        global $config, $http, $session;

        # Twig Engine http://gitnacho.github.io/Twig/
        $this->template = new \Twig_Environment(new \Twig_Loader_Filesystem('./app/templates/'), array(
            # ruta donde se guardan los archivos compilados
            'cache' => './app/templates/.cache/',
            # false para caché estricto, cero actualizaciones, recomendado para páginas 100% estáticas
            'auto_reload' => $twig_reload,
            # en true, las plantillas generadas tienen un método __toString() para mostrar los nodos generados
            'debug' => $config['framework']['debug']
        )); 
        
        # Request global
        $this->template->addGlobal('http', $http);
        $this->template->addGlobal('session', $session);

        # Auxiliares
        $this->method = $router->getMethod();
        $this->isset_id = (bool) (is_numeric($router->getID()) && $router->getID() >= 1);
    }

}