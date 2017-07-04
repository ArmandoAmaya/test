# Ocrend-Framework

![Hecho en PHP 7](https://img.shields.io/packagist/l/doctrine/orm.svg)
![Licencia MIT](https://img.shields.io/badge/php-7-blue.svg)
![Versión Estable](https://img.shields.io/badge/stable-2.0.0-blue.svg)

## Introducción
### ¿Qué es Ocrend Framework 2?

Es un framework sencillo escrito en **PHP 7** que utiliza la arquitectura **MVC** y componentes de symfony como base de su aplicación en el desarrollo web, adicionalmente pretende acelerar el proceso de desarrollo con unas cuantas herramientas. La curva de aprendizaje es bastante baja, el concepto del framework es ofrecer una arquitectura de sencillo manejo, inclusive para aquellos que jamás han programado utilizando MVC.

### ¿Por qué utilizarlo?

* No requiere manejo de una shell (aunque existe la posibilidad con un pequeño programa escrito en php por consola)
* Es pequeño y de muy fácil aprendizaje
* Es eficiente y seguro
* Fomenta la creación de código limpio, comentado, bien estructurado y eficiente
* Se configura en 1 minuto y se puede empezar a desarrollar con el
* No estás interesado en librerías gigantes como PEAR
* No estás interesado en aprender un framework gigante como Symfony, Laravel o ZendFramework
* No necesitas gestionar rutas
* Incluye Silex en sus dependencias, para manejo de API REST correctamente configurado
* Soporte de múltiples bases de datos con distintos motores usando PDO **simultáneamente**
  * MySQL 5.1+
  * Oracle
  * PostgreSQL
  * MS SQL
  * SQLite
  * CUBRID
  * Interbase/Firebid
  * ODBC
  * MSSQL

## Requisitos

Para colocar el framework en producción se requiere un servidor que cumpla las siguientes características:

* PHP 7
* APACHE 2

## Configuración

Abrir el fichero **./Ocrend/Kernel/Config/Ocrend.ini.yml* y modificar
```yml
# Configurar la conexión con base de datos
database:
  host: Nombre del servidor para la base de datos, suele ser localhost siempre
  user: Nombre de usuario para conectarse a la base de datos
  pass: Contraseña del usuario para conectarse a la base de datos
  name: Nombre de la base de datos a conectar con el framework
  port: Puerto para conectarse con la base de datos, por defecto suele ser 1521
  protocol: Protocolo para conectarse con la DB, por defecto suele ser TCP
  motor: Tipo de motor de base de datos a utilizar, por defecto suele ser mysql
  
# Configurar el sitio
site:
  name: Nombre de su aplicación web
  author: Su nombre para el copyright en el código
  author_email: Su email para el copyright en el código
  url: URL completa para acceder al framework, es importante el "/" del final
  router:
    path: URL sin el http/https desde la cual se entra al framework, es importante el "/" del final
    protocol: Protocolo de acceso, por defecto siempre suele ser http a menos que se tenga un SSL
```
Si modificamos correctamente esos datos, y guardamos el archivo, ya podremos empezar a trabajar.

## Hola Mundo desde consola
Es tan sencillo como hacer lo siguiente desde consola desde la ruta principal del framework
```
php gen.php app:cv Hola
```
Y luego accedemos desde la url a www.miweb.com/hola/

## Documentación

[Github Wiki](https://github.com/prinick96/Ocrend-Framework/wiki) -
[Web Oficial](http://framework.ocrend.com)
