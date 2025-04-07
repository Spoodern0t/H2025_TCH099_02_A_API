<?php 
    require 'vendor/autoload.php';
    use \Firebase\JWT\JWT;

    // TODO Get /calendriers 
    function getCalendrier($token){

        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

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

        creerVue();

        $stmt = $pdo->prepare("SELECT id_utilisateur FROM Connexion WHERE token = ?");
        $stmt->execute([$token]);
        $tab_utilisateur = $stmt->fetch();

        $utilisateur = $tab_utilisateur["id_utilisateur"];

        $stmt = $pdo->prepare("SELECT * FROM Vue_utilisateur_Calendrier WHERE id_utilisateur = ? AND id_calendrier = ?");
        $stmt->execute([$utilisateur, $id_calendrier]);
        $user_information = $stmt->fetchAll(PDO::FETCH_ASSOC);

        var_dump($user_information);

        


    }

    function creerVue(){
        include(__DIR__ . '/../config.php');

        $pdo->exec("DROP VIEW IF EXISTS Vue_utilisateur_Calendrier");
        $stmt = $pdo->prepare(
            "CREATE VIEW Vue_utilisateur_Calendrier AS SELECT
                Utilisateur.id_utilisateur,
                Calendrier.id_calendrier,
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

    }

?>