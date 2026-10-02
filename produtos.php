<?php
session_start();

require_once 'config.php';
verificarLogin();

$mensagem = $tipoMensagem = '';

if(isset($_GET['excluir'])){
  try {
    $conn->prepare("DELETE FROM insumos WHERE id = ?")
    ->execute([$_GET['excluir']]);
    $mensagem = "Insumo excluido com sucesso!";
    $tipoMensagem = "sucesso";
  } catch (PDOException $e) {
    $mensagem = "Erro ao excluir: " . $e->getMessage();
    $tipoMensagem = "erro";
  }
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $id = $_POST['id'] ?? null;

  $data = [
    'codigo'=> trim($_POST['codigo'] ?? ''),
    'nome'=> trim($_POST['nome'] ?? ''),
    'preco'=> trim($_POST['preco'] ?? 0),
    'categoria'=> trim($_POST['categoria'] ?? ''),
    'estoque_ativo'=> trim($_POST['estoque_ativo'] ?? 0),
    'estoque_minimo'=> trim($_POST['estoque_minimo'] ?? 0),
    'ativo'=> trim($_POST['ativo'] ?? 0)
  ];
  
  if(!$data['codigo'] || !$data['nome'] || $data['preco'] <=0){
    $mensagem = 'Preencha todos os campos obrigatórios';
    $tipoMensagem = 'erro';
  } else {
    if($id > 0){
      $sql = "UPDATE insumos SET 
                codigo=:codigo,
                nome=:nome,
                preco=:preco,
                categoria=:categoria,
                estoque_ativo=:estoque_ativo,
                estoque_minimo=:estoque_minimo,
                ativo=:ativo
              WHERE id=:id";
      $data['id']=$id;
    } else {
      $sql = "INSERT INTO insumos (
                codigo, nome, preco, estoque_ativo,
                categoria, estoque_minimo, ativo
              ) 
              VALUES (
                :codigo, :nome, :preco, :estoque_ativo,
                :categoria, :estoque_minimo, :ativo
              )";
    }

    try {
      $conn->prepare($sql)->execute($data);
      $mensagem = $id > 0 ? 'Insumo atualizado!' : 'Insumo cadastrado!';
      $tipoMensagem = "sucesso";
    } catch (PDOException $e){
      $mensagem = 'Erro: ' . $e->getMessage();
      $tipoMensagem = "erro";
    }
  }
}
$InsumoEditar = null;
if(isset($_GET['editar'])){
  $stmt = $conn->prepare("SELECT * FROM insumos WHERE id = ?");
  $stmt->execute([$_GET['editar']]);
  $InsumoEditar = $stmt->fetch();
}

$busca = trim($_GET['busca'] ?? '');
$sql = "SELECT * FROM insumos WHERE ativo = 1" 
        . ($busca ? " AND (nome LIKE :b OR categoria LIKE :b)" : "") 
        . " ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute($busca ? ['b' => "%$busca%"]:[]);
$insumos = $stmt->fetchAll();

$ativo = [1=> 'ATIVO', 0=> 'INATIVO'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de insumos - Almoxarife</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Cadastro de insumos - Almoxarife</h1>
  <p><a href="index.php">Voltar</a></p>

  <?php if($mensagem): ?>
    <p style="color: <?= $tipoMensagem === 'sucesso' ? 'green' : 'red' ?>">
      <?= $mensagem?>
    </p>
  <?php endif; ?>

  <h2><?= $InsumoEditar? 'Editar' : 'Novo' ?></h2>
  <form method="POST" class="edit-update-form">
    <input type="hidden" name="id"required
      value="<?= $InsumoEditar['id'] ?? 0 ?>">
    <label>Código: * <input type="text" name="codigo" required
      value="<?= $InsumoEditar['codigo'] ?? '' ?>">
    </label><br>
    <label>Nome: * <input type="text" name="nome" required size='40'
      value="<?= $InsumoEditar['nome'] ?? '' ?>">
    </label><br>
    <label>Preço de compra: <input type="number" name="preco" required step="0.01"
      value="<?= $InsumoEditar['preco'] ?? 0 ?>">
    </label><br>
    <label>Categoria: * <input type="text" name="categoria" required
      value="<?= $InsumoEditar['categoria'] ?? '' ?>">
    </label><br>
    <label>Estoque Atual: <input type="number" name="estoque_ativo" min="0"
      value="<?= $InsumoEditar['estoque_ativo'] ?? 0 ?>">
    </label><br>
    <label>Estoque Mínimo: <input type="number" name="estoque_minimo" min="0"
      value="<?= $InsumoEditar['estoque_minimo'] ?? 0 ?>">
    </label><br>
    <label>Status (Ativo):
      <select name="ativo">
        <option value="1" <?=($InsumoEditar['ativo'] ?? 1) == 1 ? 'selected' : ''?>>Ativo</option>
        <option value="0" <?=($InsumoEditar['ativo'] ?? 1) == 0 ? 'selected' : ''?>>Inativo</option>
      </select>
    </label><br>

    <button type="submit">Salvar</button>
    <?php if($InsumoEditar) : ?><a href="produtos.php"><button type="button">Cancelar</button></a><?php endif;?>
  </form>

  <hr>
  <h2>Lista de insumos</h2>
  <form method="GET">
    <label>Buscar
      <input type="text" name="busca" value="<?= $busca ?>" placeholder="Nome, código ou categoria...">
    </label>
    <button type="submit">Buscar</button>
    <?php if($busca):?><a href="produtos.php">Limpar</a><?php endif; ?>
  </form>

  <?php if($insumos):?>
    <table border="1" cellpadding="5" cellspacing="0">
      <tr>
        <th>Código</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Preço</th>
        <th>Estoque Atual</th>
        <th>Estoque Mínimo</th>
        <th>Status</th>
      </tr>
      <?php foreach($insumos as $p): ?>
        <tr>
          <td><?=$p['codigo'] ?></td>
          <td><?=$p['nome'] ?></td>
          <td><?=$p['categoria'] ?></td>
          <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
          <td><?= $p['estoque_ativo'] ?? 0 ?></td>
          <td><?= $p['estoque_minimo'] ?></td>
          <td><?=$ativo[$p['ativo']] ?></td>
          <td>
            <a href="produtos.php?editar=<?=$p['id']?>">Editar</a>
            <a href="produtos.php?excluir=<?=$p['id']?>" onclick="return confirm('Confirmar exclusão:')">Excluir</a>
          </td>
        </tr>
      <?php endforeach;?>
    </table>
    <?php else:?>
      <p>Nenhum Insumo cadastrado...</p>
  <?php endif;?>
</body>
</html>