<?php
require 'connessione.php';
$pdo = new PDO($connString, $connUser, $connPass);
 
$pag_numero = 0;
$msgErrore = [];
 
// dati base
try {
    $categorie = $pdo->query("SELECT * FROM categorie WHERE visibile = true ORDER BY ordine")->fetchAll(PDO::FETCH_ASSOC);
    $allergeni = $pdo->query("SELECT * FROM allergeni")->fetchAll(PDO::FETCH_ASSOC);
    $caratteristiche = $pdo->query("SELECT * FROM caratteristiche")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msgErrore["lettura"] = $e->getMessage();
}

// Visualizzo la pagina scelta dall'utente.
if (isset($_GET['pag']) == true && is_numeric($_GET['pag']) == true && intval($_GET['pag']) > 0 && intval($_GET['pag']) <= count($categorie)) {
    $pag_numero = intval($_GET['pag']);
}
if ($pag_numero === 0 && count($categorie) > 0) {
    $pag_numero = $categorie[0]['id_categoria'];
}
 
//prodotti
try {
    $parametri = [':categoria' => $pag_numero];
 
    $sql = "SELECT
            p.id_prodotto,
            p.nome,
            p.descrizione,
            p.prezzo,
            p.immagine,

            GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') AS allergeni,
            GROUP_CONCAT(DISTINCT c.nome SEPARATOR ', ') AS caratteristiche

        FROM prodotti p

        LEFT JOIN prodotti_allergeni pa ON p.id_prodotto = pa.id_prodotto
        LEFT JOIN allergeni a ON pa.id_allergene = a.id_allergene

        LEFT JOIN prodotti_caratteristiche pc ON p.id_prodotto = pc.id_prodotto
        LEFT JOIN caratteristiche c ON pc.id_caratteristica = c.id_caratteristica

        WHERE p.id_categoria = :categoria
        AND p.disponibile = TRUE
        ";
 
    if (!empty($_GET['no_allergeni'])) {
        $placeholders = [];
        foreach ($_GET['no_allergeni'] as $i => $id) {
            $key = ":allergene$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }
        $sql .= " AND p.id_prodotto NOT IN (
            SELECT pa.id_prodotto FROM prodotti_allergeni pa 
            WHERE pa.id_allergene IN (" . implode(',', $placeholders) . ")
        )";
    }
 
    if (!empty($_GET['caratteristiche'])) {
        $placeholders = [];
        foreach ($_GET['caratteristiche'] as $i => $id) {
            $key = ":car$i";
            $placeholders[] = $key;
            $parametri[$key] = $id;
        }
        $sql .= " AND p.id_prodotto IN (
            SELECT pc.id_prodotto FROM prodotti_caratteristiche pc 
            WHERE pc.id_caratteristica IN (" . implode(',', $placeholders) . ")
        )";
    }
    $sql .= " GROUP BY p.id_prodotto";
   
    $stm = $pdo->prepare($sql);
    $stm->execute($parametri);
    $numProdotti = $stm->rowCount();
 
    if ($numProdotti == 0) {
        $msgErrore["prodotti"] = 'Nessun prodotto trovato.';
        $prodotti = [];
    } else {
        $prodotti = $stm->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $msgErrore["prodotti"] = $e->getMessage();
}
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizzeria Da Paggi | Il Nostro Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;600&display=swap%22">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root { --primary-color: #e67e22; --dark-bg: #1a1a1a; }
        body { font-family: 'Montserrat', sans-serif; background-color: #fcfaf7; }
        .serif-font { font-family: 'Playfair Display', serif; }
       
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
                        url('https://images.unsplash.com/photo-1513104890138-7c749659a591?q=80&w=2070&auto=format&fit=crop%27');
            background-size: cover; background-position: center; background-attachment: fixed; height: 50vh;
        }
 
 
 
        .category-link { position: relative; transition: all 0.3s ease; }
        .category-link::after {
            content: ''; position: absolute; bottom: -5px; left: 50%; width: 0; height: 2px;
            background: var(--primary-color); transition: all 0.3s ease; transform: translateX(-50%);
        }
        .category-link.active::after, .category-link:hover::after { width: 100%; }
 
        .product-card { border-bottom: 1px dashed #ddd; padding-bottom: 1.5rem; transition: transform 0.3s ease; }
        .product-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="text-gray-800">
 
    <header class="hero-section flex flex-col justify-center items-center text-white text-center px-4">
        <h1 class="serif-font text-5xl md:text-7xl mb-4">Il Nostro Menu</h1>
        <div class="w-24 h-1 bg-[#e67e22] mb-6"></div>
        <p class="text-lg md:text-xl max-w-2xl font-light tracking-wide">Tradizione e passione in ogni fetta.</p>
    </header>
 
    <main class="max-w-6xl mx-auto px-4 -mt-10">
       
        <?php if (!empty($msgErrore)): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-8 shadow-lg text-center">
                <i class="fa fa-exclamation-triangle mr-2"></i> 
                <?php foreach ($msgErrore as $query => $err): ?>
                    <p><?= $query . ' - ' . $err; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
 
        <!-- filtri -->
        <section class="bg-white rounded-lg shadow-xl p-8 mb-12 border-t-4 border-[#e67e22]">
            <form method="get" class="space-y-8">
                <input type="hidden" name="pag" value="<?= $pag_numero ?>">
               
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Escludi Allergeni</h4>
                        <div class="flex flex-wrap gap-4">
                            <?php foreach($allergeni as $a): ?>
                                <label class="flex items-center space-x-2 group cursor-pointer">
                                    <input type="checkbox" name="no_allergeni[]" value="<?= $a['id_allergene'] ?>"
                                           class="w-4 h-4 accent-[#e67e22]"
                                           <?= (!empty($_GET['no_allergeni']) && in_array($a['id_allergene'], $_GET['no_allergeni'])) ? 'checked' : '' ?>>
                                    <span class="text-sm group-hover:text-[#e67e22] transition-colors"><?= $a['nome'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
 
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Preferenze</h4>
                        <div class="flex flex-wrap gap-4">
                            <?php foreach($caratteristiche as $c): ?>
                                <label class="flex items-center space-x-2 group cursor-pointer">
                                    <input type="checkbox" name="caratteristiche[]" value="<?= $c['id_caratteristica'] ?>"
                                           class="w-4 h-4 accent-[#e67e22]"
                                           <?= (!empty($_GET['caratteristiche']) && in_array($c['id_caratteristica'], $_GET['caratteristiche'])) ? 'checked' : '' ?>>
                                    <span class="text-sm group-hover:text-[#e67e22] transition-colors"><?= $c['nome'] ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
 
                <div class="flex justify-center border-t pt-6 gap-4">
                    <button type="submit" class="bg-[#e67e22] text-white px-10 py-3 rounded-full font-semibold hover:bg-black transition-all shadow-lg">
                        APPLICA FILTRI
                    </button>
                    <a href="?pag=<?= $pag_numero ?>" class="text-gray-400 hover:text-black self-center text-sm font-bold">RESET</a>
                </div>
            </form>
        </section>

        <!-- selezione categorie -->
        <nav class="flex flex-wrap justify-center gap-6 md:gap-8 mb-16 border-b border-gray-200 pb-4">
            <?php foreach ($categorie as $cat): ?>
                <a href="?pag=<?= $cat['id_categoria'] ?>"
                   class="category-link serif-font text-lg md:text-2xl <?= ($cat['id_categoria'] == $pag_numero) ? 'active text-[#e67e22]' : 'text-gray-400 hover:text-gray-800' ?>">
                    <?= $cat['nome'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
 
        <!-- card prodotti -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-16 gap-y-12 mb-20">
            <?php if ($numProdotti > 0): ?>
                <?php foreach ($prodotti as $p): ?>
                    <div class="product-card">
                        <div class="flex justify-between items-baseline mb-2">
                            <h3 class="serif-font text-2xl text-gray-900 uppercase tracking-tight">
                                <?= htmlspecialchars($p['nome']) ?>
                                <?php if (!empty($p['caratteristiche'])): ?>
                                    <span class="text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded ml-2 align-middle font-sans">
                                        <?= strtoupper(htmlspecialchars($p['caratteristiche'])) ?>
                                    </span>
                                <?php endif; ?>
                            </h3>
                            <span class="text-xl font-light text-[#e67e22]">€<?= number_format($p['prezzo'], 2, ',', '.') ?></span>
                        </div>
                        <p class="text-gray-500 font-light leading-relaxed italic mb-2">
                            <?= htmlspecialchars($p['descrizione']) ?>
                        </p>
                       
                        <?php if (!empty($p['allergeni'])): ?>
                            <p class="text-[10px] text-red-400 uppercase tracking-widest font-bold">
                                <i class="fa fa-circle-exclamation mr-1"></i> Allergeni: <?= htmlspecialchars($p['allergeni']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-10 italic text-gray-400">
                    Nessun piatto trovato con i filtri selezionati.
                </div>
            <?php endif; ?>
        </div>
    </main>
 
    <footer class="bg-[#1a1a1a] text-white py-12">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="serif-font text-2xl mb-4">Pizzeria Da Paggi</h2>
            <div class="flex justify-center gap-6 mb-6">
                <a href="#" class="text-gray-400 hover:text-[#e67e22] transition-colors"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-[#e67e22] transition-colors"><i class="fab fa-instagram"></i></a>
            </div>
            <p class="text-[10px] text-gray-600 uppercase tracking-widest">© 2026 Pizzeria Da Paggi - Tutti i diritti riservati</p>
        </div>
    </footer>
 
</body>
</html>
 