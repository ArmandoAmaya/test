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
 * Modelo Redes
 *
 * @author DevSystemVzla <prinick@ocrend.com>
 */

class Redes extends Models implements ModelsInterface {
    /**
     * Link de instagram
     * @var string
     */
    private $instagram;

    /**
     * Link de twitter
     * @var string
     */
    private $twitter;

    /**
     * Link de facebook
     * @var string
     */
    private $facebook;

    /**
     * Link de google plus
     * @var string
     */
    private $google;

    /**
     * Valida el formato de la url de una red social
     * @param string $url: Url a evaluar
     * @param string $red: Tipo de red social [instagram,twitter,facebook,google-plus]
     * @return la url de la red social
     */
    final private function getLink(string $url, string $red) {
      if (!preg_match("/^(https?:\/\/)?(www\.)?$red.(com|es)\/[a-zA-Z0-9(\.\?)?]/", $url)) {
        throw new ModelsException('Debes introducir una url de perfil válida de <b>'.$red.'</b>.');
      }

      return $url;
    }

    /**
      * Configura las redes sociales
      *
      * @return array
    */
    final public function Redes() : array {
      global $http;
      try {

        # Validación de campos vacíos
        if (!$this->functions->all_full($http->request->all())) {
          throw new ModelsException('Todos los campos con <b>*</b> deben ser llenados.');
        }

        # Guardamos en variables las redes
        $this->instagram = $this->getLink($http->request->get('instagram'),'instagram');
        $this->twitter = $this->getLink($http->request->get('twitter'), 'twitter');
        $this->facebook = $this->getLink($http->request->get('facebook'), 'facebook');
        $this->google = $this->getLink($http->request->get('google'), 'plus.google');

        # Actualizamos la db
        $this->db->update('redes', array(
          'instagram' => $this->instagram,
          'twitter' => $this->twitter,
          'facebook' => $this->facebook,
          'google' => $this->google
        ), "id_red = '1'",'LIMIT 1');

        return array('success' => 1, 'message' => 'Redes sociales configuradas exitosamente.');
      } catch (ModelsException $e) {
        return array('success' => 0, 'message' => $e->getMessage());
      }
      
    }
    /**
     * Obtiene las redes sociales
     * @return matriz con las redes sociales
     */
    final public function get() {
      return $this->db->select('*', 'redes', "id_red = '1'", 'LIMIT 1')[0];
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