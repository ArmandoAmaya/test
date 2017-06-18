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

/**
 * Error de ejecución dentro del generador.
 *
 * @author Brayan Narváez <prinick@ocrend.com>
*/

class CommandException extends \Exception {

    /**
      * Muestra el error con un formato u otro dependiendo desde donde se hace la petición.
    */
    private function showError() {
        exit("\nERROR: " . $this->getMessage() . "\n");
    }

    /**
      * __construct()
    */
    public function __construct($message = null, $code = 1, \Exception $previous = null) {
        parent::__construct($message, $code,$previous);
        $this->showError();
    }
}