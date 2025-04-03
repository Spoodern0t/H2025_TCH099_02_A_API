<?php
    
        $serverName = "mysqlserverv1.database.windows.net,1433";
        $database = "TCH099-Calendrier";
        $username = "azureuser";
        $password = "ETS2025!";

        define('CLE_SECRETE', "ASFDASDIENAIJSDFBSALJF");

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
        
?>