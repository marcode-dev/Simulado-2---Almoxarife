<?php

$host = 'localhost'; // 172.0.0.1
$port = '3307';
$dbname = 'simulado_saep_02';
$username = 'root';
$password = '';

try {
  $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
  
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na Conexão: " . $e->getMessage());
}

function verificarLogin(){
  if(!isset($_SESSION['usuario_id'])){
    header('Location: login.php');
    exit;
  }
}