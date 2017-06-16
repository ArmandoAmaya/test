$app->{{method}}('/{{view}}', function() use($app) {
    ${{var_model}} = new Model\{{model}}; 

    return $app->json(${{var_model}}->{{method_model}}());   
});