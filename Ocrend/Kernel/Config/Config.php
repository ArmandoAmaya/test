<?php

namespace Ocrend\Kernel\Config;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

final class Config {

    const FILE_CONFIG_ROUTE = 'Ocrend/Kernel/Config/vars.yml';

    final public function readConfig() {
        try {
            $value = Yaml::parse(file_get_contents(self::FILE_CONFIG_ROUTE));
            
            return $value;
        } catch (ParseException $e) {
            die('No se puede leer el fichero config.yml');
        }
    }

}