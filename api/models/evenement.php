<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Evenement {
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

    //TODO Requetes modifier parler avec mes coequipiers
    // A completer
    function creerEvenement($id_calendrier){
        header('Content-type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $pdo = $this->global->getPdo();

        $token = $data['token'];
        $titre = $data['titre'];
        $description = $data['description'];
        $couleur = $data['couleur'];

        // Nouvelle méthode
        $tab_token = $this->global->verfierExpirationToken($token);

        if($tab_token['status'] === false){
            http_response_code(401);
            echo json_encode
            ([
               "message" => $tab_token['message'] 
            ]);
            return;

        } else {
            $id_utilisateur = $tab_token['utilisateur'];
        }

        if($id_utilisateur === null){
            echo json_encode(["token" => false]);
            return;
        }

        // Ancienne méthode
        /*
        $id_utilisateur = $this->global->verifierToken($token);
        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }
        */

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Evenement (id_calendrier, nom, description, couleur) VALUES (?,?,?,?)");
            $stmt->execute([$id_calendrier, $titre, $description, $couleur]);

            // Ligne pour locale pu va etre enlever 
            //$stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id_calendrier = ? ORDER BY id_evenement DESC LIMIT 1");
            
            // Ligne pour azure ashsadjkfhalskjdhfljkas
            $stmt = $pdo->prepare("SELECT TOP 1 * FROM Evenement WHERE id_calendrier = ? ORDER BY id_evenement DESC");
            
            $stmt->execute([$id_calendrier]);
            $info_evenement = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->commit();
            echo json_encode
            ([
                "id" => (int)$info_evenement['id_evenement'],
                "calendrierId" => (int)$id_calendrier,
                "titre" => $info_evenement['nom'],
                "description" => $info_evenement['description'],
                "couleur" => $info_evenement['couleur']
            ]);

        } catch(\Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false, "message" => $e->getMessage()]);
        }

    }

    function modifierEvenement($id_evenement){
        header('Content-type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $pdo = $this->global->getPdo();
        
        $token = $data['token'];
        $titre = $data['titre'];
        $description = $data['description'];
        $couleur = $data['couleur'];

        // Nouvelle méthode
        $tab_token = $this->global->verfierExpirationToken($token);

        if($tab_token['status'] === false){
            http_response_code(401);
            echo json_encode
            ([
               "message" => $tab_token['message'] 
            ]);
            return;

        } else {
            $id_utilisateur = $tab_token['utilisateur'];
        }

        if($id_utilisateur === null){
            echo json_encode(["token" => false]);
            return;
        }

        // Ancienne méthode
        /*
        $id_utilisateur = $this->global->verifierToken($token);
        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }
        */

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE Evenement SET nom = ?, description = ?, couleur = ? WHERE id_evenement = ?");
            $stmt->execute([$titre, $description, $couleur, $id_evenement]);

            $pdo->commit();
            http_response_code(200);
        
        } catch(\Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false]);
        }
    }

    function supprimerEvenement($id_evenement){
        header('Content-type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $pdo = $this->global->getPdo();

        $token = $data['token'];
        $id_calendrier = $data['calendrierId'];

        // Nouvelle méthode
        $tab_token = $this->global->verfierExpirationToken($token);

        if($tab_token['status'] === false){
            http_response_code(401);
            echo json_encode
            ([
               "message" => $tab_token['message'] 
            ]);
            return;

        } else {
            $id_utilisateur = $tab_token['utilisateur'];
        }

        if($id_utilisateur === null){
            echo json_encode(["token" => false]);
            return;
        }

        // Ancienne méthode
        /*
        $id_utilisateur = $this->global->verifierToken($token);
        if(!$id_utilisateur){
            echo json_encode(["token" => false]);
            return;
        }
        */

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM Evenement WHERE id_evenement = ? AND id_calendrier = ?");
            $stmt->execute([$id_evenement, $id_calendrier]);

            $pdo->commit();
            http_response_code(200);

        }catch(\Throwable $e){
            $pdo->rollback();
            echo json_encode(["token", false]);
        }
    }
}

?>