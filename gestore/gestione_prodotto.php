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
    
    $img = $prodotto['immagine']; 
    // if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {
    //     $img = time() . "_" . $_FILES['immagine']['name'];
    //     move_uploaded_file($_FILES['immagine']['tmp_name'], "uploads/" . $img);
    // }

    try {
        $pdo->beginTransaction();

        $dati = [
            ':n' => $_POST['nome'],
            ':d' => $_POST['descrizione'],
            ':p' => $_POST['prezzo'],
            ':disp' => isset($_POST['disponibile']) ? 1 : 0,
            ':img' => $img,
            ':cat' => $_POST['id_categoria'] ?: null
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
        $pdo->rollBack();
        $msgErrore = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?= $id ? 'Modifica' : 'Nuovo' ?> Prodotto | Pizzeria da Paggi</title>
    <style>
        body {
            background-color: #f7f3ee;
            color: #333;
        }
        .bg-pizza {
            background-color: #b22222 !important;
            color: white;
        }
        .bg-basil {
            background-color: #2e7d32 !important;
            color: white;
        }
        .bg-cream {
            background-color: #fffaf2;
        }
        .text-pizza {
            color: #b22222;
        }
        .form-container {
            max-width: 800px;
            margin: 20px auto;
        }
        .input-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="w3-container bg-pizza w3-padding-16">
        <h2 class="w3-xlarge w3-margin-0">
            <i class="fa <?= $id ? 'fa-edit' : 'fa-plus' ?>"></i>
            <?= $id ? 'Modifica Prodotto' : 'Aggiungi Nuovo Prodotto' ?>
        </h2>
    </div>

    <div class="w3-container form-container">
        <form method="POST" enctype="multipart/form-data" class="w3-container bg-cream w3-padding-24 w3-card-4 w3-round-large">
            
            <div class="input-group">
                <label class="text-pizza"><b>Nome Prodotto</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="nome" value="<?= htmlspecialchars($prodotto['nome']) ?>" required placeholder="Es: Margherita">
            </div>
            
            <div class="input-group">
                <label class="text-pizza"><b>Descrizione</b></label>
                <textarea class="w3-input w3-border w3-round" name="descrizione" rows="3" placeholder="Ingredienti e dettagli..."><?= htmlspecialchars($prodotto['descrizione']) ?></textarea>
            </div>

            <div class="w3-row-padding" style="margin:0 -16px">
                <div class="w3-half input-group">
                    <label class="text-pizza"><b>Prezzo (€)</b></label>
                    <input class="w3-input w3-border w3-round" type="number" step="0.01" name="prezzo" value="<?= $prodotto['prezzo'] ?>" required>
                </div>
                <div class="w3-half input-group">
                    <label class="text-pizza"><b>Categoria</b></label>
                    <select class="w3-select w3-border w3-round" name="id_categoria">
                        <option value="">Nessuna categoria</option>
                        <?php foreach($categorie as $c): ?>
                            <option value="<?= $c['id_categoria'] ?>" <?= $c['id_categoria'] == $prodotto['id_categoria'] ? 'selected' : '' ?>><?= $c['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- <div class="w3-row-padding w3-margin-top" style="margin:0 -16px">
                <div class="w3-half input-group">
                    <label class="text-pizza"><b>Immagine Prodotto</b></label>
                    <input type="file" name="immagine" class="w3-input w3-border w3-round w3-white">
                    <?php if($prodotto['immagine']): ?>
                        <p class="w3-small w3-text-grey">Attuale: <span class="w3-tag w3-light-grey"><?= $prodotto['immagine'] ?></span></p>
                    <?php endif; ?>
                </div>
                <div class="w3-half w3-padding-large">
                    <div class="w3-padding-top">
                        <input class="w3-check" type="checkbox" name="disponibile" <?= $prodotto['disponibile'] ? 'checked' : '' ?>>
                        <label><b>Disponibile nel Menù</b></label>
                    </div>
                </div>
            </div> -->

            <hr style="border-top: 1px solid #ddd">

            <div class="w3-row-padding">
                <div class="w3-half">
                    <h4 class="text-pizza"><i class="fa fa-exclamation-triangle"></i> Allergeni</h4>
                    <div class="w3-white w3-padding w3-border w3-round" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($tutti_allergeni as $a): ?>
                            <label class="w3-show-block">
                                <input class="w3-check" type="checkbox" name="allergeni[]" value="<?= $a['id_allergene'] ?>" <?= in_array($a['id_allergene'], $selAllergeni) ? 'checked' : '' ?>> <?= $a['nome'] ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="w3-half">
                    <h4 class="text-pizza"><i class="fa fa-leaf"></i> Caratteristiche</h4>
                    <div class="w3-white w3-padding w3-border w3-round" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($tutte_caratteristiche as $c): ?>
                            <label class="w3-show-block">
                                <input class="w3-check" type="checkbox" name="caratteristiche[]" value="<?= $c['id_caratteristica'] ?>" <?= in_array($c['id_caratteristica'], $selCaratteristiche) ? 'checked' : '' ?>> <?= $c['nome'] ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="w3-margin-top w3-padding-top w3-center">
                <hr>
                <a href="index.php" class="w3-button w3-white w3-border w3-round-large" style="width: 150px;">Annulla</a>
                <button type="submit" class="w3-button bg-basil w3-round-large" style="width: 150px;">
                    <i class="fa fa-save"></i> Salva
                </button>
            </div>
        </form>
    </div>

    <footer class="w3-container w3-center w3-padding-32 w3-opacity w3-small">
        © 2026 Pizzeria da Paggi - Sistema di Gestione
    </footer>

</body>
</html>