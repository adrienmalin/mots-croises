<?php


include_once "Grille.php";


const HAUTEUR_DEFAUT = 7;
const HAUTEUR_MIN    = 2;
const HAUTEUR_MAX    = 10;
const LARGEUR_DEFAUT = 7;
const LARGEUR_MIN    = 2;
const LARGEUR_MAX    = 10;


$hauteur = filter_input(INPUT_GET, 'lignes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => HAUTEUR_DEFAUT,
        "min_range" => HAUTEUR_MIN,
        "max_range" => HAUTEUR_MAX
    ]
]);

$largeur = filter_input(INPUT_GET, 'colonnes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => LARGEUR_DEFAUT,
        "min_range" => LARGEUR_MIN,
        "max_range" => LARGEUR_MAX
    ]
]);

$grille = new Grille($hauteur, $largeur);
$basedir = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].dirname($_SERVER["DOCUMENT_URI"]);

if (!isset($_GET["grille"]) || $_GET["grille"] == "") {
    do {
        $id = uniqid();
        $grille_valide = $grille->genere($id);
    } while (!$grille_valide);

    $_GET["grille"] = $id;
    header("Location: $basedir/?" . http_build_query($_GET));
} else {
    $id = htmlspecialchars($_GET["grille"]);
    $grille_valide = $grille->load($id) || $grille->genere($id);
}

function formatter_definition($definition) {
    if (isset($definition["nb_mots"]) && $definition["nb_mots"] > 1){
        $nb_mots = $definition["nb_mots"];
        $nb_mots = " <small>($nb_mots mots)</small>";
    } else {
        $nb_mots = "";
    }
    if (array_key_exists(AUTEUR, $definition)) {
        $auteur = $definition[AUTEUR];
        $auteur = " <small><em>$auteur</em></small>";
    } else {
        $auteur = "";
    }
    return $definition[DEFINITION] . $nb_mots . $auteur;
}
?>
<!DOCTYPE HTML>
<html lang="fr-FR" dir="ltr" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <title>🄼🄾🅃🅂▣🄲🅁🄾🄸🅂🄴🅂</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="icon" type="image/svg+xml" href="apercu.svg.php?grille=<?=$id?>&lignes=<?=$hauteur?>&colonnes=<?=$largeur?>">
    <link rel="icon" type="image/png" sizes="96x96" href="apercu.png.php?grille=<?=$id?>&lignes=<?=$hauteur?>&colonnes=<?=$largeur?>&largeur=96&hauteur=96" />
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="🄼🄾🅃🅂 🄲🅁🄾🄸🅂🄴🅂" />
    <link rel="manifest" href="site.webmanifest" />
    <meta property="og:title" content="🄼🄾🅃🅂▣🄲🅁🄾🄸🅂🄴🅂"/>
    <meta property="og:type" content="game"/>
    <meta property="og:url" content="<?=$basedir?>"/>
    <meta property="og:image" content="<?=$basedir?>/apercu.png.php?grille=<?=$id?>&lignes=<?=$hauteur?>&colonnes=<?=$largeur?>&largeur=1200&hauteur=630"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height" content="630"/>
    <meta property="og:locale" content="fr_FR"/>
    <meta property="og:site_name" content="<?=$_SERVER["HTTP_HOST"]?>"/>
</head>

<body>
    <form id="grilleForm" method="get" location=".">
        <h1 class="large width">
            <a href=".">
                <table>
                    <tbody>
                        <tr>
                            <td colspan="2"></td>
                            <td>M</td>
                        </tr>
                        <tr>
                            <td>c</td>
                            <td>r</td>
                            <td>o</td>
                            <td>i</td>
                            <td>s</td>
                            <td>é</td>
                            <td>s</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>t</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td>s</td>
                        </tr>
                    </tbody>
                </table>
            </a>
        </h1>
        <h1 class="small width"><a href=".">Mots■croisés</a></h1>
        <div class="grille-et-definitions">
            <?php if ($grille_valide): ?>
                <div class="grille">
                    <table>
                        <tr>
                            <th></th>
                            <?php for ($x = 0; $x < $largeur; $x++): ?>
                                <th><?= chr($x + 65) ?></th>
                            <?php endfor; ?>
                            <th></th>
                        </tr>
                        <?php for ($y = 0; $y < $hauteur; $y++): ?>
                            <tr>
                                <th><?= $y + 1 ?></th>
                                <?php for ($x = 0; $x < $largeur; $x++): ?>
                                    <?php if ($grille[$y][$x] == CASE_NOIRE): ?>
                                        <td class="case noire">
                                            <input id="<?= chr($x + 65) . ($y + 1) ?>" type="text" maxlength="1" size="1" value="<?= CASE_NOIRE ?>" disabled />
                                        </td>
                                    <?php else: ?>
                                        <td class="case blanche">
                                            <input id="<?= chr($x + 65) . ($y + 1) ?>" type="text" maxlength="1" size="1" pattern="[A-Z]"
                                                title="<?= strip_tags("→ " . implode("\n→ ", array_map("formatter_definition", $grille->definitions["horizontales"][$y] ?? [])) . "\n↓ " . implode("\n↓ ", array_map("formatter_definition", $grille->definitions["verticales"][$x] ?? []))) ?>" />
                                        </td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </table>
                </div>
                <div class="definitions horizontales">
                    <h2>Horizontalement</h2>
                    <ol type="1">
                        <?php foreach ($grille->definitions["horizontales"] as $y => $definitions): ?>
                            <li>
                                <?php if (count($definitions)): ?>
                                    <?php if (count($definitions) == 1): ?>
                                        <?= formatter_definition($definitions[0]) ?>
                                    <?php else: ?>
                                        <ol>
                                        <?php foreach ($definitions as $definition) : ?>
                                            <li><?= formatter_definition($definition) ?></li>
                                        <?php endforeach ?>
                                        </ol>
                                    <?php endif ?>
                                <?php endif ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="definitions verticales">
                    <h2>Verticalement</h2>
                    <ol type="A">
                        <?php foreach ($grille->definitions["verticales"] as $x => $definitions): ?>
                            <li>
                                <?php if (count($definitions)): ?>
                                    <?php if (count($definitions) == 1): ?>
                                        <?= formatter_definition($definitions[0]) ?>
                                    <?php else: ?>
                                        <ol>
                                            <?php foreach ($definitions as $definition) : ?>
                                                <li><?= formatter_definition($definition) ?></li>
                                            <?php endforeach ?>
                                        </ol>
                                    <?php endif ?>
                                <?php endif ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <input type="hidden" id="solution_hashee" value="<?= $grille->hash() ?>" />
            <?php else: http_response_code(500); ?>
                <h3 class="erreur">Erreur de génération de la grille</h3>
            <?php endif ?>
        </div>
        
        <div class="nouvelle-grille">
            <img src="favicons/favicon.svg" width="16" height="16">
            <button type="submit">Nouvelle grille</button>
            de
            <input type="number" id="lignes"<?= isset($_GET["lignes"])? ' name="lignes"': "" ?> value="<?= $hauteur ?>" min="<?=HAUTEUR_MIN?>" max="<?=HAUTEUR_MAX?>"/>
            lignes et
            <input type="number" id="colonnes"<?= isset($_GET["colonnes"])? ' name="colonnes"': "" ?> value="<?= $largeur ?>" min="<?=LARGEUR_MIN?>" max="<?=LARGEUR_MAX?>"/>
            colonnes
        </div>
    </form>

    <script src="script.js"></script>
    <script>navigator?.serviceWorker.register('service-worker.js')</script>
</body>

</html>