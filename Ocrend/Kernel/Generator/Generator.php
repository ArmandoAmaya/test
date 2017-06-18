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

    /**
      * Nombre del módulo a escribir.
      *
      * @var string
    */
    private $name;

    /**
      * Módulos a escribir
      *
      * @var array
    */
    private $modules = array(
      'model' => false,
      'view' => false,
      'controller' => false,
      'js' => false,
      'database' => false,
      'crud' => false,
      'api' => null # contiene el verbo http
    ); 

    /**
      * Nombre de la tabla a crear en la base de datos.
      *
      * @var string
    */
    private $table_name;

    /**
      * Colección de tablas para la base de datos.
      * Con la forma:
      * {'nombre_tabla' => array('tipo' => string , 'longitud' => null|int )}
      *
      * @var array
    */
    private $tablesCollection = array();


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
        $this->writeLn('app:m [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear un controlador vacio:');
        $this->writeLn('app:c [Nombre]');
        $this->writeLn();
        $this->writeLn('Crear una vista vacia:');
        $this->writeLn('app:v [Nombre]');
        $this->writeLn();
        $this->writeLn();
        $this->writeLn('Opciones extras, se aniaden al final de una instruccion.');
        $this->writeLn('-------------------------------------');
        $this->writeLn();
        $this->writeLn('Generar un fichero javascript de ajax.');
        $this->writeLn('-js');
        $this->writeLn();
        $this->writeLn('Escribe en el fichero del verbo http correspondiente en la api rest.');
        $this->writeLn('-api:[verbo]');
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
      if(sizeof($action) != 2 || $action[0] != 'app') {
        throw new CommandException('El comando inicial debe tener la forma app:accion.');
      }
  
      # Verificar que exista un nombre 
      if(!array_key_exists(1,$this->arguments)) {
        throw new CommandException('Se debe asignar un nombre.');
      } else {
        $this->name = $this->arguments[1];
      }

      # Saber si se pasaron opciones correctas
      $lexer = false;

      # Revisar lo que debe hacerse 
      if($action[1] == 'crud') {
        $this->modules['crud'] = true;
      } else {
        # Modelo
        if(strpos($action[1], 'm') !== false) {
          $lexer = true;
          $this->modules['model'] = true;
        }
        # Controlador
        if(strpos($action[1], 'c') !== false) {
          $lexer = true;
          $this->modules['controller'] = true;
        }
        # Vista
        if(strpos($action[1], 'v') !== false) {
          $lexer = true;
          $this->modules['view'] = true;
        }
      }

      # Error
      if(!$lexer) {
        throw new CommandException('Problema en la sintaxis, para informacion usar: php gen.php -ayuda');
      }

      # Existencia de opciones
      if(array_key_exists(2,$this->arguments)) {
        $size = sizeof($this->arguments);
        for($i = 2; $i < $size; $i++) {

          # Base de datos
          if($this->arguments[$i] == '-db') {  
            # Revisar que exista el nombre
            if(!array_key_exists($i + 1, $this->arguments)) {
              throw new CommandException('Se necesita un nombre para la tabla en la base de datos.');
            }
            # Revisar la sintaxis del nombre 
            else {
              if(!preg_match('/^[a-zA-Z0-9_]*$/',$this->arguments[$i + 1])) {
                throw new CommandException('El formato del nombre debe ser alfanumerico y el unico caracter extra permitido es el " _ "');
              }
              #  Establecer el nombre
              $this->table_name = $this->arguments[$i + 1];
            }

            # Revisar que existe al menos un campo
            if(!array_key_exists($i + 2, $this->arguments)) {
              throw new CommandException('Se necesita al menos un campo para la tabla.');
            }

            # Recorrer los campos y revisar la sintaxis uno a uno
            for($x = $i + 2; $x < $size; $x++) {
              $campo = explode(':',$this->arguments[$x]);
              # Requisito mínimo, nombre y tipo
              if(sizeof($campo) >= 2) {
                # Formato del nombre
                if(!preg_match('/^[a-zA-Z0-9_]*$/',$campo[0])) {
                  throw new CommandException('El formato del nombre del campo '. $campo[0] .' debe ser alfanumerico y el unico caracter extra permitido es el " _ "');
                }
                # Tipo de dato
                if(!in_array(strtolower($campo[1]),['tinyint','bit','bool','smallint','mediumint','int','bigint','integer','float','xreal','double','decimal','date','datetime','timestamp','char','varchar','tinytext','text','mediumtext','longtext','enum'])) {
                  throw new CommandException('El tipo de dato ' . $campo[1] . ' no existe.');
                }
                # Almacenar en la colección
                $this->tablesCollection[$campo[0]] = array(
                  'tipo' => strtoupper($campo[1]), 
                  'longitud' => null
                );
              } else {
                throw new CommandException('El formato del campo ' . $this->arguments[$x] . ' debe ser nombre:tipo.');
              }

              # Existe longitud
              if(sizeof($campo) == 3) {
                # Revisar valor de longitud
                if($campo[2] < 0) {
                  throw new CommandException('La longitud del campo ' . $campo[0] . ' debe ser positiva.');
                }
                # Poner longitud
                $this->tablesCollection[$campo[0]]['longitud'] = $campo[2];
              }
            }

            $this->modules['database'] = true;
            break;
          }

          # Javascript
          if($this->arguments[$i] == '-js') {
            $this->modules['js'] = true;
          }

          # Api rest 
          if(strpos($this->arguments[$i], '-api:') !== false) {
            if(in_array($this->arguments[$i],['-api:get','-api:post','-api:put','-api:delete'])) {
              $this->modules['api'] = explode(':',$this->arguments[$i])[1];
            } else {
              throw new CommandException('El verbo HTTP de la api rest no existe.');
            }
          }
          
        }
      }
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