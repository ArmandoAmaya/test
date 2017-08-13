<?php

use app\models as Model;

/**
    * Inicio de sesión
    *
    * @return json
*/  
$app->post('/login', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->login());   
});

/**
    * Registro de un usuario
    *
    * @return json
*/
$app->post('/register', function() use($app) {
    $u = new Model\Users; 

    return $app->json($u->register());   
});

/**
    * Recuperar contraseña perdida
    *
    * @return json
*/
$app->post('/lostpass', function() use($app) {
    $u = new Model\Users; 

    return $app->json($u->lostpass());   
});

/**
  * Crea un usuario administrador
  *
  * @return json
*/
$app->post('/admins/crear', function() use($app) {
  $a = new Model\Admins; 

  return $app->json($a->Crear());   
});

/**
  * Edita un usuario administrador
  *
  * @return json
*/
$app->post('/admins/editar', function() use($app) {
  $a = new Model\Admins; 

  return $app->json($a->Editar());   
});


/**
  * Acción vía ajax de Categorias en api/categorias/crear
  *
  * @return json
*/
$app->post('/categorias/crear', function() use($app) {
  $c = new Model\Categorias; 

  return $app->json($c->add());   
});


/**
  * Acción vía ajax de Categorias en api/categorias/editar
  *
  * @return json
*/
$app->post('/categorias/editar', function() use($app) {
  $c = new Model\Categorias; 

  return $app->json($c->edit());   
});


/**
  * Acción vía ajax de Proyectos en api/proyectos/crear
  *
  * @return json
*/
$app->post('/proyectos/crear', function() use($app) {
  $p = new Model\Proyectos; 

  return $app->json($p->add());   
});


/**
  * Acción vía ajax de Proyectos en api/proyectos/editar
  *
  * @return json
*/
$app->post('/proyectos/editar', function() use($app) {
  $p = new Model\Proyectos; 

  return $app->json($p->edit());   
});

/**
  * Acción vía ajax de Proyectos en api/proyectos/tmp
  *
  * @return json
*/
$app->post('/upload/tmp', function() use($app) {
  $p = new Model\Proyectos; 

  return $app->json($p->UploadTmp());   
});

/**
  * Acción vía ajax de Proyectos en api/upload/delete
  *
  * @return json
*/
$app->post('/upload/delete', function() use($app) {
  $p = new Model\Proyectos; 

  return $app->json($p->DeleteDir());   
});

/**
  * Acción vía ajax de Proyectos en api/delete/file
  *
  * @return json
*/
$app->post('/delete/file', function() use($app) {
  $p = new Model\Proyectos; 

  return $app->json($p->DeleteFile());   
});


/**
  * Acción vía ajax de Home en api/home
  *
  * @return json
*/
$app->post('/home', function() use($app) {
  $h = new Model\Home; 

  return $app->json($h->Add_Slider());   
});

/**
  * Acción vía ajax de Home en api/home/edit
  *
  * @return json
*/
$app->post('/home/edit', function() use($app) {
  $h = new Model\Home; 

  return $app->json($h->Edit_Slider());   
});

/**
  * Acción vía ajax de Home en api/home/delete
  *
  * @return json
*/
$app->post('/home/delete', function() use($app) {
  $h = new Model\Home; 

  return $app->json($h->Delete_Slider());   
});


/**
  * Acción vía ajax de Contact en api/contact
  *
  * @return json
*/
$app->post('/contact', function() use($app) {
  $c = new Model\Contact; 

  return $app->json($c->Config());   
});


/**
  * Acción vía ajax de Redes en api/redes
  *
  * @return json
*/
$app->post('/redes', function() use($app) {
  $r = new Model\Redes; 

  return $app->json($r->Redes());   
});