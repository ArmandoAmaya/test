<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ocrend\Kernel\Generator;

use Ocrend\Kernel\Generator\CommandException;
use Ocrend\Kernel\Helpers\Files;

/**
 * Generador de scripts en PHP
 *
 * @author Brayan Narváez <prinick@ocrend.com>
*/

final class Generator {

    /**
      * Contiene los argumentos pasados por la consola
      *
      * @var array 
    */
    private $arguments;

    /**
      * Ruta de modelos en la aplicación
      *
      * @var string
    */
    const R_MODELS = './app/models/';

    /**
      * Ruta de controladores en la aplicación
      *
      * @var string
    */
    const R_CONTROLLERS = './app/controllers/';

    /**
      * Ruta de templates en la aplicación
      *
      * @var string
    */
    const R_TEMPLATES = './app/templates/';

    /**
      * Ruta de assets en la aplicación
      *
      * @var string
    */
    const R_VIEWS = './views/app/';

    /**
      * Ruta de la api rest en la aplicación
      *
      * @var string
    */
    const R_API = './api/http/';

    /**
      * Verbos HTTP compatibles
      *
      * @var string
    */
    const API_HTTP_VERBS = ['get','post','put','delete'];

    /**
      * Ruta de plantillas para leer
      *
      * @var string
    */
    const TEMPLATE_DIR = './Generator/Templates/';

    private $name;
    private $pattern = array(
      'model' => false,
      'view' => false,
      'controller' => false,
      'api' => false,
      'js' => false,
      'database' => false,
      'crud' => false
    ); 


    /**
      * Escribe un mensaje en consola y salta de línea 
      *
      * @param null|string $msg: Mensaje 
      *
      * @return void
    */
    private function writeLn($msg = null) {
        if(null != $msg) {
            echo "$msg";
        } 
        
        echo "\n";
    }

    private function help() {
        $this->writeLn();
        $this->writeLn('Comandos disponibles ');
        $this->writeLn('-------------------------------------');
        $this->writeLn();
        $this->writeLn('Escribir en un fichero de verbo http de la api rest: ');
        $this->writeLn('api:[metodo] [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un crud conectado a la base de datos:');
        $this->writeLn('app:crud [Nombre] [Nombre de la tabla en la DB] campo1:tipo:longitud(opcional) campo2:tipo ...');
        $this->writeLn();
        $this->writeLn('Crear un modelo, una vista y un controlador:');
        $this->writeLn('app:mvc [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear una vista y un controlador:');
        $this->writeLn('app:vc [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un modelo y un controlador:');
        $this->writeLn('app:mc [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un modelo y una vista:');
        $this->writeLn('app:mv [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un modelo vacio:');
        $this->writeLn('app:model [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un controlador vacio:');
        $this->writeLn('app:controller [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear una vista vacia:');
        $this->writeLn('app:view [Nombre]');
        $this->writeLn();
        $this->writeLn();
        $this->writeLn('Opciones extras, se aniaden al final de una instruccion.');
        $this->writeLn('-------------------------------------');
        $this->writeLn();
        $this->writeLn('Generar un fichero javascript que se conecta con la api rest por ajax usando el verbo POST.');
        $this->writeLn('-js');
        $this->writeLn();
        $this->writeLn('Escribir en un fichero de verbo http de la api rest: ');
        $this->writeLn('-api:[metodo]');
        $this->writeLn();
        $this->writeLn('Crear una tabla en la base de datos, (No puede haber ninguna otra opcion despues de esta).');
        $this->writeLn('-db [Nombre de la tabla en la DB] campo1:tipo:longitud(opcional) campo2:tipo');
    }

    private function checkRestMethod(string $method) {
      if(!in_array($method,self::API_HTTP_VERBS)) {
        throw new CommandException('El verbo http para la api rest no existe.');
      }
    }

    private function lexer() {
      # Cargar la ayuda
      if($this->arguments[0] == '-ayuda' || 
         $this->arguments[0] == '-ashuda' || 
         $this->arguments[0] == '-help') {
        
        $this->help();

        return;
      }

      # Verificar comando
      $action = explode(':',$this->arguments[0]);
      if(sizeof($action) != 2) {
        throw new CommandException('El comando inicial debe tener la forma elemento:accion.');
      }

      # Revisar acción 
      if($action[0] == 'api') {
        $this->checkRestMethod($action[1]);
        // API TRUE
      }
      else if($action[0] == 'app') {
        if($action[1] === 'crud') {
          // CRUD TRUE
        } else {
          # Modelo
          if(strpos($this->args[1], 'm') !== false) {
            // MODELO TRUE
          }
          # Vista
          if(strpos($this->args[1], 'v') !== false) {
            // VIEW TRUE
          }
          # Controlador
          if(strpos($this->args[1], 'c') !== false) {
            // CONTROLLER TRUE
          }
        }
      } else {
        throw new CommandException('El comando no es valido, para mas informacion utilizar "-ayuda".');
      }

      /** 

        ANALIZAR LAS OPTIONS

        */

      # Verificar que exista un nombre 
      if(!array_key_exists(1,$this->argument)) {
        throw new CommandException('Se debe asignar un nombre.');
      }

     /**


      api:[metodo]
      app:crud [Nombre] [Tabla] campo:tipo campo:tipo campo:tipo
      app:mvc [Nombre] -js -api:post
      app:vc [Nombre]
      app:mc [Nombre]
      app:mv [Nombre]
      app:m [Nombre]
      app:c [Nombre]
      app:v [Nombre]
    

     */

    }

    /**
      * Constructor, arranca el generador
      *
      * @throws CommandException si la cantida de argumentos es insuficiente
      * @return void
    */
    public function __construct(array $args) {
        # Cantidad mínima de argumentos 
        if(sizeof($args) < 2) {
            throw new CommandException('El generador debe tener la forma php gen.php [comandos]');
        }
        # Picar argumentos
        $this->arguments = array_slice($args,1);
        # Empezar a leer los argumentos
        $this->lexer();
    }

}