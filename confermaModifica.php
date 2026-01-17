<?php
try {
    require "connessione.php";

    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $pdo = new PDO($connString, $connUser, $connPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (isset($_GET["modifica"]) && $_GET["modifica"] == "si") {
            
            $desc = $_POST["descrizione"] ?? "";
            $prov = $_POST["provincia"] ?? "";
            $data = $_POST["data"] ?? "";
            $prezzo = $_POST["prezzo"] ?? "";

            // Query di UPDATE
            $sqlModify = "UPDATE opera SET descrizione = :desc, provincia = :prov, data = :da, prezzo = :pre WHERE id = :cod";
            $stmModify = $pdo->prepare($sqlModify);
            $stmModify->bindParam(":cod", $id);
            $stmModify->bindParam(":desc", $desc);
            $stmModify->bindParam(":prov", $prov);
            $stmModify->bindParam(":da", $data);
            $stmModify->bindParam(":pre", $prezzo);
            $stmModify->execute();

            $operaModificata = true;
        } else {
            // Recupera dati per il form
            $sql = "SELECT * FROM opera WHERE id = :cod";
            $stm = $pdo->prepare($sql);
            $stm->bindParam(":cod", $id);
            $stm->execute();
            $ris = $stm->fetchAll(PDO::FETCH_ASSOC);
            $rows = $stm->rowCount();
            $errore = "";

            if ($rows > 0) {
                $desc = $ris[0]["descrizione"];
                $prov = $ris[0]["provincia"];
                $data = $ris[0]["data"];
                $prezzo = $ris[0]["prezzo"];
            }
            $operaEliminata = false;
        }
    } else {
        throw new Exception("ID non specificato.");
    }
} catch (PDOException $e) {
    $errore = $e->getMessage();
} catch (Exception $e) {
    $errore = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Opera</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <header class="w3-container w3-teal w3-padding-16">
        <h1><i class="fa fa-paint-brush"></i> Archivio Opere d'Arte</h1>
    </header>
        <style>
        .footer{
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
        }
        
    </style>
    <div class="w3-container w3-padding-64">
        <div class="w3-content" style="max-width:600px">
            <?php if (isset($operaModificata) && $operaModificata): ?>
                <div class="w3-panel w3-green w3-round-large w3-card-4">
                    <h3><i class="fa fa-check-circle"></i> Successo!</h3>
                    <p>Opera modificata con successo.</p>
                </div>
                <div class="w3-margin-top w3-center">
                    <a href="index.php" class="w3-button w3-teal w3-round">
                        <i class="fa fa-list"> Torna All'Elenco</i>
                    </a>
                </div>
            <?php elseif ($rows == 0 || $errore != ""): ?>
                <div class="w3-panel w3-red w3-round-large w3-card-4">
                    <h3><i class="fa fa-times-circle"></i> Errore!</h3>
                    <p>Nessuna opera trovata.</p>
                    <?php if ($errore != ""): ?>
                        <p><small><?= htmlspecialchars($errore) ?></small></p>
                    <?php endif ?>
                </div>
                <a href="index.php" class="w3-button w3-grey w3-round w3-block w3-margin-top">
                    <i class="fa fa-arrow-left"> Torna All'Elenco</i>
                </a>
            <?php else: ?>
                    <div class="w3-card-4 w3-white w3-round-large">
                <form method="post" action="confermaModifica.php?id=<?= $id ?>&modifica=si" class="w3-container w3-padding">
                        <header class="w3-container w3-teal">
                            <h2><i class="fa fa-pencil"></i> Modifica Opera</h2>
                        </header>
                        
                            <h1 class="w3-text-teal"><b><i class="fa fa-align-left"></i> Descrizione</b></h1>
                            <input class="w3-input w3-border w3-round" type="text" name="descrizione" required
                                value="<?= htmlspecialchars($desc) ?>">
                    
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-map-marker"></i> Provincia</b></label>
                        <select class="w3-select w3-border w3-round" name="provincia" required>
                            <option value="<?= htmlspecialchars($prov) ?>" selected><?= htmlspecialchars($prov) ?></option>
                            <option value="BG">Bergamo</option>
                            <option value="MI">Milano</option>
                            <option value="AN">Ancona</option>
                            <option value="RM">Roma</option>
                            <option value="NA">Napoli</option>
                        </select>
                    </div>
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-calendar"></i> Data di Vendita</b></label>
                        <input class="w3-input w3-border w3-round" type="date" name="data" max="<?= date("Y-m-d"); ?>" required
                            value="<?= htmlspecialchars($data) ?>">
                    </div>
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-euro"></i> Prezzo (€)</b></label>
                        <input class="w3-input w3-border w3-round" type="number" name="prezzo" step="0.01" min="0" required
                            value="<?= htmlspecialchars($prezzo) ?>">
                    </div>
                    <div class="w3-margin-top w3-margin-bottom">
                        <button type="submit" class="w3-button w3-green w3-round w3-block">
                            <i class="fa fa-check"> Conferma Modifica</i>
                        </button>
                        <a href="index.php" class="w3-button w3-grey w3-round w3-block w3-margin-top">
                            <i class="fa fa-arrow-left"> Torna All'Elenco</i>
                        </a>
                    </div>
                    </div>
                </form>
            </div>
        <?php endif ?>
    </div>
    </div>

    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top footer">
        <p class="w3-center">© 2025 Gestione Opere d'Arte</p>
    </footer>
    </body>

</html>