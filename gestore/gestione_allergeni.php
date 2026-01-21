<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$id = $_GET['id'] ?? null;
$msgErrore = "";
$allergene = ['nome' => ''];

if ($id) {
    try {
        $stm = $pdo->prepare("SELECT * FROM allergeni WHERE id_allergene = ?");
        $stm->execute([$id]);
        $allergene = $stm->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $msgErrore = $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($id) {
            $pdo->prepare("UPDATE allergeni SET nome=? WHERE id_allergene=?")->execute([$_POST['nome'], $id]);
        } else {
            $pdo->prepare("INSERT INTO allergeni (nome) VALUES (?)")->execute([$_POST['nome']]);
        }
        header("Location: allergeni.php?successo=1");
        exit;
    } catch (PDOException $e) { $msgErrore = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Gestione Allergene</title>
</head>
<body class="w3-light-grey">
    <div class="w3-container w3-teal"><h2><?= $id ? 'Modifica' : 'Nuovo' ?> Allergene</h2></div>
    <form method="POST" class="w3-container w3-white w3-margin w3-padding w3-card-4">
        <label>Nome Allergene (es: Glutine, Lattosio)</label>
        <input class="w3-input w3-border" type="text" name="nome" value="<?= htmlspecialchars($allergene['nome']) ?>" required>

        <div class="w3-margin-top">
            <button type="submit" class="w3-button w3-teal">Salva</button>
            <a href="allergeni.php" class="w3-button w3-red">Annulla</a>
        </div>
    </form>
</body>
</html>