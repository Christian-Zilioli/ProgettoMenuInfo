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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?= $id ? 'Modifica' : 'Nuovo' ?> Allergene | Pizzeria da Paggi</title>
    <style>
        body { background-color: #f7f3ee; color: #333; }
        .bg-pizza { background-color: #b22222 !important; color: white; }
        .bg-basil { background-color: #2e7d32 !important; color: white; }
        .bg-cream { background-color: #fffaf2; }
        .text-pizza { color: #b22222; }
    </style>
</head>
<body>
    <div class="w3-container bg-pizza w3-padding-16">
        <h2 class="w3-xlarge w3-margin-0"><i class="fa fa-exclamation-triangle"></i> <?= $id ? 'Modifica' : 'Nuovo' ?> Allergene</h2>
    </div>

    <div class="w3-content" style="max-width:600px; margin-top:20px">
        <form method="POST" class="w3-container bg-cream w3-padding-24 w3-card-4 w3-round-large">
            <label class="text-pizza"><b>Nome Allergene</b></label>
            <p class="w3-small w3-text-grey">Esempio: Glutine, Lattosio, Frutta a guscio</p>
            <input class="w3-input w3-border w3-round w3-white" type="text" name="nome" value="<?= htmlspecialchars($allergene['nome']) ?>" required>

            <div class="w3-margin-top w3-center">
                <hr>
                <a href="allergeni.php" class="w3-button w3-white w3-border w3-round-large" style="width:120px">Annulla</a>
                <button type="submit" class="w3-button bg-basil w3-round-large" style="width:120px">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </form>
    </div>
</body>
</html>