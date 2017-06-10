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
use Ocrend\Kernel\Helpers\Functions;

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
      * Contiene una instancia del helper para funciones
      *
      * @var Ocrend\Kernel\Helpers\Functions
    */
    protected $functions;

     /**
      * Inicia la configuración inicial de cualquier controlador
      *
      * @param RouterInterface $router: Instancia de un Router
      * @param bool $twig_reload: true activa la recarga de caché estricto de twig
      *                           false desactiva la recarga de caché estricto de twig    
    */
    protected function __construct(RouterInterface $router, bool $twig_reload = true) {
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

        # Instanciar las funciones
        $this->functions = new Functions();
        
        # Request global
        $this->template->addGlobal('http', $http);
        $this->template->addGlobal('session', $session);
        $this->template->addGlobal('config', $config);
        $this->template->addExtension($this->functions);

        # Auxiliares
        $this->method = $router->getMethod();
        $this->isset_id = (bool) (is_numeric($router->getID()) && $router->getID() >= 1);
    }

}