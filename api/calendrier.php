<?php 
    require 'vendor/autoload.php';
    use \Firebase\JWT\JWT;

    // TODO Get /calendriers 
    function getCalendrier($token){

        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $key = constant('CLE_SECRETE'); 

        $stmt = $pdo->prepare("SELECT id_utilisateur FROM Connexion WHERE token = ?");
        $stmt->execute([$token]);
        $tab_utilisateur = $stmt->fetch();

        $utilisateur = $tab_utilisateur["id_utilisateur"];


        $pdo->exec("DROP VIEW IF EXISTS Vue_utilisateur_Calendrier");
        $stmt = $pdo->prepare(
            "CREATE VIEW Vue_utilisateur_Calendrier AS SELECT
                Utilisateur.id_utilisateur,
                Calendrier.auteur_id,
                Utilisateur.nom,
                Utilisateur_Calendrier.est_membre
            FROM
                Utilisateur
            INNER JOIN 
                Calendrier ON Utilisateur.id_utilisateur = Calendrier.auteur_id
            INNER JOIN
                Utilisateur_Calendrier ON Calendrier.id_calendrier = Utilisateur_Calendrier.id_calendrier"
        );
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT * FROM Vue_utilisateur_Calendrier WHERE id_utilisateur = ?");
        $stmt->execute([$utilisateur]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["userCalendars" => $results]);

    }

    // POST /calendriers
    function getCalendrierUtilisateur($id_calendrier, $token){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        // Valider que l'utilisateur connectée a belle et bien accès au calendrier demander.
        $pdo->exec("DROP VIEW IF EXISTS Vue_utilisateur_Calendrier");
        // TODO
        $stmt = $pdo->prepare(
            "CREATE VIEW Vue_utilisateur_Calendrier AS SELECT
                Utilisateur.id_utilisateur,
                Calendrier.auteur_id,
                Utilisateur.nom,
                Utilisateur_Calendrier.est_membre
            FROM
                Utilisateur
            INNER JOIN 
                Calendrier ON Utilisateur.id_utilisateur = Calendrier.auteur_id
            INNER JOIN
                Utilisateur_Calendrier ON Calendrier.id_calendrier = Utilisateur_Calendrier.id_calendrier"
        );
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT (id) FROM Connexion WHERE token =?");
        $stmt->execute([$token]);
        $id_utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        var_dump($id_utilisateur);

        // -> Si oui, retourner information sur le calendrier, plus liste element et evenement du calendrier
        // -> Si non, retourner false

    }

?>