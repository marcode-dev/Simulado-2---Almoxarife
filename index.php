<?php
session_start();
require_once 'config.php';
verificarLogin();

if(isset($_GET['logout'])){
  session_destroy();
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Almoxarife</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Almoxarife</h1>
  <div>
    <p>
      Usuário Logado: <?php echo $_SESSION['usuario_nome']; ?>
    </p>
  </div>
  <hr>
  <h2>Menu</h2>
  <ul>
    <li><a href="produtos.php">Cadastro de Produtos</a></li>
    <li><a href="estoque.php">Estoque</a></li>
    <li><a href="?logout=1">Sair</a></li>
  </ul>
</body>
</html>