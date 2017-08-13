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
 * Modelo Home
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Home extends Models implements ModelsInterface {
    /**
     * Objeto de la imágen
     * @var Object
     */
    private $slider;

    /**
     * Directorio de archivos
     * @var String
     */
    const SLIDER_DIR = '../views/app/images/home/';

     /**
      * Revisa si un archivo es una imagen y se puede subir al servidor
      *
      * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $file: Archivo que se sube
      *
      * @throws ModelsException cuando la imágen es muy pesada o no es una imágen jpg,png,jpeg ó gif
    */
    final private function checkImage($file) {
        if(null != $file) {
          
            # Tamaño
            if($file->getClientSize() > (1024 * 1024) ) {
                throw new ModelsException('El tamaño de la imagen no debe superar 1Mb');
            } 
            # Extensión
            if(!in_array($file->getClientOriginalExtension(),['jpg','png','jpeg','gif'])) {
                throw new ModelsException('La imagen debe tener una extensión válida (jpg,jpeg,png,gif).');
            }
        }
    }

    /**
      * Revisa si un archivo es una imagen y se puede subir al servidor
      *
      * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $file: Archivo que se sube
      * @param string $name: Nombre correspondiente de la variable en el lenguaje
      *
      * @return string nombre del archivo subido
    */
    final private function uploadImage($file, string $dir) : string {
                
        # Crear el directorio si no existe
        if(!is_dir($dir)) {
            mkdir($dir,0777,true);
        } 
        # Si el archivo ya existe lo borramos 
        if (file_exists($dir . $file->getClientOriginalName())) {
          unlink($dir . $file->getClientOriginalName());
        }
        # Subir el archivo
        $file->move('../'.$dir, ($name = $file->getClientOriginalName()));
        return $dir . $name;
    }
    
    /**
      * Devuelve un arreglo para la api
      *
      * @return array
    */
    final public function Add_Slider() : array {
      global $http;
      try {

        # Guardamos el objeto en una variable
        $this->slider = $http->files->get('slider');

        # Validación de la imágen
        $this->checkImage($this->slider);

        # Insertamos los datos
        $this->db->insert('home_sliders', ['file' => str_replace('../', './', $this->uploadImage($this->slider, self::SLIDER_DIR) )  ]);
     

        return array('success' => 1, 'message' => 'Creado con éxito.');
      } catch (ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      }
      
    }

    final public function Edit_Slider(){
      global $http;
      try {
        $this->setId($http->request->get('id'));

        # Guardamos el objeto en una variable
        $this->slider = $http->files->get('slider');

        # Validación de la imágen
        $this->checkImage($this->slider);

        # Actualizamos los datos
        $this->db->update('home_sliders', ['file' => str_replace('../', './', $this->uploadImage($this->slider, self::SLIDER_DIR) )  ], "id_home_slider = '$this->id'", 'LIMIT 1');
     

        return array('success' => 1, 'message' => 'Editado con éxito.');
      } catch (ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      }
    }

    /**
     * Obtiene los sliders 
     * @return array matriz con los sliders
     */
    final public function get() {
      return $this->db->select('*', 'home_sliders');
    }
    /**
     * Elimina un slider del home
     * @return array con exito o error
     */
    final public function Delete_Slider() {
      global $http;

      $id = (int) $http->request->get('id');
      $file = $http->request->get('file');

      $this->db->delete('home_sliders', "id_home_slider = '$id'");
      if (file_exists('../../'. $file)) {
        unlink('../../'. $file);
      }

      return array('success' => 1);
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