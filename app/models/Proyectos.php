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
use Ocrend\Kernel\Helpers\Files;

/**
 * Modelo Proyectos
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Proyectos extends Models implements ModelsInterface {

    /**
     * Título del proyecto
     * @var string
     */
    private $titulo;

    /**
     * Categoria(s)
     * @var array
     */
    private $categorias;

    /**
     * Descripción corta - es
     * @var string
     */
    private $short_desc_es;

    /**
     * Descripción corta - en
     * @var string
     */
    private $short_desc_en;

    /**
     * Contenido - es
     * @var string
     */
    private $content_es;

    /**
     * Contenido - en
     * @var string
     */
    private $content_en;

    /**
     * Archivos temporales
     * @var array
     */
    private $tmp_files = array();

    /**
     * Directorio de los archivos
     * @var array
     */

    const DIRS = array(
      '../views/app/images/projects/{{id_project}}/portada/',
      '../views/app/images/projects/{{id_project}}/logo/',
      '../views/app/images/.tmp/{{tmp_dir}}/',
      '../views/app/images/projects/{{id_project}}/'
    );
    /**
     * Imagen portada
     * @var Object
     */
    private $portada;

    /**
     * Imagen logo
     * @var Object
     */
    private $logo;




    /**
      * Revisa si un archivo es una imagen y se puede subir al servidor
      *
      * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $file: Archivo que se sube
      * @param string $name: Nombre correspondiente de la variable en el lenguaje
      *
      * @throws ModelsException cuando la imágen es muy pesada o no es una imágen jpg,png,jpeg ó gif
    */
    private function checkImage($file, string $name) {
        if(null != $file) {
            # Tamaño
            if($file->getClientSize() > $file->getMaxFilesize()) {
                throw new ModelsException('La imagen de "' . $name .'" debe tener un tamaño máximo de' . ($file->getMaxFilesize()/1024/1024) . 'Mb');
            } 
            # Extensión
            if(!in_array($file->getClientOriginalExtension(),['jpg','png','jpeg','gif'])) {
                throw new ModelsException('La imagen de "'.$name.'" debe ser de formato jpg, png ó gif');
            }
        }
    }

    
    /**
      * Controla los errores de entrada del formulario
      *
      * @throws ModelsException
    */
    final private function errors(bool $edit = false) {
      global $http;

      # Guardamos datos en propiedades
      $this->titulo = $this->db->scape( $http->request->get('titulo') );
      $this->categorias = explode(',', $http->request->get('id_categoria'));
      $this->short_desc_es = $http->request->get('short_desc_es');
      $this->short_desc_en = $http->request->get('short_desc_en');
      $this->content_es = $http->request->get('content_es');
      $this->content_en = $http->request->get('content_en');
      $this->portada = $http->files->get('portada');
      $this->logo = $http->files->get('logo');


      if ($edit) {
        $where = "AND id_proyectos <> '$this->id'";
      }else{
        $where = '';

        # Validación de las imágenes
        if (null == $this->portada || null == $this->logo) {
          throw new ModelsException('Los archivos de imágenes son necesarios.');
        }
        $dir = str_replace('{{tmp_dir}}', ( $this->id_user . '/' . $http->request->get('tmp_dir') ) .'/' , self::DIRS[2]);
        $this->tmp_files = sizeof( glob( '../' . $dir . '*' ) );
        # Validación de los archivos temporales
        if ( $this->tmp_files == 0) {
          throw new ModelsException('Debe haber al menos 1 imagen para la galería.');
        }
      }

      # Validación de existencia
      if (false != $this->db->select('id_proyectos','proyectos', "titulo = '$this->titulo' $where",'LIMIT 1')) {
        throw new ModelsException('Este proyeto ya está registrado.');
      }

      # Todos los campos son necesarios
      if ($this->functions->e($this->titulo, $this->short_desc_en, $this->short_desc_es, $this->content_es, $this->content_en)) {
        throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
      }
      # Validación de la categoría
      if (in_array('null', $this->categorias)) {
        throw new ModelsException('Debes escoger al menos 1 categoría.');
      }

      # Validación para los textarea
      if ($this->functions->e( strip_tags($this->content_es), strip_tags($this->content_en) )) {
        throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
      }

      # Recorrido para categorias
      if (sizeof($this->categorias) > 0) {
        foreach ($this->categorias as $c) {
          
          # Categoría válida
          if (!array_key_exists($c, $this->getCategories())) {
            throw new ModelsException('Debes escoger una categoría válida.');
          }

          # Debe ser numérica ya que es un id
          if (!is_numeric($c) and $c < 1) {
            throw new ModelsException('Debes escoger una categoría válida.');
          }

        }
      }else{
        throw new ModelsException('Debes escoger al menos 1 categoría.');
      }
      
      # Validación de imgen de portada
      $this->checkImage($this->portada, 'portada');
      # Validación del logo
      $this->checkImage($this->logo, 'logo');

    }

    /** 
      * Crea un elemento de Proyectos en la tabla `proyectos`
      *
      * @return array con información para la api, un valor success y un mensaje.
    */
    final public function add() : array{
      try {
        global $http;
                  
        # Controlar errores de entrada en el formulario
        $this->errors();

        # Insertar elementos
        $this->db->insert('proyectos',array(
					'titulo' => $this->titulo,
          'short_desc_es' => $this->short_desc_es,
          'short_desc_en' => $this->short_desc_en,
					'content_es' => $this->content_es,
					'content_en' => $this->content_en,
        ));

        # Obtenemos el último id
        $this->id = $this->db->lastInsertId();

        # Movemos los archivos de la carpeta temporal a la real
        $this->MoveToDir($http->request->get('tmp_dir'));

        # Guardamos las imagenes y los directorios
        $this->portada = str_replace('../', './', $this->resize_image($this->portada, ( '../' . str_replace('{{id_project}}', $this->id, self::DIRS[0]) ), 'portada' ));
        $this->logo = str_replace('../', './', $this->resize_image($this->logo, ( '../' . str_replace('{{id_project}}', $this->id, self::DIRS[1]) ), 'logo', 70, 70 ));

        # Actualizamos el registro en la db
        $this->db->update('proyectos', array(
          'portada' => $this->portada,
          'logo' => $this->logo
        ), "id_proyectos = '$this->id'", 'LIMIT 1');

        # Insertamos las categorías
        if (sizeof($this->categorias) > 1) {
          foreach ($this->categorias as $c) {
            $this->db->insert('categoria_proyecto', ['id_categoria' => $c, 'id_proyecto' => $this->id ]);
          }
        }else{
          $this->db->insert('categoria_proyecto', ['id_categoria' => $this->categorias[0], 'id_proyecto' => $this->id ]);
        }

        return array('success' => 1, 'message' => 'Proyecto creado exitosamente.');
      } catch(ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      } 
    }
          
    /** 
      * Edita un elemento de Proyectos en la tabla `proyectos`
      *
      * @return array con información para la api, un valor success y un mensaje.
    */
    final public function edit() : array {
      try {
        global $http;

        # Obtener el id del elemento que se está editando y asignarlo en $this->id
        $this->setId($http->request->get('id_proyectos'),'No se puede editar el elemento.'); 
                  
        # Controlar errores de entrada en el formulario
        $this->errors(true);

        # Elementos a actuaizar
        $a = array(
          'titulo' => $this->titulo,
          'short_desc_es' => $this->short_desc_es,
          'short_desc_en' => $this->short_desc_en,
          'content_es' => $this->content_es,
          'content_en' => $this->content_en,
        );

        # Si existen nuevos archivos temporales
        if ( $this->tmp_files > 0) {
          # Movemos los archivos de la carpeta temporal a la real
          $this->MoveToDir($http->request->get('tmp_dir'));
        }

        # Si existe imagen de portada
        if (null != $this->portada) {
          # Guardamos la imagen y el directorio
          $this->portada = str_replace('../../', './', $this->resize_image($this->portada, ( '../' . str_replace('{{id_project}}', $this->id, self::DIRS[0]) ), 'portada' ));
          $a['portada'] = $this->portada;
        }

  

        # Si existe logo
        if (null != $this->logo) {
         
          # Guardamos la imagen y el directorio
          $this->logo = str_replace('../../', './', $this->resize_image($this->logo, ( '../' . str_replace('{{id_project}}', $this->id, self::DIRS[1]) ), 'logo', 70, 70 ));
          $a['logo'] = $this->logo;
        }


        $this->db->update('proyectos',$a,"id_proyectos='$this->id'",'LIMIT 1');

        
        return array('success' => 1, 'message' => 'Proyecto editado exitosamente.');
      } catch(ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      } 
    }


    /** 
      * Borra un elemento de Proyectos en la tabla `proyectos`
      * y luego redirecciona a proyectos/&success=true
      *
      * @return void
    */
    final public function delete() {
      global $config;
      # Borrar el elemento de la base de datos
      $this->db->delete('proyectos',"id_proyectos='$this->id'");
      # Borramos los archivos de la carpeta temporal
      Files::rm_dir( str_replace('{{id_project}}', $this->id, self::DIRS[3]));
      # Redireccionar a la página principal del controlador
      $this->functions->redir($config['site']['url'] . 'proyectos/&success=true');
    }

    /**
      * Obtiene elementos de Proyectos en la tabla `proyectos`
      *
      * @param bool $multi: true si se quiere obtener un listado total de los elementos 
      *                     false si se quiere obtener un único elemento según su id_proyectos
      * @param string $select: Elementos de proyectos a seleccionar
      *
      * @return false|array: false si no hay datos.
      *                      array con los datos.
    */
    final public function get(bool $multi = true) {
      # En caso de traer todos los proyectos
      if ($multi) {
        $where = '1=1';
        $limit = '';
      }else{
      # En caso de traer un solo proyecto
        $where = "id_proyectos = '$this->id'";
        $limit = 'LIMIT 1';
      }
      # Traemos los proyectos
      $proj = $this->db->select('*','proyectos', $where, $limit);
      # Si no hay resultamos retornamos false
      if (false == $proj) {
        return false;
      }
      # Preparamos la consulta
      $prepare = $this->db->prepare("SELECT c.id_categorias,c.name_es FROM categoria_proyecto cp INNER JOIN categorias c ON cp.id_categoria = c.id_categorias WHERE cp.id_proyecto = ?");

      # Recorremos los proyectos
      foreach ($proj as $p) {
        # Ejecutamos la consulta preparada
        $prepare->execute(array($p['id_proyectos']));
        # Convertimos los datos en array
        $result = $prepare->fetchAll();

        # Creamos el nuevo array con los datos
        $real_proj[] = array(
          'id_proyectos' => $p['id_proyectos'],
          'titulo' => $p['titulo'],
          'short_desc_es' => $p['short_desc_es'],
          'short_desc_en' => $p['short_desc_en'],
          'content_es' => $p['content_es'],
          'content_en' => $p['content_en'],
          'portada' => $p['portada'],
          'logo' => $p['logo'],
          'categorias' => $result
        );
      }
      return $real_proj;

    }

    /**
     * Convierte las categorías array asoativo con el formato ['id' => 'categoria']
     * @param array $categories - array con las categorias
     * @return categorias convertidas
     */

    final public function categories_convert_array(array $categories) : array {
      return $this->select_array($categories, 'id_categorias', 'name_es');
    }

    /**
     * Obtiene las imagenes de la galería de un proyecto
     * @return array
     */
    final public function getGallery() : array {
      $dir = str_replace('{{id_project}}',$this->id,self::DIRS[3]);
      return glob($dir . '{*.jpeg,*.jpg,*.png,*.gif}', GLOB_BRACE);
    }

    /**
     * Obtiene las categorias para asociar a un proyecto
     */

    final public function getCategories() {
      return $this->select_array( $this->db->query("SELECT id_categorias,name_es FROM categorias"), 'id_categorias', 'name_es');
    }


    /**
     * Crea un directorio temporal con el id del usuario
     * @return nombre del directorio
     */
    final public function createTmpDir() : string {
      $tmp = uniqid();
      $dir = str_replace('{{tmp_dir}}',$this->id_user .'/',self::DIRS[2]);

      if (!is_dir($dir . $tmp)) {
        mkdir($dir.$tmp, 0777,true);
      }else{
        $tmp = $tmp . md5( time() );
        mkdir($dir.$tmp, 0777,true);
      }

      return $tmp;
    }
    
    /**
     * Mueve los archivos de la carpeta temporal a la real
     * @param string $tmp - directorio temporal
     * @return void
     */

    final private function MoveToDir(string $tmp) {
      
      # Obtenemos los archvios de la carpeta temporal
      $tmp_dir = '../' . str_replace('{{tmp_dir}}', ( $this->id_user . '/' . $tmp ) , self::DIRS[2]);
      # Indicamos la nueva carpeta a la que se van a mover
      $new_dir = '../' . str_replace('{{id_project}}', $this->id , self::DIRS[3]);

      # Si el directorio no existe, lo creamos
      if (!is_dir($new_dir)) {
        mkdir($new_dir, 0777, true);
      }


      # Recorremos cada uno de los archivos
      foreach (glob($tmp_dir . '*') as $file) {
        $name = explode('/', $file);
        $name = end($name);
        
        # Si el archivo ya existe lo borramos
        if (file_exists($new_dir . $name)) {
          unlink($new_dir.$name);
        }

        # Movemos los archivos del carpeta temporal a la real
        copy($file, $new_dir . $name);
        # Borramos los archivos de la carpeta temporal
        unlink($file);

      }

    }



    /**
     * Limpia los directorios temporadales cada media hora si no se ha utilizado
     * @return void
     */
    final public function clearTmpDir() {
      $dir = str_replace('{{tmp_dir}}', $this->id_user .'/',self::DIRS[2]);
      $dr = glob($dir . '*');
      if (sizeof($dr) > 0) {
        foreach($dr as $d){
          Files::rm_dir($d);
        }
      }     
    }

     /**
     * Sube una imagen a un directorio temporal
     * @return void
     */

    final public function UploadTmp() : array{
      try {
        global $http;
        $dir = '../' . str_replace('{{tmp_dir}}', ( $this->id_user . '/' . $http->request->get('tmp_dir') ) , self::DIRS[2]);
        $files = $http->files->get('file');

        $this->checkImage($files, 'galeria');

        $files->move($dir, $files->getClientOriginalName());

        return array('success' => 1);
      } catch (ModelsException $e) {
        return array('success' => 0);
      }
      
    }
    
    /**
     * Elimina archivos de un directorio temporal
     * @return void
     */
    final public function DeleteDir() {
      global $http;
      $file = '../' . str_replace('{{tmp_dir}}', ( $this->id_user . '/' . $http->request->get('tmp_dir') ) , self::DIRS[2]) . $http->request->get('name');
      if (file_exists($file)) {
        unlink($file);
      }
    }

    /**
     * Realiza una copia de una imagen con nuevas dimensiones
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $file: Archivo que se sube
     * @param string $new_dir: directorio hacia donde se copiará
     * @param string $name: nombre de la imagen
     * @param int|int $new_width: nuevo ancho, por defecto 380
     * @param int|int $new_height: nueva altura, por defecto 250
     * @return string con el nombre de la imagen y su directorio
     */
    final private function resize_image($file, string $new_dir, string $name, int $new_width = 380, int $new_height = 250) {
      # Extension del archivo 
      $ext = $file->getClientOriginalExtension();

      # Obtenemos la imagen original
      if (in_array($ext, ['jpg','jpeg', 'JPG','JPEG'])) {
        $original = imagecreatefromjpeg($file->getPathName());
      }else if (in_array($ext, ['png','PNG'])){
        $original = imagecreatefrompng($file->getPathName());
      }else if (in_array($ext, ['gif', 'GIF'])){
        $original = imagecreatefromgif($file->getPathName());
      }else{
        throw new ModelsException('Las imagenes deben tener un formato válido (jpeg,jpg,png,gif)');
      }

      # Obtenemos las dimensiones originales
      $width = imagesx($original);
      $height = imagesy($original);
      
      # Definimos las nuevas dimensiones sin deformar
      if ($width > $height) {
        # Caso de imagenes horizontales
        $new_width = $new_width;
        $new_height = round( ($new_width * $height) / $width );
      }else if ($width < $height){
        # caso de imágenes verticales
        $new_height = $new_height;
        $new_width = round( $new_height * $width ) / $height;
      }else if ($width == 380 && $height == 250) {
        # Caso de imagen exacta
        $new_width = $width;
        $new_height = $height;
      }else{
        # caso de imágenes cuadradas
        $new_width = $new_width;
        $new_height = $new_height;
      }
      # Si el directorio no existe lo creamos
      if (!is_dir($new_dir)) {
        mkdir($new_dir, 0777,true);
      }
      

      # Creamos la nueva imagen en blanco
      $img = imagecreatetruecolor($new_width, $new_height);

      # Para mantener la transparencia en caso de png
      imagealphablending($img, false);
      imagesavealpha($img, true);
      $trans_layer_overlay = imagecolorallocatealpha($img, 220, 220, 220, 127);
      imagefill($img, 0, 0, $trans_layer_overlay);
      # *********************************************

      # Creamos la copia de la imagen
      imagecopyresampled($img, $original, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

      # Creamos la imagen segun su tipo
      if (in_array($ext, ['jpg','jpeg', 'JPG','JPEG'])) {
        imagejpeg($img,$new_dir  . $name.'.'. $ext,95);
      }else if (in_array($ext, ['png','PNG'])){
        imagepng($img,$new_dir . $name .'.'. $ext);
      }else{
        imagegif($img,$new_dir . $name .'.'. $ext);
      }

      # Retornamos el directorio para ser guardado en la db
      return $new_dir . $name .'.'. $ext;
    }


    /**
     * Elimina un archivo de la carpeta real
     * @return array 
     */
    final public function DeleteFile(){
      try {
        global $http;

        # Comprobamos que el archivo exista
        if (!file_exists('../'.$http->request->get('file'))) {
          throw new ModelsException(true);
        }

        # Eliminamos el archivo
        unlink( '../'.$http->request->get('file') );

        return array('success' => 1);

      } catch (ModelsException $e) {
        return array('success' => 0);
      }
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


