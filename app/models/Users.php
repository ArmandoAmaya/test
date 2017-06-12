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
use Ocrend\Kernel\Helpers\Strings;

/**
 * Controla todos los aspectos de un usuario dentro del sistema.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
 */

class Users extends Models implements ModelsInterface {

    /**
      * Máximos intentos de inincio de sesión de un usuario
      *
      * @var int
    */
    const MAX_ATTEMPTS = 5;

    /**
      * Tiempo entre máximos intentos en segundos
      *
      * @var int
    */
    const MAX_ATTEMPTS_TIME = 120; # (dos minutos)

    /**
      * Log de intentos recientes con la forma 'email' => (int) intentos
      *
      * @var array
    */
    private $recentAttempts = array();

    /**
      * Genera la sesión con el id del usuario que ha iniciado
      *
      * @param string $pass : Contraseña sin encriptar
      * @param string $pass_repeat : Contraseña repetida sin encriptar
      *
      * @throws ModelsException cuando las contraseñas no coinciden
    */
    private function checkPassMatch(string $pass, string $pass_repeat) {
        if($pass != $pass_repeat) {
            throw new ModelsException('Las contraseñas no coinciden.');
        }
    }

    /**
      * Verifica el email introducido, tanto el formato como su existencia en el sistema
      *
      * @param string $email: Email del usuario
      *
      * @throws ModelsException en caso de que no tenga formato válido o ya exista
    */
    private function checkEmail(string $email) {
        # Formato de email
        if(!Strings::is_email($email)) {
            throw new ModelsException('El email no tiene un formato válido.');
        }
        # Existencia de email
        $email = $this->db->scape($email);
        $query = $this->db->select('id_user','users',"email='$email'",'LIMIT 1');
        if(false !== $query) {
            throw new ModelsException('El email introducido ya existe.');
        }
    }

    /**
      * Genera la sesión con el id del usuario que ha iniciado
      *
      * @param array $user_data: Arreglo con información de la base de datos, del usuario
      *
      * @return void
    */
    private function generateSession(array $user_data) {
        global $session;

        $session->set('user_id',(int) $user_data['id_user']);
    }

    /**
      * Verifica en la base de datos, el email y contraseña ingresados por el usuario
      *
      * @param string $email: Email del usuario que intenta el login
      * @param string $pass: Contraseña sin encriptar del usuario que intenta el login
      *
      * @return bool true: Cuando el inicio de sesión es correcto 
      *              false: Cuando el inicio de sesión no es correcto
    */
    private function authentication(string $email,string $pass) : bool {
        $email = $this->db->scape($email);
        $query = $this->db->select('id_user,pass','users',"email='$email'",'LIMIT 1');
    
        if(false !== $query && Strings::chash($query[0]['pass'],$pass)) {
            $this->generateSession($query[0]);
            return true;
        }

        return false;
    }

    /**
      * Establece los intentos recientes desde la variable de sesión acumulativa
      *
      * @return void
    */
    private function setDefaultAttempts() {
        global $session;

        if(null != $session->get('login_user_recentAttempts')) {
            $this->recentAttempts = $session->get('login_user_recentAttempts');
        }
    }
    
    /**
      * Establece el intento del usuario actual o incrementa su cantidad si ya existe
      *
      * @param string $email: Email del usuario
      *
      * @return void
    */
    private function setNewAttempt(string $email) {
        if(!array_key_exists($email,$this->recentAttempts)) {
            $this->recentAttempts[$email] = array(
                'attempts' => 0, # Intentos
                'time' => null # Tiempo 
            );
        } 

        $this->recentAttempts[$email]['attempts']++;
    }

    /**
      * Controla la cantidad de intentos permitidos máximos por usuario, si llega al límite,
      * el usuario podrá seguir intentando en self::MAX_ATTEMPTS_TIME segundos.
      *
      * @param string $email: Email del usuario
      *
      * @throws ModelsException cuando ya ha excedido self::MAX_ATTEMPTS
      * @return void
    */
    private function maximumAttempts(string $email) {
        global $session;

        if($this->recentAttempts[$email]['attempts'] >= self::MAX_ATTEMPTS) {
            
            if(null == $this->recentAttempts[$email]['time']) {
                $this->recentAttempts[$email]['time'] = time() + self::MAX_ATTEMPTS_TIME;
            }
            
            if(time() < $this->recentAttempts[$email]['time']) {
                throw new ModelsException('Ya ha superado el número máximo de intentos.');
            } else {
                $this->recentAttempts[$email]['attempts'] = 0;
                $this->recentAttempts[$email]['time'] = null;
            }
        }

        $session->set('login_user_recentAttempts', $this->recentAttempts);
    }

