<?php

        $serverName = getenv('DB_SERVER');   
        $dbname = getenv('DB_DATABASE');     
        $username = getenv('DB_USERNAME');   
        $password = getenv('DB_PASSWORD');

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    
        try{
            $pdo = new PDO(
                "sqlsrv:server=$serverName;Database=$dbname;Encrypt=1;TrustServerCertificate=0",
                $username,
                $password, 
                $options
            );
        } catch (PDOException $e){
            echo "Erreur de connexion à " . $e->getMessage();
            exit();
        }
        
?>