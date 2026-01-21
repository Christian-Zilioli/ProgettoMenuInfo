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
<html>
<head>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Gestione Categoria</title>
</head>
<body class="w3-light-grey">
    <div class="w3-container w3-teal"><h2><?= $id ? 'Modifica' : 'Nuova' ?> Categoria</h2></div>
    <form method="POST" class="w3-container w3-white w3-margin w3-padding w3-card-4">
        <label>Nome Categoria</label>
        <input class="w3-input w3-border" type="text" name="nome" value="<?= htmlspecialchars($categoria['nome']) ?>" required>
        
        <label>Ordine di apparizione</label>
        <input class="w3-input w3-border" type="number" name="ordine" value="<?= $categoria['ordine'] ?>">

        <div class="w3-margin-top">
            <input class="w3-check" type="checkbox" name="visibile" <?= $categoria['visibile'] ? 'checked' : '' ?>>
            <label>Rendi visibile nel menu</label>
        </div>

        <div class="w3-margin-top">
            <button type="submit" class="w3-button w3-teal">Salva</button>
            <a href="categorie.php" class="w3-button w3-red">Annulla</a>
        </div>
    </form>
</body>
</html>