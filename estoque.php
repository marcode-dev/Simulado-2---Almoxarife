<?php

session_start();
require_once 'config.php';
verificarLogin();

$mensagem = $tipoMensagem = $alertaEstoque = "";

if($_SERVER['REQUEST_METHOD'] === "POST"){
  $pid = (int)($_POST['insumos_id'] ?? 0);
  $tipo = $_POST['tipo'] ?? "";
  $qtd = (int)($_POST['quantidade']?? 0);
  $data = $_POST['data'] ?? "";

  if(!$pid || !$tipo || !$data || $qtd <= 0){
    $mensagem = "Preencha todos os campos!";
    $tipoMensagem = 'erro';
  } else {
    $stmt = $conn->prepare('SELECT * FROM insumos WHERE id = ?');
    $stmt->execute([$pid]);
    if (!($prod = $stmt->fetch())){
      $mensagem = "Insumo não encontrado";
      $tipoMensagem = 'erro';

    } else {
      $ant = $prod['estoque_ativo'];
      $novo = $tipo === 'ENTRADA'? $ant + $qtd : $ant - $qtd;

      if($novo < 0){
        $mensagem = "Estoque insuficiente!";
        $tipoMensagem = 'erro';

      } else {
        try{
          $conn->beginTransaction();
          $conn->prepare("INSERT INTO movimentacoes
            (tipo, data, quantidade, saldo_anterior, usuarios_id, insumos_id)
            VALUES (?,?,?,?,?,?)")->execute([$tipo === 'ENTRADA' ? 1:2,
          $data, $qtd, $ant, $_SESSION['usuario_id'], $pid]);
          $conn->prepare("UPDATE insumos SET  estoque_ativo = ? WHERE id = ?")
            ->execute([$novo, $pid]);

          $mensagem = "Movimentação registrada com sucesso!";
          $tipoMensagem = 'sucesso';

          $conn->commit();

          if($tipo === 'SAIDA' && $novo <= $prod['estoque_minimo']){
            $alertaEstoque = "ALERTA DE ESTOQUE BAIXO!
              O Insumo {$prod['nome']} está com estoque BAIXO! 
              Atual: {$novo}, Mínimo: {$prod['estoque_minimo']}";
          }
        } catch(PDOException $e) {
          if($conn->inTransaction()) $conn -> rollback();
          $mensagem = "Erro: " . $e->getMessage();
          $tipoMensagem = 'erro';
        }
      }
    }
  }
}
$insumos = $conn->query("SELECT * FROM insumos WHERE ativo = 1 ORDER BY nome")->fetchAll();
$movimentacoes = $conn->query("SELECT m.*,
  (SELECT p.nome FROM insumos p WHERE p.id = m.insumos_id) AS insumo_nome,
  (SELECT u.nome FROM usuarios u WHERE u.id = m.usuarios_id) AS usuario_nome
    FROM movimentacoes m ORDER BY m.id DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestão de Estoque - Almoxarife</title>
</head>
<body>
  <h1>Gestão de Estoque - Almoxarife</h1>
  <p><a href="index.php">Voltar</a></p>
  <hr>

  <?php if ($mensagem): ?>
    <p style="color: <?= $tipoMensagem === 'sucesso' ? 'green' : 'red' ?>;">
      <?= htmlspecialchars($mensagem) ?>
    </p>
    <hr>
  <?php endif; ?>

  <?php if ($alertaEstoque): ?>
    <p style="color: gold">
      <?= htmlspecialchars($alertaEstoque) ?>
    </p>
    <hr>
  <?php endif;?>

  <h2>Nova movimentação de Estoque</h2>
  <form method="POST">
    <label>Insumo:
      <select name="insumos_id" required>
        <option value="">Selecione</option>
        <?php foreach($insumos as $p): ?>
          <option value="<?= $p['id'] ?>">
            <?= htmlspecialchars($p['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br>
    <label>Tipo:
      <input type="radio" name="tipo" value="ENTRADA" required> ENTRADA
      <input type="radio" name="tipo" value="SAIDA" required> SAIDA
    </label>
    <br>
    <label>Quantidade:
      <input type="number" name="quantidade" min="1" required>
    </label>
    <br>
    <label>Data:
      <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
    </label>
    <br><br>
    <button type="submit">Registrar Movimentação</button>
  </form>

  <h2>Lista de insumos</h2>
  <?php if($insumos): ?>
    <table border="1">
      <tr>
        <th>Código</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Preço</th>
        <th>Estoque Atual</th>
        <th>Estoque Mínimo</th>
        <th>Aviso</th>
      </tr>
      <?php foreach($insumos as $p):?>
        <tr>
          <td><?= htmlspecialchars($p['codigo']) ?></td>
          <td><?= htmlspecialchars($p['nome']) ?></td>
          <td><?= htmlspecialchars((string)$p['categoria']) ?></td>
          <td><?= number_format($p['preco'],2,',','.') ?></td>
          <td><?= $p['estoque_ativo'] ?></td>
          <td><?= $p['estoque_minimo'] ?></td>
          <td><?= $p['estoque_ativo'] < $p['estoque_minimo'] ?
          '<strong>AVISO: Estoque Baixo</strong>' : 'Normal' ?></td>
        </tr>
        <?php endforeach;?>
    </table>
  <?php else: ?><p> Nenhum Insumo Cadastrado </p><?php endif; ?>

    <h2>Histórico de Movimentação</h2>
    <?php if ($movimentacoes):?>
      <table border="1">
        <tr>
          <th>ID</th>
          <th>Data</th>
          <th>Insumo</th>
          <th>Tipo</th>
          <th>Quantidade</th>
          <th>Saldo Anterior</th>
          <th>Usuário</th>
        </tr>
        <?php foreach($movimentacoes as $m):?>
          <tr>
            <td><?= $m['id'] ?></td>
            <td><?= date('d-m-Y', strtotime($m['data']))?></td>
            <td><?= htmlspecialchars($m['insumo_nome']) ?></td>
            <td><?= $m['tipo'] == 1 ? 'ENTRADA': 'SAIDA' ?></td>
            <td><?= $m['quantidade'] ?></td>
            <td><?= $m['saldo_anterior'] ?></td>
            <td><?= htmlspecialchars($m['usuario_nome']) ?></td>
          </tr>
          <?php endforeach;?>
      </table>
    <?php else: ?> <p>Nenhuma movimetação registrada.</p><?php endif; ?>
</body>
</html>