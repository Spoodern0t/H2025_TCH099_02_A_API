<?php 
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';
    use \Firebase\JWT\JWT;

    class Calendrier{
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        function getCalendrier($token){
            header('Content-type: application/json');

            $pdo = $this->global->getPdo();

            $id_utilisateur = $this->global->verifierToken($token);

            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            $this->global->creerVue();

            $stmt = $pdo->prepare("SELECT id_utilisateur, id_calendrier, nom_utilisateur, nom_calendrier, est_membre FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ?");
            $stmt->execute([$id_utilisateur]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($results);

            echo json_encode($results);
        }

        function getCalendrierUtilisateur($id_calendrier, $token){
            header('Content-type: application/json');
            $pdo = $this->global->getPdo();

            $this->global->creerVue();

            $id_utilisateur = $this->global->verifierToken($token);

            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            $stmt = $pdo->prepare("SELECT * FROM Vue_Utilisateur_Calendrier WHERE id_utilisateur = ? AND id_calendrier = ?");
            $stmt->execute([$id_utilisateur, $id_calendrier]);
            $user_information = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_information);

            $stmt = $pdo->prepare("SELECT * FROM Evenement WHERE id_calendrier = ?");
            $stmt->execute([$id_calendrier]);
            $user_evenement = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_evenement);

            $stmt = $pdo->prepare("SELECT * FROM Element WHERE id_calendrier = ?");
            $stmt->execute([$id_calendrier]);
            $user_element = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->global->transformCalendrierInfo($user_element);

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
            header('Content-type: application/json');
            $pdo = $this->global->getPdo();

            $data = json_decode(file_get_contents("php://input"), true);

            $token = $data["token"];
            $nom = $data["nom"];
            $description = $data["description"];

            $id_utilisateur = $this->global->verifierToken($token);

            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
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
                echo json_encode(["token" => false]);
            }

        }

        // TODO reparler au gars pour cette requetes
        function modifierCalendrier($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data["token"];
            $nom_calendrier = $data["nom"];
            $description = $data["description"]; 

            $id_utilisateur = $this->global->verifierToken($token);

            if(!$id_utilisateur){
                echo json_encode(["token" => false]);
                return;
            }

            try{
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE Calendrier SET nom = ?, description = ? WHERE id_calendrier = ?");
                $stmt->execute([$nom_calendrier, $description, $id_calendrier]);

                $pdo->commit();
                http_response_code(200);

            } catch(\Throwable $e){
                $pdo->rollBack();
                echo json_encode(["token" => false]);
            }
        }

        // TODO revoir la suppression avec mes coequipiers.
        function supprimerCalendrier($id_calendrier){
            header('Content-type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();

            $token = $data['token']; 

            $id_utilisateur = $this->global->verifierToken($token);

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
    }
?>