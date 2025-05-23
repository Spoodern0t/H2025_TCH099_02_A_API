<?php
    require 'vendor/autoload.php';
    require './api/services/globalMethode.php';
    require './config/config.php';

    require 'vendor/PHPMailer/PHPMailer/src/Exception.php';
    require 'vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
    require 'vendor/PHPMailer/PHPMailer/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    use \Firebase\JWT\JWT;

    class Inscription {
        public $global;

        public function __construct() {
            $this->global = new GlobalMethode();
        }

        function inscription(){
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents("php://input"), true);
            $pdo = $this->global->getPdo();
            

            // Verifier si les données ont été bien décoder
            if(json_last_error() !== JSON_ERROR_NONE){
                http_response_code(400);
                echo json_encode(["token" => false]);
                exit();
            }

            // Verifier le format du tableau $data
            if(!is_array($data)){
                echo json_encode(["token" => false]);
                exit();
            }

            $nomUtilisateur = $data['user-name'];
            $courriel = $data['email'];
            $motDePasse = $data['password'];

            $stmt = $pdo->prepare("SELECT courriel FROM Utilisateur WHERE courriel = ?");
            $stmt -> execute([$courriel]);
            $confirmerCourriel = $stmt -> fetch();

            if($confirmerCourriel){
                http_response_code(400);
                echo json_encode(["message" => "Le courriel est invalide."]);
                exit();
            } else {
                $MDPHache = password_hash($motDePasse, PASSWORD_DEFAULT);

                $mail = new PHPMailer(true);
                $key = constant('CLE_SECRETE');
                try{
                    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    //Enable verbose debug output
                    $mail->isSMTP();                                            //Send using SMTP
                    $mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
                    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                    $mail->Username   = 'multus.calendrius.sender@gmail.com';   //SMTP username
                    $mail->Password   = constant('CLE_SECRETE_EMAIL');          //SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                    $mail->Port       = 465;
                    
                    $mail->addAddress($courriel);
                    
                    $payload = [
                        "email" => $courriel,
                        "motDePasse" => $motDePasse,
                        "iat" => time(),
                        // Le token pour se connecter au email expire dans les 1 heures après sa création
                        // donc l'utilisateur donc cliquer sur le lien avant les 1 heures sinon une autre token
                        // sera créer et un autre email sera envoyer.
                        "ext" => time() + 3600
                    ];

                    $tokenEmail = JWT::encode($payload, $key, 'HS256');

                    $mail->isHTML(true);                                  //Set email format to HTML
                    $mail->Subject = 'Activation de votre email.';
                    $mail->Body    = "<p>Veuillez cliquez sur ce lien pour activez votre compte:</p>
                                        <a href = 'http://localhost/H2025_TCH099_02_A_API/index.php/valider-email/$tokenEmail'> http://localhost/H2025_TCH099_02_A_API/index.php/valider-email/$tokenEmail</a>";

                    $mail->send();
                } catch(Exception $e){
                    http_response_code(400);
                    echo json_encode(["message" => "La création du email à échouer."]);
                    exit();
                }

                try{
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, courriel, mot_de_passe) VALUES (?, ?, ?)");
                    $stmt -> execute([$nomUtilisateur, $courriel, $MDPHache]);

                    $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE courriel = ?");
                    $stmt -> execute([$courriel]);
                    $utilisateur = $stmt ->fetch(PDO::FETCH_ASSOC);

                    $id_Utilisateur = $utilisateur['id_utilisateur'];

                    $stmt2 = $pdo->prepare("INSERT INTO Calendrier (nom, auteur_id) VALUES (?, ?)");
                    $stmt2->execute([$nomUtilisateur, $id_Utilisateur]);

                    $stmt = $pdo->prepare("SELECT id_calendrier FROM Calendrier WHERE auteur_id = ?");
                    $stmt -> execute([$id_Utilisateur]);
                    $calendrier = $stmt ->fetch(PDO::FETCH_ASSOC);

                    $id_Calendrier = $calendrier['id_calendrier'];

                    $stmt = $pdo->prepare("INSERT INTO Utilisateur_Calendrier (id_utilisateur, id_calendrier, est_membre) VALUES (?, ?, ?)");
                    $stmt->execute([$id_Utilisateur, $id_Calendrier, 1]);
                    
                    $pdo->commit();
                    http_response_code(200);

                } catch( \Throwable $e){
                    $pdo->rollback();
                    http_response_code(400);
                    echo json_encode(["message" => "La création du nouvelle utilisateur à échouer."]);
                }
            }
        }
    }
?>