<?php

    require_once './api/controllers/Router.php';

    $routeur = new Router();

    //Connexion d'un utilisateur
    $routeur->post('/index.php/inscription', function() {
        require_once './api/models/inscription.php';

        $inscription = new Inscription();

        if (method_exists($inscription, 'inscription')) {
            $inscription->inscription();
        } else {
            echo json_encode(["token" => false]);
        }
    });

    // Inscription d'un utilisateur
    $routeur->post('/index.php/connexion', function(){
        require_once './api/models/connexion.php';

        $connexion = new Connexion();

        if(method_exists($connexion, 'connexion')){
            $connexion->connexion();
        } else {
            echo json_encode(["token" => false]);
        }
    });

    // Déconnexion d'un utilisateur
    $routeur->delete('/index.php/deconnexion', function(){
        require_once './api/models/deconnexion.php';

        $deconnexion = new Deconnexion();

        if(method_exists($deconnexion,'deconnexion')){
            $deconnexion->deconnexion();
        } else {
            echo json_encode(["token" => false]);
        }
    });

    /*
    * Toutes les routes qui agissent en rapport avec le calendrier.
    */
    $routeur->post('/index.php/calendrier', function() {
        require_once './api/models/calendrier.php';

        $calendrier = new Calendrier();

        if(method_exists($calendrier,'creerCalendrier')){
            $calendrier->creerCalendrier();
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->get('/index.php/usercalendars/{token}', function($token){
        require_once "./api/models/calendrier.php";

        $calendrier = new Calendrier();

        if(method_exists($calendrier,'getCalendrier')){
            $calendrier->getCalendrier($token);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->get('/index.php/calendrier/{calendrier_id}/token/{token}', function($id_calendrier, $token) {
        require_once './api/models/calendrier.php';

        $calendrier = new Calendrier();

        if(method_exists($calendrier, 'getCalendrierUtilisateur')){
            $calendrier->getCalendrierUtilisateur($id_calendrier, $token);
        } else {
            echo json_encode (['token' => false]);
        }

    });

    $routeur->put('/index.php/calendrier/{calendrier_id}', function($id_calendrier){
        require_once './api/models/calendrier.php';

        $calendrier = new Calendrier();

        if(method_exists($calendrier,'modifierCalendrier')){
            $calendrier->modifierCalendrier($id_calendrier);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->delete('/index.php/calendrier/{calendrier_id}', function($id_calendrier){
        require_once './api/models/calendrier.php';

        $calendrier = new Calendrier();

        if(method_exists($calendrier, 'supprimerCalendrier')){
            $calendrier->supprimerCalendrier($id_calendrier);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    /*
    * "Toutes les routes qui agissent en rapport avec les evemenents" 
    */
    $routeur->post('/index.php/calendrier/{calendrier_id}/evenement', function($id_calendrier){
        require_once './api/models/evenement.php';

        $evenement = new Evenement();

        if(method_exists($evenement,'creerEvenement')){
            $evenement->creerEvenement($id_calendrier);
        } else {
            echo json_encode(["token" => false]);
        }
    });

    $routeur->put('/index.php/evenement/{id_evenement}', function($id_evenement) {
        require_once './api/models/evenement.php';

        $evenement = new Evenement();

        if(method_exists($evenement,'modifierEvenement')){
            $evenement->modifierEvenement($id_evenement);
        } else {
            json_encode(["token" => false]);
        }
    });

    $routeur->delete('/index.php/evenement/{id_evenement}', function($id_evenement) {
        require_once './api/models/evenement.php';

        $evenement = new Evenement();

        if(method_exists($evenement,'supprimerEvenement')){
            $evenement->supprimerEvenement($id_evenement);
        } else {
            json_encode(["token" => false]);
        }
    });

    /*
    * "Toutes les routes qui agissent en rapport avec les evemenents" 
    */
    $routeur->post('/index.php/calendrier/{id}/element', function($id_calendrier){
        require_once './api/models/element.php';

        $element = new Element();

        if(method_exists($element, 'creerElement')){
            $element->creerElement($id_calendrier);
        } else {
            json_encode(["token" => false]);
        }
    });


    $routeur->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    
?>
