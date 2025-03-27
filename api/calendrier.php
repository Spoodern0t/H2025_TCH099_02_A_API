<?php 

    // TODO Get /calendriers 
    function getCalendrier(){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);

        // Verifier si les données ont été bien décoder
        if(json_last_error() !== JSON_ERROR_NONE){
            http_response_code(400);
            echo json_encode(["token" => false]);
            exit;
        }

        // Verifier le format du tableau $data
        if(!is_array($data)){
            echo json_encode(["token" => false]);
            exit;
        }

        $id_utilisateur = $data['id'];

        $stmt = $pdo->prepare("SELECT * FROM calendriers WHERE auteur_id = ?");
        $stmt->execute([$id_utilisateur]);
        $tab_calendrier = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // TODO retourne seulement true pour l'instant modifier quand je vais recevoir le document.
        echo json_encode(["token" => true,
                         "tableau-calendrier" => $tab_calendrier]);
    }

    // POST /calendriers
    function postCalendrier(){
        include(__DIR__ . '/../config.php');
        header('Content-type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);

        // Verifier si les données ont été bien décoder
        if(json_last_error() !== JSON_ERROR_NONE){
            http_response_code(400);
            echo json_encode(["token" => false]);
            exit;
        }

        // Verifier le format du tableau $data
        if(!is_array($data)){
            echo json_encode(["token" => false]);
            exit;
        }

        $nomCalendrier = $data['nom-calendrier'];
        $description = $data['description'];
        $id_utilisateur = $data['auteurId'];

        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO calendriers (nom, description, auteur_id) VALUES (?,?,?)");
            $stmt->execute([$nomCalendrier, $description, $id_utilisateur]);

            $id_calendrier = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO calendrier_utilisateur (user_id, id_calendrier, role) VALUES (?,?,?)");
            $stmt->execute([$id_utilisateur, $id_calendrier, 'AUTHOR']);

            $pdo->commit();
            // TODO retourne seulement true pour l'instant modifier quand je vais recevoir le document.
            echo json_encode(["token" => true]);

        } catch( \Throwable $e){
            $pdo->rollback();
            echo json_encode(["token" => false]);
        }
    }
?>