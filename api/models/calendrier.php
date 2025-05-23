<?php 
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    
    class Calendrier{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        // Fonction changer avec la nouvelle methode des tokens expirés
        function getCalendrier($token){
            header('Content-type: application/json');

            $pdo = $this->global->getPdo();
            $id_utilisateur = null;

            $tab_token = $this->global->verfierExpirationToken($token);

            if($tab_token['status'] === false){
                http_response_code(401);
                echo json_encode
                ([
                   "message" => $tab_token['message'] 
                ]);
                exit();

            } else {
                $id_utilisateur = $tab_token['utilisateur'];
            }

            if($id_utilisateur === null){
                http_response_code(400);
                echo json_encode(["message" => "L'id de l'utilisateur n'est pas valide."]);
                exit();
            }

            $stmt = $pdo->prepare("SELECT id_utilisateur, id_calendrier, nom_utilisateur, nom_calendrier, est_membre, description FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ?");
            $stmt->execute([$id_utilisateur]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($results);

            echo json_encode($results);
        }

        function getCalendrierUtilisateur($id_calendrier, $token){
            header('Content-type: application/json');
            $pdo = $this->global->getPdo();

            // Nouvelle methode
            $tab_token = $this->global->verfierExpirationToken($token);

            if($tab_token['status'] === false){
                http_response_code(401);
                echo json_encode
                ([
                   "message" => $tab_token['message'] 
                ]);
                exit();

            } else {
                $id_utilisateur = $tab_token['utilisateur'];
            }

            if($id_utilisateur === null){
                http_response_code(400);
                echo json_encode(["message" => "L'id de l'utilisateur n'est pas valide."]);
                exit();
            }

            $stmt = $pdo->prepare("SELECT * FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ? AND id_calendrier = ?");
            $stmt->execute([$id_utilisateur, $id_calendrier]);
            $user_information = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_information);

            $stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id_calendrier = ?");
            $stmt->execute([$id_calendrier]);
            $user_evenement = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_evenement);

            $stmt = $pdo->prepare("SELECT el.*, 
            ev.id_evenement AS id_ev_evenement,
            ev.id_calendrier AS id_ev_calendrier,
            ev.nom AS ev_nom,
            ev.description AS ev_description,
            ev.couleur 
            FROM Element el LEFT JOIN Evenement ev ON ev.id_evenement = el.id_evenement WHERE el.id_calendrier = ?");
            $stmt->execute([$id_calendrier]);
            $user_element = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_element);

            $elements = [];

            foreach($user_element as $row){
                $element = [
                    "id_calendrier" => (INT)$row["id_calendrier"],
                    "id_element" => (INT)$row["id_element"],
                    "nom" => $row["nom"],
                    "description" => $row["description"],
                    "date_debut" => $row["date_debut"],
                    "date_fin" => $row["date_fin"],
                    "evenement" => null
                ];

                if(!empty($row["id_evenement"])){
                    $element["evenement"] = [
                        "id_evenement"=> (INT)$row["id_ev_evenement"],
                        "id_calendrier" => (INT)$row["id_ev_calendrier"],
                        "nom" => $row["ev_nom"],
                        "description" => $row["ev_description"],
                        "couleur" => $row["couleur"]
                    ];
                }

                $elements[] = $element;
            }

                echo json_encode
                ([
                    "id" => $user_information["id_calendrier"],
                    "nom" => $user_information["nom_calendrier"],
                    "description" => $user_information["description"],
                    "auteur" => $user_information["nom_utilisateur"],
                    "element" => $elements,
                    "evenement" => $user_evenement
                ]);       
        }

        function creerCalendrier(){
            header('Content-type: application/json');
            $pdo = $this->global->getPdo();

            $data = json_decode(file_get_contents("php://input"), true);

            $token = $data["token"];
            $nom = $data["nom"];
            $description = $data["description"];

            // Nouvelle méthode
            $tab_token = $this->global->verfierExpirationToken($token);

            if($tab_token['status'] === false){
                http_response_code(401);
                echo json_encode
                ([
                   "message" => $tab_token['message'] 
                ]);
                exit();

            } else {
                $id_utilisateur = $tab_token['utilisateur'];
            }

            if($id_utilisateur === null){
                http_response_code(400);
                echo json_encode(["message" => "L'id de l'utilisateur n'est pas valide."]);
                exit();
            }

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO Calendrier (nom, auteur_id, description) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $id_utilisateur, $description]);

                $id_calendrier = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO Utilisateur_Calendrier (id_utilisateur, id_calendrier, est_membre) VALUES (?, ?, ?)");
                $stmt->execute([$id_utilisateur, $id_calendrier, 1]);

                $stmt = $pdo->prepare("SELECT nom FROM Utilisateur WHERE id_utilisateur = ?");
                $stmt->execute([$id_utilisateur]);
                $nom_utilisateur = $stmt->fetch();

                $pdo->commit();

                $elements = [];
                $evenements = [];
                echo json_encode
                ([
                    "id" => (int)$id_calendrier,
                    "nom" => $nom,
                    "description" => $description,
                    "auteur" => $nom_utilisateur["nom"],
                    "elements" => $elements,
                    "evenements" => $evenements
                ]);

            } catch(\Throwable $e){
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(["message" => "Problème lors de la création du calendrier"]);
            }

        }

        function modifierCalendrier($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data["token"];
            $nom_calendrier = $data["nom"];
            $description = $data["description"]; 

            // Nouvelle méthode
            $tab_token = $this->global->verfierExpirationToken($token);

            if($tab_token['status'] === false){
                http_response_code(401);
                echo json_encode
                ([
                   "message" => $tab_token['message'] 
                ]);
                exit();

            } else {
                $id_utilisateur = $tab_token['utilisateur'];
            }

            if($id_utilisateur === null){
                http_response_code(400);
                echo json_encode(["message" => "L'id de l'utilisateur n'est pas valide."]);
                exit();
            }

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE Calendrier SET nom = ?, description = ? WHERE id_calendrier = ?");
                $stmt->execute([$nom_calendrier, $description, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);

            } catch(\Throwable $e){
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(["message" => "Problème lors de la modification du calendrier."]);
            }
        }

        // TODO revoir la suppression avec mes coequipiers.
        function supprimerCalendrier($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token']; 

            // Nouvelle méthode
            $tab_token = $this->global->verfierExpirationToken($token);

            if($tab_token['status'] === false){
                http_response_code(401);
                echo json_encode
                ([
                   "message" => $tab_token['message'] 
                ]);
                exit();

            } else {
                $id_utilisateur = $tab_token['utilisateur'];
            }

            if($id_utilisateur === null){
                http_response_code(400);
                echo json_encode(["message" => "L'id de l'utilisateur n'est pas valide."]);
                exit();
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
                http_response_code(400);
                echo json_encode(["message" => "Problème lors de la suppression du calendrier."]);
            }
        }
    }
?>