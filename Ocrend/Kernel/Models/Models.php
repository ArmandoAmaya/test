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
use Ocrend\Kernel\Helpers\Functions;

/**
 * Clase para conectar todos los modelos del sistema y compartir la configuración.
 * Inicializa elementos escenciales como la conexión con la base de datos.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
 */

abstract class Models  {
    
    /**
      * Tiene la instancia de la base de datos actual
      *
      * @var null|Database
    */
    protected $db = null;

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
      * Contiene una instancia del helper para funciones
      *
      * @var Ocrend\Kernel\Helpers\Functions
    */
    protected $functions;

    /**
      * Contiene el id del usuario que tiene su sesión iniciada.
      *
      * @var int|null con id del usuario
    */
    protected $id_user = null;

    /**
      * Inicia la configuración inicial de cualquier modelo
      *
      * @param RouterInterface $router: Instancia de un Router 
      * @param array|null $databaseConfig: Configuración de conexión con base de datos con la forma
      *     'name' => string, # Nombre de la base de datos
      *     'motor' => string, # Motor de la base de datos
      *     'new_instance' => bool, # Establecer nueva instancia distinta a alguna ya existente
      *                                    
    */
    protected function __construct(RouterInterface $router = null, $databaseConfig = null) {
        global $session;

        # Llenar la configuración a la base de datos
        $this->setDatabaseConfig($databaseConfig);

        # Id captado por la ruta
        if(null != $router) {
            $this->id = $router->getId(true);
            $this->id = null == $this->id ? 0 : $this->id; 
        }

        # Instanciar las funciones
        $this->functions = new Functions();

        # Verificar sesión del usuario
        if(null != $session->get('user_id')) {
           $this->id_user = $session->get('user_id');
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

        # Parámetros por defecto
        $this->databaseConfig['name'] = $config['database']['name'];
        $this->databaseConfig['motor'] = $config['database']['motor'];
        $this->databaseConfig['new_instance'] = false;

        # Añadir según lo pasado por $databaseConfig
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
      * Asigna el id desde un modelo, ideal para cuando queremos darle un valor numérico 
      * que proviene de un formulario y puede ser inseguro.
      *
      * @param mixed $id : Id a asignar en $this->id
      * @param string $default_msg : Mensaje a mostrar en caso de que no se pueda asignar
      *
      * @throws ModelsException
    */
    protected function setId($id, string $default_msg = 'No puedede asignarse el id.') {
        if(null == $id || !is_numeric($id) || $id <= 0) {
            throw new ModelsException($default_msg);
        }

        $this->id = (int) $id;
    }

    /**
      * Trae los datos en forma [id => valor]
      *
      * @param PDOStatement $query: Consulta 
      * @param string $entidad: Nombre de la entidad 
      * @param string $campo: Campo para comprar existencia
      *
      * @return array con los datos de una entidad
    */
    protected function select_array($query, string $entidad, string $campo = '') {
        $a = array();
        foreach ($query as $q) {
          $a[$q[$entidad]] = array_key_exists($campo, $q) ? $q[$campo] : $q; 
        }

        return $a;

    }

    /**
      * Finaliza la conexión con la base de datos.
    */
    protected function __destruct() {
        $this->db = null;
    }

}