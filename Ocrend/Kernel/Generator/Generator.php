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
      * @var array
    */
    private $name = array(
      'controller' => null, #nombreController
      'model' => null, #Nombre
      'view' => null #nombre
    );

    /**
      * Módulos a escribir
      *
      * @var array
    */
    private $modules = array(
      'model' => false,
      'view' => false,
      'controller' => false,
      'ajax' => false,
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
      * Se encarga de definir el contenido que tendrá un controlador de acuerdo al comando.
      *
      * @return string : {{content}} del controlador
    */
    private function createControllerContent() : string {
      $content = "// Contenido del controlador... \n";
      # Si es un controlador de crud
      if($this->modules['crud']) {
        $content = "global \$config;
        
        \${{model_var}} = new Model\\{{model}}(\$router);

        switch(\$this->method) {
            case 'crear':
                echo \$this->template->render('{{view}}/crear');
            break;
            case 'editar':
                if(\$this->isset_id and false !== (\$data = \${{model_var}}->get(false))) {
                    echo \$this->template->render('{{view}}/editar', array(
                        'data' => \$data[0]
                     ));
                } else {
                    \$this->functions->redir(\$config['site']['url'] . '{{view}}/&error=true');
                }
            break;
            case 'eliminar':
                \${{model_var}}->delete();
            break;
            default:
                echo \$this->template->render('{{view}}/{{view}}',array(
                    'data' => \${{model_var}}->get()
                ));
            break;
        }";
      } else {
        # Si existe un modelo
        if($this->modules['model']) {
          $content = "\${{model_var}} = new Model\\{{model}};\n";
        }
        # Si existe una vista
        if($this->modules['view']) {
          $content .= "echo \$this->template->render('{{view}}/{{view}}');\n";
        }
      }

      return $content;
    }

    /**
      * Se encarga de definir el contenido que tendrá un modelo de acuerdo al comando.
      *
      * @return string : {{content}} del modelo
    */
    private function createModelContent() : string {
      $content = "// Contenido del modelo... \n";
      # Si es el modelo de un crud
      if($this->modules['crud']) {
        
        # Campos de la base de datos
        $database_fields = '';
        $size = sizeof($this->tablesCollection);
        $i = 1;
        foreach($this->tablesCollection as $field => $data) {
          $database_fields .= "\t\t\t'$field' => \$http->request->get('$field')";
          if($i < $size) {
            $database_fields .= ",\n";
          } 
          $i++;
        }

        # Contenido
        $content = "\n\n\**
          * Controla los errores de entrada del formulario
          *
          * @throws ModelsException
        */
        final private function errors() {
            global \$http;
            # throw new ModelsException('¡Esto es un error!');
        }

        /** 
          * Crea un elemento de {{model}} en {{table_name}}
          *
          * @return array con información para la api, un valor success y un mensaje.
        */
        final public function add() {
            try {
                global \$htpp;
                
                # Controlar errores de entrada en el formulario
                \$this->errors();

                # Insertar elementos
                \$this->db->insert('{{table_name}}',array(
$database_fields
                ));

            } catch(ModelsException \$e) {
                return array('success' => 0, 'message' => \$e->getMessage());
            } finally {
                return array('success' => 1, 'message' => 'Creado con éxito.');
            }
        }
        
        /** 
          * Edita un elemento de {{model}} en {{table_name}}
          *
          * @return array con información para la api, un valor success y un mensaje.
        */
        final public function edit() : array {
            try {
                global \$htpp;

                # Obtener el id del elemento que se está editando y asignarlo en \$this->id
                \$this->setId(\$http->request->get('{{id_table_name}}'),'No se puede editar el elemento.'); 
                
                # Controlar errores de entrada en el formulario
                \$this->errors();

                # Actualizar elementos
                \$this->db->update('{{table_name}}',array(
$database_fields
                ),\"{{id_table_name}}='\$this->id'\",'LIMIT 1');

            } catch(ModelsException \$e) {
                return array('success' => 0, 'message' => \$e->getMessage());
            } finally {
                return array('success' => 1, 'message' => 'Editado con éxito.');
            }
        }

        /** 
          * Borra un elemento de {{model}} en {{table_name}}
          * y luego redirecciona a {{view}}/&success=true
          *
          * @return void
        */
        final public function delete() {
            global \$config;
            # Borrar el elemento de la base de datos
            \$this->db->delete('{{table_name}}',\"{{id_table_name}}='\$this->id'\");
            # Redireccionar a la página principal del controlador
            \$this->functions->redir(\$config['site']['url'] . '{{view}}/&success=true');
        }

        /**
          * Obtiene elementos de {{model}} en {{table_name}}
          *
          * @param bool \$multi: true si se quiere obtener un listado total de los elementos 
          *                     false si se quiere obtener un único elemento según su {{id_table_name}}
          * @param string \$select: Elementos de {{table_name}} a seleccionar
          *
          * @return false|array: false si no hay datos.
          *                      array con los datos.
        */
        final public function get(bool \$multi = true, string \$select = '*') {
            if(\$multi) {
                return \$this->db->select('*','{{table_name}}');
            }

            return \$this->db->select('*','{{table_name}}',\"{{id_table_name}}='\$this->id'\",'LIMIT 1');
        }\n";
      } else {
        # Si existe una escritura en la api
        if(null !== $this->modules['api']) {
          $content = "/**
            * Devuelve un arreglo para la api
            *
            * @return array
          */
          final public function foo() : array {
              global \$http;

              return array('success' => 1, 'message' => 'Funcionando');
          }\n";
        }
        # Si hay una tabla nueva creada
        if($this->modules['database']) {
          $content .= "\n\n/**
            * Obtiene elementos de {{model}} en {{table_name}}
            *
            * @param string \$select: Elementos de {{table_name}} a seleccionar
            *
            * @return false|array: false si no hay datos.
            *                     array con los datos.
          */
          final public function get(string \$select = '*') {
              return \$this->db->select(\$select,'{{table_name}}');
          }\n";
        }
      }
      
      return $content;
    }

    private function createApiContent(bool $model) : string {
      if($model) {
        return "\n\n\$app->{{method}}('/{{view}}', function() use(\$app) {
            \${{model_var}} = new Model\{{model}}; 

            return \$app->json(\${{model_var}}->{{method_model}}());   
        });";
      }

      return "\n\n\$app->{{method}}('/{{view}}', function() use(\$app) {
            return \$app->json(array('success' => 0, 'message' => 'Funcionando.'));   
        });";

    }

    private function buildFiles() {
      # Crear tabla en la base de datos
      if($this->modules['crud'] || $this->modules['database']) {
        // Registrar base de datos
      }

      # Crear controlador
      if($this->modules['crud'] || $this->modules['controller']) {
        $a = $this->createControllerContent();
        // Crear controlador
      }

      # Crear modelo
      if($this->modules['crud'] || $this->modules['model']) {
        $a = $this->createModelContent();
        // Crear modelo
      }

      # Crear vista con ajax
      if($this->modules['view'] && ($this->modules['ajax'] || $this->modules['api'])) {
        
      }

      # Ajax o api (escribir en la api) y generar javascript
      if($this->modules['ajax'] || $this->modules['api']) {
        # Escribir la api
        $a = $this->createApiContent($this->modules['model']);
        // Crear API + JS
      }

      # Crear vistas y ajax para el crud
      if($this->modules['crud']) {

      }
    }

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
        $this->writeLn('Generar un fichero javascript de ajax que apunta a la api rest via POST.');
        $this->writeLn('-ajax');
        $this->writeLn();
        $this->writeLn('Escribe en el fichero del verbo http correspondiente en la api rest.');
        $this->writeLn('-api:[verbo]');
        $this->writeLn();
        $this->writeLn('Crear una tabla en la base de datos, (No puede haber ninguna otra opcion despues de esta).');
        $this->writeLn('-db [Nombre de la tabla en la DB] campo1:tipo:longitud(opcional) campo2:tipo');
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

        # Formato del nombre
        if(!preg_match('/^[a-zA-Z]*$/',$this->arguments[1])) {
          throw new CommandException('El nombre para el modulo solo puede contener letras.');
        }

        # Nombres para las partes del módulo
        $this->name['controller'] = strtolower($this->arguments[1]) . 'Controller';
        $this->name['model'] = ucfirst(strtolower($this->arguments[1]));
        $this->name['view'] = strtolower($this->arguments[1]);
      }

      # Saber si se pasaron opciones correctas
      $lexer = false;

      # Revisar lo que debe hacerse 
      if($action[1] == 'crud') {
        $lexer = true;
        $this->modules['crud'] = true;

        # Verificar que exista la opción de base de datos
        if(!array_key_exists(2,$this->arguments) || $this->arguments[2] != '-db') {
          throw new CommandException('El crud necesita el parametro -db.');
        }

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
              $this->table_name = strtolower($this->arguments[$i + 1]);
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

          # Javascript ajax
          if($this->arguments[$i] == '-ajax') {
            $this->modules['ajax'] = true;
            $this->modules['api'] = 'post';
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
        # Comenzar a construir los archivos
        $this->buildFiles();
    }

}