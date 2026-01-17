<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Opera</title>
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
            
            <div class="w3-card-4 w3-white w3-round-large">
                <header class="w3-container w3-teal">
                    <h2><i class="fa fa-plus-circle"></i> Inserisci Nuova Opera</h2>
                </header>

                <form method="post" action="conferma.php" class="w3-container w3-padding">

                    <!-- Descrizione -->
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-align-left"></i> Descrizione</b></label>
                        <input class="w3-input w3-border w3-round" type="text" name="descrizione" placeholder="Inserisci la descrizione dell'opera" required>
                    </div>

                    <!-- Provincia -->
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-map-marker"></i> Provincia</b></label>
                        <select class="w3-select w3-border w3-round" name="provincia" required>
                            <option value="" disabled selected>Seleziona una provincia</option>
                            <option value="BG">Bergamo</option>
                            <option value="MI">Milano</option>
                            <option value="AN">Ancona</option>
                            <option value="RM">Roma</option>
                            <option value="NA">Napoli</option>
                        </select>
                    </div>

                    <!-- Data -->
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-calendar"></i> Data di Vendita</b></label>
                        <input class="w3-input w3-border w3-round" type="date" name="data" max="<?= date("Y-m-d");?>" required>
                    </div>

                    <!-- Prezzo -->
                    <div class="w3-margin-bottom">
                        <label class="w3-text-teal"><b><i class="fa fa-euro"></i> Prezzo (€)</b></label>
                        <input class="w3-input w3-border w3-round" type="number" name="prezzo" step="0.01" min="0" placeholder="0.00" required>
                    </div>

                    <!-- Buttons -->
                    <div class="w3-margin-top w3-margin-bottom">
                        <button type="submit" class="w3-button w3-green w3-round w3-block">
                            <i class="fa fa-check"></i> Conferma Inserimento
                        </button>
                        <a href="index.php" class="w3-button w3-grey w3-round w3-block w3-margin-top">
                            <i class="fa fa-arrow-left"></i> Torna Indietro
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="w3-container w3-teal w3-padding-16 w3-margin-top">
        <p class="w3-center">© 2025 Gestione Opere d'Arte</p>
    </footer>

</body>
</html>