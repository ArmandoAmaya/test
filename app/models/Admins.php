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
use Ocrend\Kernel\Helpers\{Strings,Files};

/**
 * Modelo Admins
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Admins extends Models implements ModelsInterface {

    /**
     * Email de usuario
     * @var Strings
     */

    private $email;

    /**
     * Contraseña
     * @var Strings
     */
    private $pass;

    /**
     * Repeticion Contraseña
     * @var Strings
     */
    private $pass_repeat;

    /**
     * Nombre usuario
     * @var Strings
     */
    private $name;

    /**
     * Objeto de archivos
     * @var Object
     */

    private $file;

    /**
     * Directorio de archivos
     * @var String
     */
    const AVATARS_DIR = '../views/app/images/avatars/{{id_user}}/';

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
            if($file->getClientSize() > $file->getMaxFilesize()) {
                throw new ModelsException('El tamaño de la imagen no debe superar' . ($file->getMaxFilesize()/1024/1024) . 'Mb');
            } 
            # Extensión
            if(!in_array($file->getClientOriginalExtension(),['jpg','png','jpeg','gif'])) {
                throw new ModelsException('La imagen debe tener una extensión válida (jpg,jpeg,png,gif).');
            }
        }
    }


    /**
     * Control de errores
     * @param Object $http: Objecto $http de symfony 
     * @return un array con el errro en caso de haberlo, false en caso contrario
     */
    final private function Errors($http, bool $edit = false){
      try {
        
        # Datos
        $this->email = $this->db->scape( $http->request->get('email') );
        $this->pass = $http->request->get('pass');
        $this->pass_repeat = $http->request->get('pass_repeat');
        $this->name = $http->request->get('name');
        $this->file = $http->files->get('perfil');

        # En caso de editar
        if ($edit) {
          $this->setId( $this->db->scape( $http->request->get('id') ) );
          $where = "AND id_user <> '$this->id'";

          # Campos llenos
          if ($this->functions->e($this->email, $this->name)) {
            throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
          }

          # Validación de contraseñas
          if (null != $this->pass && $this->pass != $this->pass_repeat) {
            throw new ModelsException('Las contraseñas no coinciden.');
          }

        }else{
          $where = '';

          # Campos llenos
          if (!$this->functions->all_full($http->request->all())) {
            throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
          }

          # Validación de contraseñas
          if ($this->pass != $this->pass_repeat) {
            throw new ModelsException('Las contraseñas no coinciden.');
          }
        }

 

        # Validar que el usuario no exista
        if ( false != $this->db->select('id_user', 'users', "email = '$this->email' $where", 'LIMIT 1') ) {
          throw new ModelsException('Este administrador ya existe.');
        }

        # Validación de formato de correo
        if (!Strings::is_email($this->email)) {
          throw new ModelsException('Debe introducir un correo válido.');
          
        }

        # Validación de imagenes
        $this->checkImage($this->file);
        
        return false;
      } catch (ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
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
        # Directorio real
        $dir = str_replace('{{id_user}}',$this->id,$dir);
        
        # Crear el directorio si no existe
        if(!is_dir($dir)) {
            mkdir($dir,0777,true);
        } 
        # Borrar todo contenido de allí 
        else {
            foreach(glob($dir . '*') as $f) {
                if(is_file($f)) {
                    unlink($f);
                }
            }
        }
        # Subir el archivo
        $file->move($dir, ($name = $file->getClientOriginalName()));
        return $name;
    }
    
    /**
      * Crea un usuario administrador
      *
      * @return array
    */
    final public function Crear() : array {
      global $http;

      # Capturamos el error
      $error = $this->Errors($http);
      if (!is_bool($error)) {
        return $error;
      }
      
      # Insertamos datos
      $this->db->insert('users', array(
        'name'=> $this->name, 
        'email'=> $this->email,
        'pass'=> Strings::hash($this->pass),
        'fecha_reg' => time()
      ));

      # Si existe una imagen la guardamos
      if (null != $this->file) {
        # Obtenemos el id de este ultimo usuario insertado
        $this->setId( $this->db->lastInsertId() );

        # Capturamos el nombre de la imagen
        $avatar = $this->uploadImage($this->file, self::AVATARS_DIR);

        # Actualiamos los datos con el nombre de la imagen a este usuario
        $this->db->update('users', array('avatar' => $avatar), "id_user = '$this->id'", 'LIMIT 1');
      }

      
      return array('success' => 1, 'message' => 'Administrador creado exitosamente.');
    }

    final public function Editar() : array {
      global $http;

      # Capturamos el error
      $error = $this->Errors($http,true);
      if (!is_bool($error)) {
        return $error;
      }

      # Guardamos los datos en un array
      $a = array(
        'name'=>$http->request->get('name'), 
        'email'=>$this->email,
      );
      
      # Si existe contraseña la guardamos en el array
      if (!$this->functions->emp($this->pass)) {
        $a['pass'] = Strings::hash($http->request->get('pass'));
      }

      # Si existe una imagen la guardamos
      if (null != $this->file) {

        # Guardamos la imagen en la posición avatar del array
        $a['avatar'] = $this->uploadImage($this->file, self::AVATARS_DIR);
        
      }

      # Actualiamos los datos del usuario
      $this->db->update('users', $a, "id_user = '$this->id'", 'LIMIT 1');
      
      return array('success' => 1, 'message' => 'Administrador editado exitosamente.');

    }

    /**
      * Trae los datos de un usuario
    */

    final public function leer(bool $multi = true) {
      if ($multi) {
        return $this->db->select('*', 'users');
      }

      return $this->db->select('*', 'users', "id_user='$this->id'", 'LIMIT 1');
    }

     /**
      * Obtiene el avatar del usuario solicitado
      *
      * @param string $image_name: Nombre de la imagen
      * @param int $id_user: Id del usuario solicitado
      *
      * @return string con la dirección de la imagen
    */
    final public function getUserImage(string $image_name, int $id_user) : string{
        # Directorio real
        $dir = str_replace('{{id_user}}',$id_user,self::AVATARS_DIR);

        # Verificar si existe 
        if(!$this->functions->emp($image_name)) {

            return str_replace('../','',$dir . $image_name);
        }

        # Si no existe, retornar por defecto
        return '';
    }

    /**
     * Elimina un registro de usuario
     * @return void
     */
    final public function borrar() {
      global $config;
      # Verificar que no se borre a sí mismo.
      if ($this->id != $this->id_user) {
        
        # Se borra al usuario
        $this->db->delete('users',"id_user='$this->id'", 'LIMIT 1');

        # Directorio real
        $dir = str_replace('{{id_user}}',$this->id,
              str_replace('../', '', self::AVATARS_DIR));

        # Si tiene archivos, los borramos
        if (is_dir($dir)) {
          Files::rm_dir($dir);
        }
      }

      # Volvemos a la vista
      $this->functions->redir($config['site']['url'].'admins/?success=true');

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