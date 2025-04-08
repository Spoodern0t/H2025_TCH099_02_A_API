<?php

    require_once 'Router.php';

    $routeur = new Router();

    //Connexion d'un utilisateur
    $routeur->post('/index.php/inscription', function() {
        require_once './api/inscription.php';
    });

    // Inscription d'un utilisateur
    $routeur->post('/index.php/connexion', function(){
        require_once './api/connexion.php';
    });

    $routeur->delete('/index.php/deconnexion', function(){
        require_once './api/deconnexion.php';
    });

    $routeur->post('/index.php/calendrier', function() {
        require_once './api/calendrier.php';

        if(function_exists('creerCalendrier')){
            creerCalendrier();
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->get('/index.php/usercalendars/{token}', function($token){
        require_once "./api/calendrier.php";

        if(function_exists('getCalendrier')){
            getCalendrier($token);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->get('/index.php/calendrier/{calendrier_id}/token/{token}', function($id_calendrier, $token) {
        require_once './api/calendrier.php';

        if(function_exists('getCalendrierUtilisateur')){
            getCalendrierUtilisateur($id_calendrier, $token);
        } else {
            echo json_encode (['token' => false]);
        }

    });

    $routeur->put('/index.php/calendrier/{calendrier_id}', function($id_calendrier){
        require_once './api/calendrier.php';

        if(function_exists('modifierCalendrier')){
            modifierCalendrier($id_calendrier);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->delete('/index.php/calendrier/{calendrier_id}', function($id_calendrier){
        require_once './api/calendrier.php';

        if(function_exists('supprimerCalendrier')){
            supprimerCalendrier($id_calendrier);
        } else {
            echo json_encode(["token" => false]);
        }
    });





    $routeur->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
?>
