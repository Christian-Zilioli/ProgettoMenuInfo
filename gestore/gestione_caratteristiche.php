<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$id = $_GET['id'] ?? null;
$msgErrore = "";
$caratteristica = ['nome' => ''];

if ($id) {
    try {
        $stm = $pdo->prepare("SELECT * FROM caratteristiche WHERE id_caratteristica = ?");
        $stm->execute([$id]);
        $caratteristica = $stm->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $msgErrore = $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($id) {
            $pdo->prepare("UPDATE caratteristiche SET nome=? WHERE id_caratteristica=?")->execute([$_POST['nome'], $id]);
        } else {
            $pdo->prepare("INSERT INTO caratteristiche (nome) VALUES (?)")->execute([$_POST['nome']]);
        }
        header("Location: caratteristiche.php?successo=1");
        exit;
    } catch (PDOException $e) { $msgErrore = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Gestione Caratteristica</title>
</head>
<body class="w3-light-grey">
    <div class="w3-container w3-teal"><h2><?= $id ? 'Modifica' : 'Nuova' ?> Caratteristica</h2></div>
    <form method="POST" class="w3-container w3-white w3-margin w3-padding w3-card-4">
        <label>Nome Caratteristica (es: Vegano, Piccante)</label>
        <input class="w3-input w3-border" type="text" name="nome" value="<?= htmlspecialchars($caratteristica['nome']) ?>" required>

        <div class="w3-margin-top">
            <button type="submit" class="w3-button w3-teal">Salva</button>
            <a href="caratteristiche.php" class="w3-button w3-red">Annulla</a>
        </div>
    </form>
</body>
</html>