    /**
      * Realiza la acción de login dentro del sistema
      *
      * @return array : Con información de éxito/falla al inicio de sesión.
    */
    public function login() : array {
        try {
            global $http;

            # Definir de nuevo el control de intentos
            $this->setDefaultAttempts();   

            # Obtener los datos $_POST
            $email = $http->request->get('email');
            $pass = $http->request->get('pass');

            # Verificar que no están vacíos
            if($this->functions->e($email,$pass)) {
                throw new ModelsException('Credenciales incompletas.');
            }
            
            # Añadir intentos
            $this->setNewAttempt($email);
        
            # Verificar intentos 
            $this->maximumAttempts($email);

            # Autentificar
            if($this->authentication($email,$pass)) {
                return array('success' => 1, 'message' => 'Conectado con éxito.');
            }
            
            throw new ModelsException('Credenciales incorrectas.');

        } catch(ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }        
    }

    /**
      * Realiza la acción de registro dentro del sistema
      *
      * @return array : Con información de éxito/falla al registrar el usuario nuevo.
    */
    public function register() : array {
        try {
            global $http;

            # Obtener los datos $_POST
            $name = $http->request->get('name');
            $email = $http->request->get('email');
            $pass = $http->request->get('pass');
            $pass_repeat = $http->request->get('pass_repeat');

            # Verificar que no están vacíos
            if($this->functions->e($name,$email,$pass,$pass_repeat)) {
                throw new ModelsException('Todos los datos son necesarios');
            }

            # Verificar email 
            $this->checkEmail($email);

            # Veriricar contraseñas
            $this->checkPassMatch($pass,$pass_repeat);

            # Registrar al usuario
            $this->db->insert('users',array(
                'name' => $name,
                'email' => $email,
                'pass' => Strings::hash($pass)
            ));

            # Iniciar sesión
            $this->generateSession(array(
                'id_user' => $this->db->lastInsertId()
            ));

            return array('success' => 1, 'message' => 'Registrado con éxito.');
        } catch(ModelsException $e) {
            return array('success' => 0, 'message' => $e->getMessage());
        }        
    }

     /**
      * Desconecta a un usuario si este está conectado, y lo devuelve al inicio
      *
      * @return void
    */    
    public function logout() {
        global $session;

        if(null != $session->get('user_id')) {
            $session->remove('user_id');
        }

        $this->functions->redir();
    }

    /**
      * Obtiene datos de un usuario según su id en la base de datos
      *    
      * @param int $id: Id del usuario a obtener
      * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios 
      *
      * @return false|array con información del usuario
    */   
    public function getUserById(int $id, string $select = '*') {
       return $this->db->select($select,'users',"id_user='$this->id'",'LIMIT 1');
    }
    
    /**
      * Obtiene datos de un usuario según su id en la base de datos
      *    
      * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios 
      *
      * @return false|array con información de los usuarios
    */  
    public function getUsers(string $select = '*') {
       return $this->db->select($select,'users');
    }

    /**
      * Obtiene datos del usuario conectado actualmente
      *
      * @param string $select : Por defecto es *, se usa para obtener sólo los parámetros necesarios
      *
      * @throws ModelsException si el usuario no está logeado
      * @return array con datos del usuario conectado
    */
    public function getOwnerUser(string $select = '*') : array {
        try {
            if(null != $this->id_user) {
                return $this->db->select($select,'users',"id_user='$this->id_user'",'LIMIT 1')[0];
            } 
           
            throw new ModelsException('El usuario no está logeado.');

        } catch(ModelsException $e) {
            $e->errorResponse();
        }
    }

    /**
      * Instala el módulo de usuarios en la base de datos para que pueda funcionar correctamete.
      *
      * @throws \RuntimeException si no se puede realizar la query
    */
    public function install() {
        if(!$this->db->query("
            CREATE TABLE IF NOT EXISTS `users` (
                `id_user` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `email` varchar(150) NOT NULL,
                `pass` varchar(90) NOT NULL,
                PRIMARY KEY (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ")) {
            throw new \RuntimeException('No se ha podido instalar el módulo de usuarios.');
        }
        
        dump('Módulo instalado correctamente, el método <b>(new Model\Users)->install()</b> puede ser borrado.');
        exit(1);
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