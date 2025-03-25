<?php

    require_once 'Router.php';

    $routeur = new Router();


    // Route pour se connecter la connexion et l'inscriptions.
    $routeur->post('/index.php/inscription', function() {
        require_once './api/inscription.php';
    });
    $routeur->post('/index.php/connexion', function(){
        require_once './api/connexion.php';
    });

    $routeur->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>


