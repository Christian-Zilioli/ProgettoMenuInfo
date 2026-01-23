<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$id = $_GET['id'] ?? null;
$msgErrore = "";

$categoria = ['nome' => '', 'ordine' => 10, 'visibile' => 1];

if ($id) {
    try {
        $stm = $pdo->prepare("SELECT * FROM categorie WHERE id_categoria = ?");
        $stm->execute([$id]);
        $categoria = $stm->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $msgErrore = $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dati = [
            ':n' => $_POST['nome'],
            ':o' => $_POST['ordine'],
            ':v' => isset($_POST['visibile']) ? 1 : 0
        ];

        if ($id) {
            $sql = "UPDATE categorie SET nome=:n, ordine=:o, visibile=:v WHERE id_categoria=:id";
            $dati[':id'] = $id;
        } else {
            $sql = "INSERT INTO categorie (nome, ordine, visibile) VALUES (:n, :o, :v)";
        }
        
        $pdo->prepare($sql)->execute($dati);
        header("Location: categorie.php");
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
    <title><?= $id ? 'Modifica' : 'Nuova' ?> Categoria | Pizzeria da Paggi</title>
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
        <h2 class="w3-xlarge w3-margin-0"><i class="fa fa-list"></i> <?= $id ? 'Modifica' : 'Nuova' ?> Categoria</h2>
    </div>

    <div class="w3-content" style="max-width:600px; margin-top:20px">
        <form method="POST" class="w3-container bg-cream w3-padding-24 w3-card-4 w3-round-large">
            <label class="text-pizza"><b>Nome Categoria</b></label>
            <input class="w3-input w3-border w3-round w3-white" type="text" name="nome" value="<?= htmlspecialchars($categoria['nome']) ?>" required placeholder="Es: Pizze Classiche">
            
            <div class="w3-margin-top">
                <label class="text-pizza"><b>Ordine di apparizione</b></label>
                <input class="w3-input w3-border w3-round w3-white" type="number" name="ordine" value="<?= $categoria['ordine'] ?>">
            </div>

            <div class="w3-margin-top w3-padding">
                <input class="w3-check" type="checkbox" name="visibile" <?= $categoria['visibile'] ? 'checked' : '' ?>>
                <label><b>Rendi visibile nel menu</b></label>
            </div>

            <div class="w3-margin-top w3-center">
                <hr>
                <a href="categorie.php" class="w3-button w3-white w3-border w3-round-large" style="width:120px">Annulla</a>
                <button type="submit" class="w3-button bg-basil w3-round-large" style="width:120px">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </form>
    </div>
</body>
</html>