<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ocrend\Kernel\Models;

use Ocrend\Kernel\Router\RouterInterface;
use Ocrend\Kernel\Database\Database;

/**
 * Clase para conectar todos los modelos del sistema y compartir la configuración.
 * Inicializa elementos escenciales como la conexión con la base de datos.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
 */

abstract class Models  {
    
    /**
      * Obtiene la instancia de la base de datos actual
      *
      * @var int 
    */
    protected $db;

    /**
      * Tiene siempre el id pasado por la ruta, en caso de no haber ninguno, será cero.
      *
      * @var int 
    */
    protected $id = 0;

    /**
      * Contiene la información que se pasa al manejador de la base de datos. 
      * - Nombre de base de datos
      * - Motor de base de datos 
      * - Valor de nueva instancia
      *
      * @var array
    */
    private $databaseConfig = array();

    /**
      * Inicia la configuración inicial de cualquier modelo
      *
      * @param RouterInterface $router: Instancia de un Router 
      * @param array|null $databaseConfig: Configuración de conexión con base de datos con la forma
      *                                    array('name' => string, 'motor' => string, 'new_instance' => bool)
    */
    protected function __construct(RouterInterface $router = null, $databaseConfig = null) {
        # Llenar la configuración a la base de datos
        $this->setDatabaseConfig($databaseConfig);

        # Id captado por la ruta
        if(null != $router) {
            $this->id = $router->getId(true);
            $this->id = null == $this->id ? 0 : $this->id; 
        }

        # Instancia a la base de datos 
        $this->db = Database::Start(
            $this->databaseConfig['name'],
            $this->databaseConfig['motor'],
            $this->databaseConfig['new_instance']
        );
    }

    /**
      * Establece la configuración de la base de datos
      *
      * @param RouterInterface $router: Instancia de un Router 
      * @param array|null $databaseConfig: Configuración de conexión con base de datos
    */
    private function setDatabaseConfig($databaseConfig) {
        global $config;

        $this->databaseConfig['name'] = $config['database']['name'];
        $this->databaseConfig['motor'] = $config['database']['motor'];
        $this->databaseConfig['new_instance'] = false;

        if(is_array($databaseConfig)) {
            if(array_key_exists('name',$databaseConfig)) {
               $this->databaseConfig['name'] =  $databaseConfig['name'];
            } 

            if(array_key_exists('motor',$databaseConfig)) {
                $this->databaseConfig['motor'] =  $databaseConfig['motor'];
            } 

            if(array_key_exists('new_instance',$databaseConfig)) {
                $this->databaseConfig['new_instance'] = (bool) $databaseConfig['new_instance'];
            }
        }
    }

    /**
      * Finaliza la conexión con la base de datos.
    */
    protected function __destruct() {
        $this->db = null;
    }

}