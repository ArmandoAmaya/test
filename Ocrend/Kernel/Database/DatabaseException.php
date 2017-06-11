<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ocrend\Kernel\Database;

/**
 * Error en conexión, query o selección del motor.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
 */

class DatabaseException extends \Exception {

    /**
      * __construct()
    */
    public function __construct($message = null, $code = 1, \Exception $previous = null) {
        parent::__construct($message, $code,$previous);
    }

    /**
      * Muestra el error con un formato u otro dependiendo desde donde se hace la petición.
    */
    public function errorResponse() {
        if(defined('API_INTERFACE') && API_INTERFACE === '../') {
            die(json_encode(array('success' => 0, 'message' => $this->getMessage())));
        }
        
        throw new \RuntimeException($this->getMessage());
    }

}