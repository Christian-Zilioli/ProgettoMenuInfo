<?php

try{    

    require "connessione.php";

    if (isset($_GET["id"])){

        $id = $_GET["id"];
        $pdo = new PDO($connString, $connUser, $connPass);
        
        if (isset($_GET["conferma"]) && $_GET["conferma"] == "si"){
            $sqlDelete = "DELETE FROM opera WHERE id=:cod";
            $stmDelete = $pdo->prepare($sqlDelete);
            $stmDelete->bindParam("cod", $id);
            $stmDelete->execute();
            
            $operaEliminata = true;
        } 
        else 
        {
            
            $sql = "SELECT * FROM opera WHERE id=:cod";
            $stm = $pdo->prepare($sql);
            $stm->bindParam("cod",$id);
            $stm->execute();
            $ris = $stm->fetchAll(PDO::FETCH_ASSOC);
            $rows = $stm->rowCount();

            $errore = "";
            if ($rows > 0 ){
                $desc = $ris[0]["descrizione"];
                $prov = $ris[0]["provincia"];
                $data = $ris[0]["data"];
                $prezzo = $ris[0]["prezzo"];
            }
            
            $operaEliminata = false;
        }
           
    }
    else{
        throw new Exception();
    }
}
catch(PDOException $e){
     $errore = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimina</title>
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
            
            <?php if(isset($operaEliminata) && $operaEliminata): ?>
                <!-- Messaggio di successo -->
                <div class="w3-panel w3-green w3-round-large w3-card-4">
                    <h3><i class="fa fa-check-circle"></i> Successo!</h3>
                    <p>Opera eliminata con successo.</p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="index.php" class="w3-button w3-teal w3-round">
                        <i class="fa fa-list"></i> Torna all'Elenco
                    </a>
                </div>
                
            <?php elseif($rows == 0 || $errore != ""):?>
                <div class="w3-panel w3-red w3-round-large w3-card-4">
                    <h3><i class="fa fa-times-circle"></i> Errore!</h3>
                    <p>Nessuna opera trovata.</p>
                    <?php if($errore != ""): ?>
                        <p><small><?= $errore ?></small></p>
                    <?php endif ?>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="index.php" class="w3-button w3-teal w3-round">
                        <i class="fa fa-list"></i> Torna all'Elenco
                    </a>
                </div>
                
            <?php else:?>
                <!-- Riepilogo Dati Inseriti -->
                <div class="w3-card-4 w3-white w3-round-large w3-margin-top">
                    <header class="w3-container w3-red">
                        <h3>Riepilogo Opera Da Eliminare</h3>
                    </header>
                    <div class="w3-container w3-padding">
                        <p><strong><i class="fa fa-align-left"></i> Descrizione:</strong> <?= htmlspecialchars($desc) ?></p>
                        <p><strong><i class="fa fa-map-marker"></i> Provincia:</strong> <?= htmlspecialchars($prov) ?></p>
                        <p><strong><i class="fa fa-calendar"></i> Data di Vendita:</strong> <?= htmlspecialchars($data) ?></p>
                        <p><strong><i class="fa fa-euro"></i> Prezzo:</strong> € <?= number_format($prezzo, 2, ',', '.') ?></p>
                    </div>
                </div>

                <div class="w3-margin-top w3-center">
                    <a href="confermaElimina.php?id=<?= $id ?>&conferma=si" class="w3-button w3-red w3-round w3-margin-right">
                        <i class="fa fa-minus"></i> Conferma Elimina
                    </a>
                    <a href="index.php" class="w3-button w3-teal w3-round">
                        <i class="fa fa-list"></i> Torna all'Elenco
                    </a>
                </div>
            <?php endif?>

        </div>
    </div>

    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top">
        <p class="w3-center">© 2025 Gestione Opere d'Arte</p>
    </footer>
</body>
</html>