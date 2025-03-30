<?php
    // Config de test
        $host = '127.0.0.1';
        $dbname = 'tch099_calendrier';
        $username = 'root';
        $password = '';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    
        try{
            $pdo = new PDO(
                "mysql:host={$host};dbname={$dbname}",
                $username,
                $password
            );
        } catch (PDOException $e){
            echo "Erreur de connexion à " . $e->getMessage();
            exit();
        }
        
?>