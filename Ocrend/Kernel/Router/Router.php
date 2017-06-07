<?php

namespace Ocrend\Kernel\Router;

use Ocrend\Kernel\Router\RouterException;
use Ocrend\Kernel\Router\RouterInterface;

final class Router implements RouterInterface {

    /**
      * @var array CONSTANTE con las reglas permitidas
    */
    const RULES = [
        'none', # Sin ninguna regla
        'letters', # Sólamente letras
        'alphanumeric', # Letras y números
        'numeric', # Sólamente números
        'numeric_positive' # Solamente números positivos
    ];

    /**
      * @var array 
    */
    private $routerCollection = array(
        '/controller' => 'home', # controlador por defecto
        '/method' => null, # método por defecto
        '/id' => null # id por defecto
    );

    /**
      * @var array 
    */
    private $routerCollectionRules = array(
        '/controller' => 'letters',
        '/method' => 'none',
        '/id' => 'none'
    );

    /**
      * @var string
    */
    private $requestUri;

    /**
      * @var string
    */
    private $host;

    /**
        * __construct() 
    */
    public function __construct() {
        global $config;
        
        $this->requestUri = $_SERVER['REQUEST_URI'];
        $this->host = strtolower(trim($config['router']['root']));
    }   

    // poner regla a la ruta
    final private function setCollectionRule(strning $index, string $rule) {
        try {
            # Verificar si la regla existe
            if(!in_array($rule,self::RULES)) {
                throw new RouterException('La regla ' . $rule . ' no existe.');
            }
            # Definir la regla para la ruta
            $this->routerCollectionRules[$index] = $rule;
        } catch(RouterException $e) {
            die($e->getMessage());
        } 
    }

    // poner ruta
    final public function setRoute(string $index, string $rule = 'none') {
        try {
            # Nombres de rutas no permitidos
            if(in_array($index,['/controller','/method','/id'])) {
                throw new RouterException('No puede definirse ' . $index . ' como índice en la ruta.');
            }

            # Sobreescribir
            unset(
                $this->routerCollection[$index],
                $this->routerCollectionRules[$index]
            );
            
            # Definir las ruta y regla
            $this->routerCollection[$index] = null;
            $this->setCollectionRule($index,$rule);
            
        } catch(RouterException $e) {
            die($e->getMessage());
        }  
    }
    
    // obtener valor de ruta
    final public function getRoute(string $index) {
        try {
            # Verificar existencia de ruta
            if(!in_array($index,$this->routerCollection)) {
                throw new RouterException('La ruta ' . $index . ' no está definida en el controlador.');
            }

            # Obtener la ruta nativa sin reglas
            $ruta = $this->routerCollection[$index];

            # Retornar ruta con la regla definida aplicada
            switch($this->routerCollectionRules[$index]) {
                case 'none':
                    return $ruta;
                break;
                case 'letters':
                    return preg_match('[[:alpha:]]', $ruta) ? $ruta : null;
                break;
                case 'alphanumeric':
                    return preg_match('[[:alnum:]]', $ruta) ? $ruta : null;
                break;
                case 'numeric':
                    return is_numeric($ruta) ? $ruta : null;
                break;
                case 'numeric_positive':
                    return (is_numeric($ruta) && $ruta >= 0) ? $ruta : null;
                break;
                default:
                    throw new RouterException('La regla ' . $this->routerCollectionRules[$index] . ' existe en RULES pero no está implementada.');
                break;
            }
        } catch(RouterException $e) {
            die($e->getMessage());
        }  
    }

    /**
        * Obtiene el nombre del controlador.
        * 
        * @return string controlador.
    */    
    final public function getController() {
        return $this->routerCollection['/controller'];
    }

    /**
        * Obtiene el método
        * 
        * @return string con el método.
        *           null si no está definido.
    */
    final public function getMethod() {
        return $this->routerCollection['/method'];
    }   

    /**
        * Obtiene el id
        *
        * @param bool $with_rules : true para obtener el id con reglas definidas para números mayores a 0
        *                           false para obtener el id sin reglas definidas
        * 
        * @return string con el id
        *           int con el id si usa reglas.
        *           null si no está definido.
    */
    final public function getId(bool $with_rules = false) {
        $id = $this->routerCollection['/id'];
        if($with_rules) {
            return (null !== $id && is_numeric($id) && $id > 0) ? $id : null;
        }

        return $id;
    }

    # Ejecuta al controlador
    final public function executeController() {
        if(null != ($controller = $this->getController())) {
            $controller = $controller . 'Controller';

            if(!is_readable('app/controllers/' . $controller . '.php')) {
                $controller = 'errorController';
            }

        } else {
            $controller = 'errorController';
        }  

        $controller = 'app\\controllers\\' . $controller;     

        new $controller($this);       
    }

}