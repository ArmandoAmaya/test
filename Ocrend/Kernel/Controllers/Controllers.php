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

use app\models as Model;
use Ocrend\Kernel\Router\RouterInterface;
use Ocrend\Kernel\Helpers\{Functions, Strings};

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
      * Parámetros de configuración para el controlador con la forma:
      * 'parmáetro' => (bool) valor
      *
      * @var array
    */
    private $controllerConfig;

    /**
      * Configuración inicial de cualquier controlador
      *
      * @param RouterInterface $router: Instancia de un Router
      * @param array|null $configController: Arreglo de configuración con la forma  
      *     'twig_cache_reload' => bool, # Configura el autoreload del caché de twig
      *     'users_logged' => bool, # Configura el controlador para solo ser visto por usuarios logeados
      *     'users_not_logged' => bool, # Configura el controlador para solo ser visto por !(usuarios logeados)
      *
    */
    protected function __construct(RouterInterface $router, $configController = null) {
        global $config, $http, $session;

        # Instanciar las funciones
        $this->functions = new Functions;

        # Establecer la configuración para el controlador
        $this->setControllerConfig($configController);

        # Twig Engine http://gitnacho.github.io/Twig/
        $this->template = new \Twig_Environment(new \Twig_Loader_Filesystem('./app/templates/'), array(
            # ruta donde se guardan los archivos compilados
            'cache' => './app/templates/.cache/',
            # false para caché estricto, cero actualizaciones, recomendado para páginas 100% estáticas
            'auto_reload' => $this->controllerConfig['twig_cache_reload'],
            # en true, las plantillas generadas tienen un método __toString() para mostrar los nodos generados
            'debug' => $config['framework']['debug']
        )); 
        
        # Request global
        $this->template->addGlobal('controller', $router->getController());
        $this->template->addGlobal('get', $http->query->all());
        $this->template->addGlobal('server', $http->server->all());
        $this->template->addGlobal('session', $session->all());
        $this->template->addGlobal('config', $config);
        $this->template->addExtension($this->functions);
        $this->template->addExtension(new Strings);
        $this->template->addExtension(new \Twig_Extension_Debug());

        # Request globales cuando la sesión está iniciada
        if(null !== $session->get('user_id')) {
          $this->user = new Model\Users;
          $this->template->addGlobal('owner_user', $this->user->getOwnerUser());
          $this->template->addGlobal('owner_user_avatar', $this->user->getUserImage($this->user->getOwnerUser()['avatar']) );
        }

        # Verificar para quién está permitido este controlador
        $this->knowVisitorPermissions();

        # Auxiliares
        $this->method = $router->getMethod();
        $this->isset_id = (bool) (is_numeric($router->getID()) && $router->getID() >= 1);
    }

    /**
      * Establece los parámetros de configuración de un controlador
      *
      * @param RouterInterface $router: Instancia de un Router
      * @param array|null $config: Arreglo de configuración   
      *
      * @return void
    */
    private function setControllerConfig($config) {
      # Configuración por defecto
      $this->controllerConfig['twig_cache_reload'] = true;
      $this->controllerConfig['users_logged'] = false;
      $this->controllerConfig['users_not_logged'] = false;

      # Establecer las configuraciones pasadas
      if(null != $config) {
        # Configura el autoreload del caché de twig
        if(array_key_exists('twig_cache_reload',$config)) {
          $this->controllerConfig['twig_cache_reload'] = (bool) $config['twig_cache_reload'];
        }
        # Configura el controlador para solo ser visto por usuarios logeados
        if(array_key_exists('users_logged',$config)) {
          $this->controllerConfig['users_logged'] = (bool) $config['users_logged'];
        }
        # Configura el controlador para solo ser visto por usuario no logeados
        if(array_key_exists('users_not_logged',$config)) {
          $this->controllerConfig['users_not_logged'] = (bool) $config['users_not_logged'];
        }
      }
    }
    
    /**
      * Acción que regula quién entra o no al controlador según la configuración
      *
      * @return void
    */
    private function knowVisitorPermissions() {
      global $session,$config;

      # Estado del visitante, ¿es un usuario logeado?
      $is_logged = null != $session->get('user_id');

      # Sólamente usuarios logeados
      if($this->controllerConfig['users_logged'] && !$is_logged) {
        $this->functions->redir($config['site']['url'].'login/');
      }

      # Sólamente usuarios no logeados
      if($this->controllerConfig['users_not_logged'] && $is_logged) {
        $this->functions->redir();
      }
    }

}