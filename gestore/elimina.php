<?php
require 'connessione.php';

$id = $_GET['id'] ?? null;
$tabella = $_GET['tabella'] ?? null;
$conferma = $_GET['conferma'] ?? null;

$successo = false;
$errore = "";
$datiElemento = null;

$id_tabelle = [
    'prodotti' => ['id' => 'id_prodotto'],
    'categorie' => ['id' => 'id_categoria'],
    'allergeni' => ['id' => 'id_allergene'],
    'caratteristiche' => ['id' => 'id_caratteristica']
];

if (!$id || !is_numeric($id) || !array_key_exists($tabella, $id_tabelle)) {
    $errore = "Parametri non validi o tabella non autorizzata.";
} else {
    $pdo = new PDO($connString, $connUser, $connPass);
    $id_tabella = $id_tabelle[$tabella]['id'];

    try {
        if ($conferma === 'si') {
            // eliminazione
            $sql = "DELETE FROM $tabella WHERE $id_tabella = :id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':id', $id, PDO::PARAM_INT);
            $stm->execute();
            $successo = true;
        } else {
            // riepilogo
            $sql = "SELECT * FROM $tabella WHERE $id_tabella = :id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':id', $id, PDO::PARAM_INT);
            $stm->execute();
            $datiElemento = $stm->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $errore = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimina Elemento | Pizzeria da Paggi</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
        .card-confirmation {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>

    <header class="w3-container bg-pizza w3-padding-16">
        <h2 class="w3-xlarge w3-margin-0"><i class="fa fa-trash-can"></i> Gestione Eliminazione</h2>
    </header>

    <div class="w3-container w3-padding-64">
        <div class="w3-content card-confirmation">
            
            <?php if($successo): ?>
                <div class="w3-panel w3-green w3-round-large w3-card-4 w3-padding-16">
                    <h3><i class="fa fa-check-circle"></i> Successo!</h3>
                    <p>L'elemento è stato eliminato definitivamente dalla tabella <strong><?= htmlspecialchars($tabella) ?></strong>.</p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="<?= ($tabella === 'prodotti') ? 'index.php' : $tabella . '.php' ?>" class="w3-button bg-basil w3-round-large w3-large">
                        <i class="fa fa-arrow-left"></i> Torna alla Lista
                    </a>
                </div>
                
            <?php elseif($errore != "" || !$datiElemento): ?>
                <div class="w3-panel w3-red w3-round-large w3-card-4 w3-padding-16">
                    <h3><i class="fa fa-times-circle"></i> Errore!</h3>
                    <p><?= $errore ?: "Elemento non trovato." ?></p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="javascript:history.back()" class="w3-button bg-pizza w3-round-large">Torna Indietro</a>
                </div>
                
            <?php else: ?>
                <div class="w3-card-4 bg-cream w3-round-large">
                    <header class="w3-container bg-pizza w3-round-large" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
                        <h3>Conferma Eliminazione</h3>
                    </header>
                    <div class="w3-container w3-padding-24">
                        <p class="w3-large">Sei sicuro di voler eliminare questo elemento? L'operazione non è reversibile.</p>
                        <hr style="border-top: 1px solid #ddd">
                        <div class="w3-padding">
                            <p><strong><i class="fa fa-table text-pizza"></i> Tabella:</strong> <?= ucfirst($tabella) ?></p>
                            <p><strong><i class="fa fa-tag text-pizza"></i> Nome:</strong> <?= htmlspecialchars($datiElemento['nome']) ?></p>
                            <p><strong><i class="fa fa-info-circle text-pizza"></i> ID:</strong> <?= htmlspecialchars($id) ?></p>
                        </div>
                    </div>
                </div>

                <div class="w3-margin-top w3-center">
                    <div class="w3-bar">
                        <a href="elimina.php?tabella=<?= $tabella ?>&id=<?= $id ?>&conferma=si" class="w3-button w3-red w3-round-large w3-margin-right">
                            <i class="fa fa-trash"></i> Sì, Elimina Definitivamente
                        </a>
                        <a href="javascript:history.back()" class="w3-button w3-white w3-border w3-round-large">
                            <i class="fa fa-times"></i> Annulla
                        </a>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </div>

    <footer class="w3-container bg-pizza w3-padding-16 w3-margin-top w3-center">
        <p>© 2026 Pizzeria da Paggi - Sistema Gestionale</p>
    </footer>
</body>
</html>