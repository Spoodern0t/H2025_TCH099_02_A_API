<?php
/*

    bash : composer require firebase/php-jwt

    use Firebase\JWT\JWT;

    // Génération du token
    $key = "votre_cle_secrete";

    if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        $payload = [
            "id" => $utilisateur['id'],
            "nom" => $utilisateur['nom'],
            "exp" => time() + 3600 // Expire dans 1 heure
        ];
        $token = JWT::encode($payload, $key, 'HS256');
        echo json_encode(["token" => $token, "message" => "Connexion réussie"]);
    } else {
        echo json_encode(["token" => false, "message" => "Identifiants invalides"]);
    }

    // Vérification du token 
        $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        echo json_encode(["message" => "Token valide", "user_id" => $decoded->id]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Token invalide: " . $e->getMessage()]);
    }

*/
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    header('Content-Type: application/json');;

    $data = json_decode(file_get_contents("php://input"), true);

    // Verifier si les données ont été bien décoder
    if(json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        echo json_encode(["token" => false, "message" => "Erreur JSON: " .json_last_error_msg()]);
        exit;
    }

    // Verifier le format du tableau $data
    if(!is_array($data)){
        echo json_encode(["token" => false, "message" => "Erreur de format du JSON"]);
        exit;
    }

    include '../config.php';

    $courriel = $data['courriel'];
    $motDePasse = $data['mot-de-passe'];

    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe FROM utilisateurs WHERE courriel = ?");
    $stmt-> execute([$courriel]);
    $utilisateur = $stmt->fetch();

    // Verifier si les informations reçu sont les bonnes.
    // Envoie de JSON pour la verification bonne ou mouvaise.
    if($utilisateur){
        if($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            echo json_encode(["token" => true]);
        } else {
            echo json_encode(["token" => false , "message" => "Mot de passe incorrect"]); 
        }
    } else {
        echo json_encode(["token" => false, "message" => "Utilisateur incorrect"]);
    }

?>