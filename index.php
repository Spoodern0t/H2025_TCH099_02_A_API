<?php

    require_once 'Router.php';
    

    $routeur = new Router();


    //Connexion d'un utilisateur
    $routeur->post('/index.php/inscription', function() {
        require_once './api/inscription.php';
    });

    $routeur->get('/test', function() {
        echo 'Route test fonctionne';
    });

    // Inscription d'un utilisateur
    $routeur->post('/index.php/connexion', function(){
        require_once './api/connexion.php';
    });

    // Création d'une calendrier par un utilisateur
    $routeur->post('/calendrier', function() {
        require_once './api/calendrier.php';

        if(function_exists('postCalendrier')){
            postCalendrier();
        } else {
            echo json_encode (['token' => false]);
        }

    });

    // Récupération des calendriers relié a un utilisateur
    $routeur->get('/index.php/calendriers', function() {
        require_once './api/calendrier.php';

        if(function_exists('getCalendrier')){
            getCalendrier();
        } else
            echo json_encode (['token' => false]);

    });


    $routeur->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>
