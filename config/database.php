<?php
    require_once(__DIR__ . '/config.php');

    class Database {
        private static $pdo;

        public static function getConnexion(){
            if(self::$pdo === null){
                try {
                    /*
                    * 1) Le premiere pdo est utiliser pour le local.
                    * 2) Le deuxieme est utiliser pour la bd azure
                    */

                        //  self::$pdo = new PDO(
                        //      "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                        //      DB_USER,
                        //      DB_PASSWORD,

                        self::$pdo = new PDO(
                           "sqlsrv:server=tcp:mysqlserverv1.database.windows.net,1433;Database=" . DB_NAME,
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