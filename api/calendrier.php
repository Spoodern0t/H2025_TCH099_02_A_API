<?php 
    require 'vendor/autoload.php';
    use \Firebase\JWT\JWT;

    function getCalendrier($token){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $id_utilisateur = verifierToken($token);

        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }

        creerVue();

        $stmt = $pdo->prepare("SELECT id_utilisateur, id_calendrier, nom_utilisateur, est_membre FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ?");
        $stmt->execute([$id_utilisateur]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["userCalendars" => $results]);
    }

    // POST /calendriers
    function getCalendrierUtilisateur($id_calendrier, $token){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        creerVue();

        $id_utilisateur = verifierToken($token);

        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ? AND id_calendrier = ?");
        $stmt->execute([$id_utilisateur, $id_calendrier]);
        $user_information = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id_calendrier = ?");
        $stmt->execute([$id_calendrier]);
        $user_evenement = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM Element WHERE id_calendrier = ?");
        $stmt->execute([$id_calendrier]);
        $user_element = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode
        ([
            "id" => $user_information["id_calendrier"],
            "nom" => $user_information["nom_calendrier"],
            "description" => $user_information["description"],
            "auteur" => $user_information["nom_utilisateur"],
            "element" => $user_evenement,
            "evenement" => $user_element
        ]);

    }

    function creerCalendrier(){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);

        $token = $data["token"];
        $nom = $data["nom"];
        $description = $data["description"];

        $id_utilisateur = verifierToken($token);

        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Calendrier (nom, auteur_id, description) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $id_utilisateur["id_utilisateur"], $description]);

            $id_calendrier = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO Utilisateur_Calendrier (id_utilisateur, id_calendrier, est_membre) VALUES (?, ?, ?)");
            $stmt->execute([$id_utilisateur["id_utilisateur"], $id_calendrier, 1]);

            $stmt = $pdo->prepare("SELECT nom FROM Utilisateur WHERE id_utilisateur = ?");
            $stmt->execute([$id_utilisateur["id_utilisateur"]]);
            $nom_utilisateur = $stmt->fetch();

            $pdo->commit();

            $elements = [];
            $evenements = [];
            echo json_encode
            ([
                "id" => $id_calendrier,
                "nom" => $nom,
                "description" => $description,
                "auteur" => $nom_utilisateur["nom"],
                "elements" => $elements,
                "evenements" => $evenements
            ]);

        } catch(\Throwable $e){
            $pdo->rollBack();
            echo json_encode(["token" => false]);
        }

    }

    // TODO reparler au gars pour cette requetes
    function modifierCalendrier($id_calendrier){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        $token = $data["token"];
        $nom_calendrier = $data["nom"];
        $description = $data["description"]; 

        $id_utilisateur = verifierToken($token);

        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE Calendrier SET nom = ?, description = ? WHERE id_calendrier = ?");
            $stmt->execute([$nom_calendrier, $description, $id_calendrier]);
            $new_calendrier = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pdo->commit();
            http_response_code(200);

        } catch( \Trowable $e){
            $pdo->rollBack();
            echo json_encode(["token" => false]);
        }
    }

    // TODO revoir la suppression avec mes coequipiers.
    function supprimerCalendrier($id_calendrier){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        $token = $data['token']; 
        $id_utilisateur = verifierToken($token);
        
        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }

        $stmt = $pdo->prepare("SELECT auteur_id FROM Calendrier WHERE id_calendrier = ?");
        $stmt->execute([$id_calendrier]);
        $id_auteur = $stmt->fetchColumn();

        try {
            $pdo->beginTransaction();


            $stmt = $pdo->prepare("DELETE FROM Utilisateur_Calendrier WHERE id_calendrier = ? AND id_utilisateur = ?");
            $stmt->execute([$id_calendrier, $id_utilisateur]);

            if($id_auteur === $id_utilisateur){
                $stmt = $pdo->prepare("DELETE FROM Calendrier WHERE id_calendrier = ?");
                $stmt->execute([$id_calendrier]);
            }

            $pdo->commit();
            http_response_code(200);
            
        } catch(\Throwable $e){
            $pdo->rollBack();
            echo json_encode(["token" => false]);
        }
    }

    function creerVue(){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $pdo->exec("DROP VIEW IF EXISTS Vue_Utilisateur_Calendrier");
        $stmt = $pdo->prepare(
            "CREATE VIEW Vue_utilisateur_Calendrier AS SELECT
                Utilisateur.id_utilisateur,
                Calendrier.id_calendrier,
                Utilisateur.nom AS nom_utilisateur,
                Utilisateur_Calendrier.est_membre,
                Calendrier.nom AS nom_calendrier,
                Calendrier.description
            FROM
                Utilisateur
            INNER JOIN 
                Calendrier ON Utilisateur.id_utilisateur = Calendrier.auteur_id
            INNER JOIN
                Utilisateur_Calendrier ON Calendrier.id_calendrier = Utilisateur_Calendrier.id_calendrier"
        );
        $stmt->execute();
    }

    function verifierToken($token){
        include(__DIR__ . '/../config.php');

        $stmt = $pdo->prepare("SELECT id_utilisateur FROM Connexion WHERE token = ?");
        $stmt->execute([$token]);
        $id_utilisateur = $stmt->fetchColumn();
 
        if($id_utilisateur !== false){
            return $id_utilisateur;
        } else {
            return false;
        }

    }

?>