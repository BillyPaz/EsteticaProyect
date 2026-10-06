<?php
// config/conexion.php
// Conexión central a la base de datos estetica

function conexionBD(): PDO {

    $server   = "127.0.0.1";
    $port     = "3306";               
    $username = "root";
    $password = "";
    $db       = "estetica";

    try {
        $conn = new PDO(
            "mysql:host=$server;port=$port;dbname=$db;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}
