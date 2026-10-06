<?php
function conexionBD(){

$server = "localhost";
$username = "root";
$password = "12345678";
$db = "estetica";



try{
$conn = new PDO("mysql:host=$server;dbname=$db;", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $conn;
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

}
?>