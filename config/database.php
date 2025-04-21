<?php
    require_once(__DIR__ . '/config.php');

    class Database {
        private static $pdo;

        public static function getConnexion(){
            if(self::$pdo === null){
                try {
                    self::$pdo = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                        DB_USER,
                        DB_PASSWORD,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                } catch(PDOException $e) {
                    echo "Erreur de connexion a la base de données." . $e->getMessage();
                    exit();
                }
            }
            
            return self::$pdo;
        }
    }


?>