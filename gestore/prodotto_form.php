<?php
require 'connessione.php';

$pdo = new PDO($connString, $connUser, $connPass);

$msgErrore = [];

$modifica = false;
$id_prodotto = null;

$nome = '';
$descrizione = '';
$prezzo = '';
$id_categoria = '';
$disponibile = 1;
$allergeni_sel = [];
$car_sel = [];

// dati base
try {
    $categorie = $pdo->query("SELECT * FROM categorie ORDER BY ordine")->fetchAll(PDO::FETCH_ASSOC);
    $allergeni = $pdo->query("SELECT * FROM allergeni")->fetchAll(PDO::FETCH_ASSOC);
    $caratteristiche = $pdo->query("SELECT * FROM caratteristiche")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msgErrore["lettura"] = $e->getMessage();
}

// MODIFICA → carico prodotto
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $modifica = true;
    $id_prodotto = (int)$_GET['id'];

    try {
        $stm = $pdo->prepare("SELECT * FROM prodotti WHERE id_prodotto = :id");
        $stm->bindValue(':id', $id_prodotto, PDO::PARAM_INT);
        $stm->execute();
        $p = $stm->fetch(PDO::FETCH_ASSOC);

        if ($p) {
            $nome = $p['nome'];
            $descrizione = $p['descrizione'];
            $prezzo = $p['prezzo'];
            $id_categoria = $p['id_categoria'];
            $disponibile = $p['disponibile'];
        }

        // allergeni selezionati
        $stm = $pdo->prepare(
            "SELECT id_allergene FROM prodotti_allergeni WHERE id_prodotto = :id"
        );
        $stm->bindValue(':id', $id_prodotto, PDO::PARAM_INT);
        $stm->execute();
        $allergeni_sel = array_column($stm->fetchAll(PDO::FETCH_ASSOC), 'id_allergene');

        // caratteristiche selezionate
        $stm = $pdo->prepare(
            "SELECT id_caratteristica FROM prodotti_caratteristiche WHERE id_prodotto = :id"
        );
        $stm->bindValue(':id', $id_prodotto, PDO::PARAM_INT);
        $stm->execute();
        $car_sel = array_column($stm->fetchAll(PDO::FETCH_ASSOC), 'id_caratteristica');

    } catch (PDOException $e) {
        $msgErrore["caricamento"] = $e->getMessage();
    }
}

// SALVATAGGIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $prezzo = $_POST['prezzo'] ?? 0;
    $id_categoria = $_POST['id_categoria'] ?? null;
    $disponibile = isset($_POST['disponibile']) ? 1 : 0;

    try {

        if ($modifica) {

            // update prodotto
            $sql = "UPDATE prodotti SET
                        nome = :nome,
                        descrizione = :descrizione,
                        prezzo = :prezzo,
                        id_categoria = :id_categoria,
                        disponibile = :disp
                    WHERE id_prodotto = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(':nome', $nome);
            $stm->bindValue(':descrizione', $descrizione);
            $stm->bindValue(':prezzo', $prezzo);
            $stm->bindValue(':id_categoria', $id_categoria);
            $stm->bindValue(':disp', $disponibile, PDO::PARAM_INT);
            $stm->bindValue(':id', $id_prodotto, PDO::PARAM_INT);
            $stm->execute();

            // reset relazioni
            $pdo->prepare("DELETE FROM prodotti_allergeni WHERE id_prodotto = :id")
                ->execute([':id' => $id_prodotto]);

            $pdo->prepare("DELETE FROM prodotti_caratteristiche WHERE id_prodotto = :id")
                ->execute([':id' => $id_prodotto]);

        } else {

            // inserimento prodotto
            $sql = "INSERT INTO prodotti
                    (nome, descrizione, prezzo, id_categoria, disponibile)
                    VALUES (:nome, :descrizione, :prezzo, :id_categoria, :disp)";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(':nome', $nome);
            $stm->bindValue(':descrizione', $descrizione);
            $stm->bindValue(':prezzo', $prezzo);
            $stm->bindValue(':id_categoria', $id_categoria);
            $stm->bindValue(':disp', $disponibile, PDO::PARAM_INT);
            $stm->execute();

            $id_prodotto = $pdo->lastInsertId();
        }

        // inserimento allergeni
        if (!empty($_POST['allergeni'])) {
            $stm = $pdo->prepare(
                "INSERT INTO prodotti_allergeni (id_prodotto, id_allergene)
                 VALUES (:p, :a)"
            );

            foreach ($_POST['allergeni'] as $a) {
                $stm->execute([
                    ':p' => $id_prodotto,
                    ':a' => $a
                ]);
            }
        }

        // inserimento caratteristiche
        if (!empty($_POST['caratteristiche'])) {
            $stm = $pdo->prepare(
                "INSERT INTO prodotti_caratteristiche (id_prodotto, id_caratteristica)
                 VALUES (:p, :c)"
            );

            foreach ($_POST['caratteristiche'] as $c) {
                $stm->execute([
                    ':p' => $id_prodotto,
                    ':c' => $c
                ]);
            }
        }

        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        $msgErrore["salvataggio"] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modifica ? 'Modifica prodotto' : 'Nuovo prodotto' ?></title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<!-- HEADER -->
<div class="w3-container w3-teal w3-padding">
    <h2><i class="fa fa-pizza-slice"></i> <?= $modifica ? 'Modifica prodotto' : 'Nuovo prodotto' ?></h2>
</div>

<!-- FORM -->
<div class="w3-container w3-card w3-padding w3-margin">

<form method="post">

    <label>Nome</label>
    <input class="w3-input w3-border" type="text" name="nome" required value="<?= htmlspecialchars($nome) ?>">

    <label>Descrizione</label>
    <textarea class="w3-input w3-border" name="descrizione"><?= htmlspecialchars($descrizione) ?></textarea>

    <label>Prezzo (€)</label>
    <input class="w3-input w3-border" type="number" step="0.01" name="prezzo" required value="<?= $prezzo ?>">

    <label>Categoria</label>
    <select class="w3-select w3-border" name="id_categoria" required>
        <?php foreach($categorie as $c): ?>
            <option value="<?= $c['id_categoria'] ?>" <?= $c['id_categoria']==$id_categoria?'selected':'' ?>>
                <?= htmlspecialchars($c['nome']) ?>
            </option>
        <?php endforeach ?>
    </select>

    <p>
        <label>
            <input class="w3-check" type="checkbox" name="disponibile" <?= $disponibile?'checked':'' ?>>
            Disponibile
        </label>
    </p>

    <h4>Allergeni</h4>
    <?php foreach($allergeni as $a): ?>
        <label>
            <input class="w3-check" type="checkbox" name="allergeni[]"
                   value="<?= $a['id_allergene'] ?>"
                   <?= in_array($a['id_allergene'],$allergeni_sel)?'checked':'' ?>>
            <?= htmlspecialchars($a['nome']) ?>
        </label><br>
    <?php endforeach ?>

    <h4>Caratteristiche</h4>
    <?php foreach($caratteristiche as $c): ?>
        <label>
            <input class="w3-check" type="checkbox" name="caratteristiche[]"
                   value="<?= $c['id_caratteristica'] ?>"
                   <?= in_array($c['id_caratteristica'],$car_sel)?'checked':'' ?>>
            <?= htmlspecialchars($c['nome']) ?>
        </label><br>
    <?php endforeach ?>

    <br>
    <button class="w3-button w3-green"><i class="fa fa-save"></i> Salva</button>
    <a href="index.php" class="w3-button w3-grey">Annulla</a>

</form>
</div>

</body>
</html>
