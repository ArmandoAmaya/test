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
 * Modelo Contact
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Contact extends Models implements ModelsInterface {

    /**
     * Email general 
     * @var String
     */
    private $email_general;
    /**
     * Latitud google maps
     * @var Numeric
     */
    private $lat;
    /**
     * Longitud google maps
     * @var Numeric
     */
    private $lng;

    /**
     * Direccion
     * @var JSON
     */
    private $direccion;

    /**
     * Como llegar
     * @var JSON
     */
    private $como;

    /**
     * Teléfono 1
     * @var Int
     */
    private $phone_1;

    /**
     * Teléfono 2
     * @var Int
     */
    private $phone_2;

    /**
     * Emails
     * @var JSON
     */
    private $emails;

    /**
     * Trabaja con nosotros
     * @var JSON
     */
    private $desc;

    /**
     * Códigos de los números de teléfono
     * @var JSON
     */
    const PHONE_CODES = array(414,212,261,424,426,416);

    /**
     * Control de errores
     * @return void
     */
      
    final private function Errors() {
      global $http;
      $this->email_general = $http->request->get('email_general');
      $this->lat = $http->request->get('lat');
      $this->lng = $http->request->get('lng');

      # Todos los campos son obligatorios
      if ($this->functions->e($this->email_general, $http->request->get('dir_es'), $http->request->get('dir_en'), $http->request->get('como_es'), $http->request->get('como_en'), $http->request->get('desc_es'), $http->request->get('desc_en'), $http->request->get('phone_1'), $http->request->get('phone_2'),$this->lat, $this->lng)){
        throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
      }

      # Formato de los correos
      if (!Strings::is_email( $this->email_general )) {
        throw new ModelsException('Los correos deben tener un formato válido (correo@demo.com).');
      }

      # Campos numéricos
      if (!is_numeric($this->lat) || !is_numeric($this->lng)) {
        throw new ModelsException('La <b>Latitud</b> y <b>Longitud</b> solo deben contener números.');
      }

      # Proceso de emails
      $this->emails = $this->getEmailsInJSON($http->request->get('emails'));
      
      # Guardamos los datos en variables
      $this->direccion = $this->ConverInAssocJSON($http->request->get('dir_es'), $http->request->get('dir_en'));
      $this->como = $this->ConverInAssocJSON($http->request->get('como_es'), $http->request->get('como_en'));
      $this->desc = $this->ConverInAssocJSON($http->request->get('desc_es'), $http->request->get('desc_en'));
      
      $this->phone_1 = $this->phone_action($http->request->get('phone_1'));
      $this->phone_2 = $this->phone_action($http->request->get('phone_2'));
      
    }

    /**
     * Valida los emails y los devuelve como un json
     * @param array $emails - Array con los correos
     * @return String json con los correos
     */
    
    final private function getEmailsInJSON(array $emails) : string{
      # Recorremos cada email 
      foreach ($emails as $e) {
        
        # Validamos que no estén vacios
        if ($this->functions->emp($e)) {
          throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
        }
        # Validamos el formato
        if (!Strings::is_email( $e )) {
          throw new ModelsException('Los correos deben tener un formato válido (correo@demo.com).');
        }
      }

      return json_encode($emails);
    }

    /**
     * Valida un número de teléfono
     * @param int $phone: número de teléfono 
     * @return el número de teléfono limpio
     */

    final private function phone_action($phone) {
      # Validación numérica
      if (!is_numeric($phone)) {
        throw new ModelsException('Los números de teléfonos solo deben contener números.');
      }

      # Validación de longitud
      $long_phone =  strlen($phone);
      if (!in_array( $long_phone, [10,11] )) {
        throw new ModelsException('Los números de teléfonos deben contener entre 10 u 11 dígitos.');
      }
      # Validación del código
      $codes = implode('|',self::PHONE_CODES);
      $regex = $long_phone == 10 ? "#^($codes)#" : "#^0($codes)#";
      if (!preg_match($regex, $phone)) {
        throw new ModelsException('El número de teléfono debe iniciar con un código válido ('.$codes.').');
      }

      return $this->db->scape($phone);

    }

    /**
     * Combina dos campos en un json
     * @param string $field1 - Campo 1 
     * @param string $field2 - Campo 2
     * @return String json con los campos
     */
    final private function ConverInAssocJSON(string $field1, string $field2) : string {
      $field1 = $this->db->scape($field1);
      $field2 = $this->db->scape($field2);

      return json_encode(array('es' => nl2br($field1), 'en' => nl2br($field2)));

    }

    /**
      * Devuelve un arreglo para la api
      *
      * @return array
    */
    final public function Config() : array {
      global $http;
      try {
        # Controlar errores de entrada en el formulario
        $this->errors();

        # actualizamos los datos en la db
        $this->db->update('contact', array(
          'email_general' => $this->email_general,
          'latitud' => $this->lat,
          'longitud' => $this->lng,
          'address' => $this->direccion,
          'como' => $this->como,
          'phone_1' => $this->phone_1,
          'phone_2' => $this->phone_2,
          'emails' => $this->emails,
          'work' => $this->desc
        ), "id_contact = '1'", 'LIMIT 1');
        
        return array('success' => 1, 'message' => 'Configuración realizada de forma exitosa.');
      } catch(ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      } 
      
    }
    /**
     * Obtiene los datos de contacto
     * @return matriz con datos de contacto
     */
    final public function get() : array{
      $contact = $this->db->select('*', 'contact', "id_contact = '1'", 'LIMIT 1')[0];
      $contact['address'] = json_decode($contact['address'],true);
      $contact['como'] = json_decode($contact['como'],true);
      $contact['emails'] = json_decode($contact['emails'],true);
      $contact['work'] = json_decode($contact['work'],true);
      return $contact;
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