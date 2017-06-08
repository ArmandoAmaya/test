<?php

/*
 * This file is part of the Ocrend Framewok 2 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ocrend\Kernel\Controllers;

class ControllersException extends \Exception {
    
     public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code,$previous);
    }
}