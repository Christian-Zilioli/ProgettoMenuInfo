<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);

$id = $_GET['id'] ?? null;
$msgErrore = "";

//dati per form
try {
    $categorie = $pdo->query("SELECT * FROM categorie ORDER BY ordine")->fetchAll(PDO::FETCH_ASSOC);
    $tutti_allergeni = $pdo->query("SELECT * FROM allergeni")->fetchAll(PDO::FETCH_ASSOC);
    $tutte_caratteristiche = $pdo->query("SELECT * FROM caratteristiche")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $msgErrore = $e->getMessage(); 
}

$prodotto = ['nome'=>'', 'descrizione'=>'', 'prezzo'=>'', 'id_categoria'=>'', 'disponibile'=>1, 'immagine'=>''];
$selAllergeni = [];
$selCaratteristiche = [];

//dati prodotto da modificare
if ($id) {
    try {
        $stm = $pdo->prepare("SELECT * FROM prodotti WHERE id_prodotto = ?");
        $stm->execute([$id]);
        $prodotto = $stm->fetch(PDO::FETCH_ASSOC);
        
        $selAllergeni = $pdo->prepare("SELECT id_allergene FROM prodotti_allergeni WHERE id_prodotto = ?");
        $selAllergeni->execute([$id]);
        $selAllergeni = $selAllergeni->fetchAll(PDO::FETCH_COLUMN);

        $selCaratteristiche = $pdo->prepare("SELECT id_caratteristica FROM prodotti_caratteristiche WHERE id_prodotto = ?");
        $selCaratteristiche->execute([$id]);
        $selCaratteristiche = $selCaratteristiche->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) { 
        $msgErrore = $e->getMessage(); 
    }
}
//salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Gestione dell'immagine: se non ne viene caricata una nuova, tengo quella vecchia
    $img = $prodotto['immagine']; 
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {
        $img = time() . "_" . $_FILES['immagine']['name']; // Rinomino per evitare duplicati
        move_uploaded_file($_FILES['immagine']['tmp_name'], "uploads/" . $img);
    }

    try {
        $pdo->beginTransaction();

        $dati = [
            ':n' => $_POST['nome'],
            ':d' => $_POST['descrizione'],
            ':p' => $_POST['prezzo'],
            ':disp' => isset($_POST['disponibile']) ? 1 : 0,
            ':img' => $img,
            ':cat' => $_POST['id_categoria'] ?: null // Se vuoto, salva come NULL
        ];

        if ($id) {
            //modifica
            $sql = "UPDATE prodotti 
                SET nome=:n, 
                descrizione=:d, 
                prezzo=:p, 
                disponibile=:disp, 
                immagine=:img, 
                id_categoria=:cat WHERE id_prodotto=:id";

            $dati[':id'] = $id;
        } else {
            // inserimento
            $sql = "INSERT INTO prodotti (nome, descrizione, prezzo, disponibile, immagine, id_categoria) 
                VALUES (:n, :d, :p, :disp, :img, :cat)";
        }
        
        $pdo->prepare($sql)->execute($dati);
        if (!$id) $id = $pdo->lastInsertId();
        
        //gestione tabelle
        $pdo->prepare("DELETE FROM prodotti_allergeni WHERE id_prodotto = ?")->execute([$id]);
        if (!empty($_POST['allergeni'])) {
            $stm = $pdo->prepare("INSERT INTO prodotti_allergeni (id_prodotto, id_allergene) VALUES (?, ?)");
            foreach ($_POST['allergeni'] as $aid) {
                $stm->execute([$id, $aid]);
            }
        }

        $pdo->prepare("DELETE FROM prodotti_caratteristiche WHERE id_prodotto = ?")->execute([$id]);
        if (!empty($_POST['caratteristiche'])) {
            $stm = $pdo->prepare("INSERT INTO prodotti_caratteristiche (id_prodotto, id_caratteristica) VALUES (?, ?)");
            foreach ($_POST['caratteristiche'] as $cid) {
                $stm->execute([$id, $cid]);
            }
        }

        $pdo->commit();
        header("Location: index.php");
    } catch (Exception $e) {
        // In caso di errore, annullo tutto ciò che è stato fatto dall'inizio della transazione
        $pdo->rollBack();
        $msgErrore = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title><?= $id ? 'Modifica' : 'Nuovo' ?> Prodotto</title>
</head>
<body class="w3-light-grey">
    <div class="w3-container w3-teal"><h2><?= $id ? 'Modifica Prodotto' : 'Aggiungi Prodotto' ?></h2></div>
    
    <form method="POST" enctype="multipart/form-data" class="w3-container w3-white w3-margin w3-padding w3-card-4">
        <label>Nome</label>
        <input class="w3-input w3-border" type="text" name="nome" value="<?= htmlspecialchars($prodotto['nome']) ?>" required>
        
        <label>Descrizione</label>
        <textarea class="w3-input w3-border" name="descrizione"><?= htmlspecialchars($prodotto['descrizione']) ?></textarea>

        <div class="w3-row-section">
            <div class="w3-half w3-padding-small">
                <label>Prezzo (€)</label>
                <input class="w3-input w3-border" type="number" step="0.01" name="prezzo" value="<?= $prodotto['prezzo'] ?>" required>
            </div>
            <div class="w3-half w3-padding-small">
                <label>Categoria</label>
                <select class="w3-select w3-border" name="id_categoria">
                    <option value="">Nessuna</option>
                    <?php foreach($categorie as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>" <?= $c['id_categoria'] == $prodotto['id_categoria'] ? 'selected' : '' ?>><?= $c['nome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="w3-margin-top">
            <label>Immagine</label>
            <input type="file" name="immagine" class="w3-input">
            <?php if($prodotto['immagine']): ?><p><small>Attuale: <?= $prodotto['immagine'] ?></small></p><?php endif; ?>
        </div>

        <div class="w3-margin-top">
            <input class="w3-check" type="checkbox" name="disponibile" <?= $prodotto['disponibile'] ? 'checked' : '' ?>>
            <label>Disponibile</label>
        </div>

        <hr>
        <div class="w3-row">
            <div class="w3-half">
                <h4>Allergeni</h4>
                <?php foreach($tutti_allergeni as $a): ?>
                    <input type="checkbox" name="allergeni[]" value="<?= $a['id_allergene'] ?>" <?= in_array($a['id_allergene'], $selAllergeni) ? 'checked' : '' ?>> <?= $a['nome'] ?><br>
                <?php endforeach; ?>
            </div>
            <div class="w3-half">
                <h4>Caratteristiche</h4>
                <?php foreach($tutte_caratteristiche as $c): ?>
                    <input type="checkbox" name="caratteristiche[]" value="<?= $c['id_caratteristica'] ?>" <?= in_array($c['id_caratteristica'], $selCaratteristiche) ? 'checked' : '' ?>> <?= $c['nome'] ?><br>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="w3-margin-top">
            <button type="submit" class="w3-button w3-teal">Salva Prodotto</button>
            <a href="index.php" class="w3-button w3-red">Annulla</a>
        </div>
    </form>
</body>
</html>