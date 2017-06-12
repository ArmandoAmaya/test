<?php

use app\models as Model;

$app->post('/login', function() use($app) {
    $u = new Model\Users;   

    return $app->json($u->login());   
});

$app->post('/register', function() use($app) {
    $u = new Model\Users; 

    return $app->json($u->register());   
});

/*
$app->post('/', function() use($app) {

    return $app->json(array());
    
});
*/