<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models;

use app\models as Model;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsInterface;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Router\RouterInterface;

/**
 * Modelo Categorias
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Categorias extends Models implements ModelsInterface {

    /**
     * Nombre en español
     * @var string
     */
    private $name_es;

    /**
     * Nombre en ingles
     * @var string
     */
    private $name_en;
    
    /**
      * Controla los errores de entrada del formulario
      *
      * @throws ModelsException
    */
    final private function errors(bool $edit = false) {
      global $http;

      # Guardamos los valores en variables
      $this->name_es = $this->db->scape( $http->request->get('name_es') );
      $this->name_en = $this->db->scape( $http->request->get('name_en') );
      
      # Caso de editar
      $where = $edit ? "AND id_categorias <> '$this->id'" : '';


      # Existencia de la categoría
      if (false != $this->db->select('id_categorias', 'categorias', "(name_es = '$this->name_es' OR name_en = '$this->name_en') $where", 'LIMIT 1')) {
        throw new ModelsException('Esta categoría ya está registrada.');
      }

      # Campos llenos
      if ( !$this->functions->all_full( $http->request->all() ) ) {
        throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
      }


    }

    /** 
      * Crea un elemento de Categorias en la tabla `categorias`
      *
      * @return array con información para la api, un valor success y un mensaje.
    */
    final public function add() {
      try {
        global $http;

        # Controlar errores de entrada en el formulario
        $this->errors();

        # Insertar elementos
        $this->db->insert('categorias',array(
					'name_es' => $this->name_es,
					'name_en' => $this->name_en
        ));

        return array('success' => 1, 'message' => 'Categoría creada exitosamente.');
      } catch(ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      } 
    }
          
    /** 
      * Edita un elemento de Categorias en la tabla `categorias`
      *
      * @return array con información para la api, un valor success y un mensaje.
    */
    final public function edit() : array {
      try {
        global $http;

        # Obtener el id del elemento que se está editando y asignarlo en $this->id
        $this->setId($http->request->get('id_categorias'),'No se puede editar el elemento.'); 
                  
        # Controlar errores de entrada en el formulario
        $this->errors(true);

        # Actualizar elementos
        $this->db->update('categorias',array(
					'name_es' => $this->name_es,
          'name_en' => $this->name_en
        ),"id_categorias='$this->id'",'LIMIT 1');

        return array('success' => 1, 'message' => 'Categoría editada exitosamente.');
      } catch(ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      } 
    }

    /** 
      * Borra un elemento de Categorias en la tabla `categorias`
      * y luego redirecciona a categorias/&success=true
      *
      * @return void
    */
    final public function delete() {
      global $config;
      # Borrar el elemento de la base de datos
      $this->db->delete('categorias',"id_categorias='$this->id'");
      # Redireccionar a la página principal del controlador
      $this->functions->redir($config['site']['url'] . 'categorias/&success=true');
    }

    /**
      * Obtiene elementos de Categorias en la tabla `categorias`
      *
      * @param bool $multi: true si se quiere obtener un listado total de los elementos 
      *                     false si se quiere obtener un único elemento según su id_categorias
      * @param string $select: Elementos de categorias a seleccionar
      *
      * @return false|array: false si no hay datos.
      *                      array con los datos.
    */
    final public function get(bool $multi = true, string $select = '*') {
      if($multi) {
        return $this->db->select($select,'categorias');
      }

      return $this->db->select($select,'categorias',"id_categorias='$this->id'",'LIMIT 1');
    }


    /**
      * __construct()
    */
    public function __construct(RouterInterface $router = null) {
        parent::__construct($router);
    }

    /**
      * __destruct()
    */ 
    public function __destruct() {
        parent::__destruct();
    }
}