<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    class Element{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        // TODO reparler de la requetes
        function creerElement($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token'];
            $nom = $data['nom'];
            $description = $data['description'];
            $id_evenement = $data['id_evenement'];
            $date_debut = $data['dateDebut'];
            $date_fin = $data['dateFin'];

            $id_utilisateur = $this->global->verifierToken($token);
            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            var_dump($id_evenement);

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO Element (id_evenement, id_calendrier, nom, description, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_evenement, $id_calendrier, $nom, $description, $date_debut, $date_fin]);

                // A enlever plus tard
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
                echo json_encode(["token" => false]);
            }

        }

        function modifierElement($id_element){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            // Je pense pas que j'ai besoin $id_calendrier ou $id_evenement

            $token = $data['token'];
            $id_calendrier = $data['calendrierId'];
            $nom = $data['nom'];
            $description = $data['description'];
            $id_evenement = $data['id_evenement'];
            $date_debut = $data['dateDebut'];
            $date_fin = $data['dateFin'];

            $id_utilisateur = $this->global->verifierToken($token);
            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }
            
            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE Element SET nom = ?, description = ?, date_debut = ?, date_fin = ? WHERE id_element = ? AND id_calendrier = ?");
                $stmt->execute([$nom, $description, $date_debut, $date_fin, $id_element, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);
            } catch(\Throwable $e){
                $pdo->rollback();
                echo json_encode(["token" => false]);
            }
        }

        function supprimerElement($id_element){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token'];
            $id_calendrier = $data['calendrierId'];

            $id_utilisateur = $this->global->verifierToken($token);
            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("DELETE FROM Element WHERE id_element = ? AND id_calendrier = ?");
                $stmt->execute([$id_element, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);
            }catch(\Throwable $e){
                $pdo->rollback();
                echo json_encode(["token" => false]);
            }

        }
    }



?>