<?php
require_once 'config.php';
session_start();

//Verifica se o usuário já está logado
if(isset($_SESSION['usuario_id'])) {
  header('Location: index.php');
}

$mensagemErro = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? ''); // trim remove espaços em branco do inicio/final da string
  $senha = $_POST['senha'] ?? '';

  if(empty($email) || empty($senha)) {
    $mensagemErro = "Preencha todos os campos!";
  } else {
    try {
      $sql = "SELECT * FROM usuarios WHERE email = :email";
      $stmt = $conn->prepare($sql);
      $stmt->execute(['email' => $email]);
      $usuario = $stmt->fetch();

      if(!$usuario){
        // Se não existir usuário
        $mensagemErro = "Email ou senha incorretos!";
      } else if ($usuario['ativo'] != 1) {
        // Se coluna do baco ativo for diferene de 1(inativo)
        $mensagemErro = "Usuário inativo.";
      } else if ($senha == $usuario['senha']) {
        // Sucesso
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        header('Location: index.php');
      }
    } catch (PDOException $e) {
      $mensagemErro = "Erro ao processar login: " . $e->getMessage();
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Almoxarife</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" tyoe="image/png" href="https://cdn-icons-png.flaticon.com/512/9805/9805668.png">
</head>
<body>
  <h1>Sistema Inventário</h1>

  <?php 
  if(!empty($mensagemErro)) {
    echo $mensagemErro;
  }
  ?>
  <form action="" method="post">
    <div>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
  </div>
  <br>
  <div>
    <label for="senha">Senha:</label>
    <input type="password" id="senha" name="senha" required>
  </div>  
  <br>
  <button type="submit">Entrar</button>
  </form>
  
</body>
</html>