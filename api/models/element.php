<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Element{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        function creerElement($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token'];
            $nom = $data['nom'];
            $description = $data['description'];
            $id_evenement = $data['id_evenement'];
            
            $date_debut = $data['dateDebut'];
            if($date_debut != null){
                $date_debut = new DateTime($date_debut);
                $date_debut = $date_debut->format('Y-m-d H:i:s');
            }

            
            $date_fin = $data['dateFin'];
            if($date_fin != null){
                $date_fin = new DateTime($date_fin);
                $date_fin = $date_fin->format('Y-m-d H:i:s');
            }

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

                $stmt = $pdo->prepare("INSERT INTO Element (id_evenement, id_calendrier, nom, description, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_evenement, $id_calendrier, $nom, $description, $date_debut, $date_fin]);

                // Pour les test en locale
                //$stmt = $pdo->prepare("SELECT * FROM Element WHERE id_calendrier = ? ORDER BY id_evenement DESC LIMIT 1");

                $stmt = $pdo->prepare("SELECT TOP 1 * FROM Element WHERE id_calendrier = ? ORDER BY id_evenement DESC");
                $stmt->execute([$id_calendrier]);
                $info_element = $stmt->fetch(PDO::FETCH_ASSOC);

                $info_evenement = [];

                if($id_evenement != null){
                    $stmt = $pdo->prepare("SELECT id_evenement, nom, description, couleur FROM Evenement WHERE id_evenement = ?");
                    $stmt->execute([$id_evenement]);
                    $info_evenement = $stmt->fetch(PDO::FETCH_ASSOC);
                    $info_evenement["id_evenement"] = (int) $info_evenement["id_evenement"];
                } else {
                    $info_evenement = null;
                }

                $pdo->commit();
                echo json_encode
                    ([
                        "id" => (int)$info_element['id_element'],
                        "calendrierId" => (int)$id_calendrier,
                        "nom" => $nom,
                        "description" => $description,
                        "evenement" =>  $info_evenement,
                        "dateDebut" => $info_element['date_debut'],
                        "dateFin" => $info_element['date_fin']
                    ]);
            }catch(\Throwable $e){
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(["message" => "La création de l'élément à échouer."]);
            }

        }

        function modifierElement($id_element){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token'];
            $id_calendrier = $data['calendrierId'];
            $nom = $data['nom'];
            $description = $data['description'];
            $id_evenement = $data['id_evenement'];

            $date_debut = $data['dateDebut'];
            if($date_debut != null){
                $date_debut = new DateTime($date_debut);
                $date_debut = $date_debut->format('Y-m-d H:i:s');
            }
            
            $date_fin = $data['dateFin'];
            if($date_fin != null){
                $date_fin = new DateTime($date_fin);
                $date_fin = $date_fin->format('Y-m-d H:i:s');
            }

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
            
            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE Element SET nom = ?, description = ?, date_debut = ?, date_fin = ?, id_evenement = ? WHERE id_element = ? AND id_calendrier = ?");
                $stmt->execute([$nom, $description, $date_debut, $date_fin, $id_evenement, $id_element, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);
            } catch(\Throwable $e){
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(["message" => "La modification de l'èlèment à échouer."]);
            }
        }

        function supprimerElement($id_element){
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

                $stmt = $pdo->prepare("DELETE FROM Element WHERE id_element = ? AND id_calendrier = ?");
                $stmt->execute([$id_element, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);
            }catch(\Throwable $e){
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(["message" => "La suppresion de l'élément à échouer."]);
            }

        }
    }



?>