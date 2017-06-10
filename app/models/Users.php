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
      * Intentos actuales del usuario
      *
      * @var int
    */
    private $actualAttempts = 0;

    /**
      * Últimos emails de usuarios que han intentado el login en la misma instancia
      *
      * @var array 
    */
    private $lastAttemps;

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
      * Llena el arreglo $this->lastAttempts con los emails de los intentos de login.
      *
      * @param string $email: Email del usuario que intenta el login
      *
      * @return void
    */
    private function setLoginAttempts(string $email) {
        global $session;

        $this->lastAttemps[] = $email; 
        $session->set('login_user_lastAttempts',$this->lastAttemps);
    }

    /**
      * Lógica que verifica la cantidad de intentos de login de un usuario, y bloquea el login si
      * ya ha superado o ha llegado a la máxima cantida de intentos.
      *
      * Si ya han pasado self::MAX_ATTEMPTS_TIME segundos, reinicia los contadores.
      *
      * @param string $email: Email del usuario que intenta el login
      * 
      * @throws ModelsException cuando ya ha excedido el número máximo de intentos
    */
    private function maximumAttempts(string $email) {
        global $session;

        if(in_array($email,$this->lastAttemps)) {
            $this->actualAttempts++;
            $session->set('login_user_actualAttempts',$this->actualAttempts);
        }

        if($this->actualAttempts >= self::MAX_ATTEMPTS) {

            if(null !== $session->get('login_user_timeAttempts')) {

                $session->set('login_user_timeAttempts', time() + self::MAX_ATTEMPTS_TIME );

            } else if(time() >= $session->get('login_user_timeAttempts')) {
        
                $session->set('login_user_actualAttempts',0); 
                $this->actualAttempts = 0;
            }

            throw new ModelsException('Ya ha excedido el máximo de intentos.');
        }
    }

    /**
      * Establece el contador de intentos y registro de últimos intentos
      *
      * @return void
    */
    private function sessionsAttemptsStart() {
        global $session;

        if(null !== $session->get('login_user_actualAttempts')) {
            $this->actualAttempts = $session->get('login_user_actualAttempts');
        }

        if(null !== $session->get('login_user_lastAttempts')) {
            $this->lastAttemps = $session->get('login_user_lastAttempts');
        }
    }

    /**
      * Realiza la acción de login dentro del sistema
      *
      * @return array : Con información de éxito/falla al inicio de sesión.
    */
    public function login() : array {
        try {
            global $http;

            # Obtener los datos $_POST
            $email = $http->request->get('email');
            $pass = $http->request->get('pass');

            # Verificar que no están vacíos
            if($this->functions->e($email,$pass)) {
                throw new ModelsException('Credenciales incompletas.');
            }

            # Verificar intentos de inicio de sesión
            $this->setLoginAttempts($email);
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
      * Obtiene todos los datos del usuario conectado actualmente
      *
      * @throws ModelsException si el usuario no está logeado
      * @return array con datos del usuario conectado
    */
    public function getOwnerUser() : array {
        try {
            if(null != $this->id_user) {
                return $this->db->select('*','users',"id_user='$this->id_user'",'LIMIT 1')[0];
            } 
           
            throw new ModelsException('El usuario no está logeado.');

        } catch(ModelsException $e) {
            $e->errorResponse();
        }
    }

    /**
      * __construct()
    */
    public function __construct(RouterInterface $router = null) {
        parent::__construct($router);        
        $this->sessionsAttemptsStart();
    }

    /**
      * __destruct()
    */ 
    public function __destruct() {
        parent::__destruct();
    }

}