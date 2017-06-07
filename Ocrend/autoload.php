<?php

spl_autoload_register('__ocrend_autoload');

function __ocrend_autoload(string $class) {
    if(is_readable($class . '.php')) {
        require_once $class . '.php';
    }
}