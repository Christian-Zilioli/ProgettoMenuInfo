<?php

$rows = 0;
$errore = "";

try{

    require "connessione.php";

    $desc = $_POST["descrizione"];
    $prov = $_POST["provincia"];
    $data = $_POST["data"];
    $prezzo = $_POST["prezzo"];

    $pdo = new PDO($connString, $connUser, $connPass);
    $sql = "INSERT INTO opera (descrizione, provincia, data, prezzo) VALUES (:d, :p, :dv, :pr)";
    $stm = $pdo->prepare($sql);
    $stm->bindParam("d",$desc);
    $stm->bindParam("p",$prov);
    $stm->bindParam("dv",$data);
    $stm->bindParam("pr",$prezzo);

    $stm->execute();
    $rows = $stm->rowCount();
}
catch(PDOException $e){
    $errore = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Inserimento</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="w3-light-grey">

    <!-- Header -->
    <header class="w3-container w3-teal w3-padding-16">
        <h1><i class="fa fa-paint-brush"></i> Archivio Opere d'Arte</h1>
    </header>

    <!-- Main Content -->
    <div class="w3-container w3-padding-64">
        <div class="w3-content" style="max-width:600px">
            
            <?php if($rows == 0 || $errore != ""):?>
                <div class="w3-panel w3-red w3-round-large w3-card-4">
                    <h3><i class="fa fa-times-circle"></i> Errore!</h3>
                    <p>Errore nell'inserimento dei dati nel database.</p>
                    <?php if($errore != ""): ?>
                        <p><small><?= $errore ?></small></p>
                    <?php endif ?>
                </div>
            <?php else:?>
                <div class="w3-panel w3-green w3-round-large w3-card-4">
                    <h3><i class="fa fa-check-circle"></i> Successo!</h3>
                    <p>I dati sono stati inseriti correttamente nel database.</p>
                </div>

                <!-- Riepilogo Dati Inseriti -->
                <div class="w3-card-4 w3-white w3-round-large w3-margin-top">
                    <header class="w3-container w3-teal">
                        <h3>Riepilogo Opera Inserita</h3>
                    </header>
                    <div class="w3-container w3-padding">
                        <p><strong><i class="fa fa-align-left"></i> Descrizione:</strong> <?= htmlspecialchars($desc) ?></p>
                        <p><strong><i class="fa fa-map-marker"></i> Provincia:</strong> <?= htmlspecialchars($prov) ?></p>
                        <p><strong><i class="fa fa-calendar"></i> Data di Vendita:</strong> <?= htmlspecialchars($data) ?></p>
                        <p><strong><i class="fa fa-euro"></i> Prezzo:</strong> € <?= number_format($prezzo, 2, ',', '.') ?></p>
                    </div>
                </div>
            <?php endif?>

            <!-- Buttons -->
            <div class="w3-margin-top w3-center">
                <a href="inserisci.php" class="w3-button w3-blue w3-round w3-margin-right">
                    <i class="fa fa-plus"></i> Inserisci Altra Opera
                </a>
                <a href="index.php" class="w3-button w3-teal w3-round">
                    <i class="fa fa-list"></i> Torna all'Elenco
                </a>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top">
        <p class="w3-center">© 2025 Gestione Opere d'Arte</p>
    </footer>

</body>
</html>