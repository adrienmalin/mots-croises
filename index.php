<?php
include_once "Grille.php";


const HAUTEUR_DEFAUT = 8;
const HAUTEUR_MIN = 2;
const HAUTEUR_MAX = 10;
const LARGEUR_DEFAUT = 8;
const LARGEUR_MIN = 2;
const LARGEUR_MAX = 10;


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

$grille_valide = false;
$grille = new Grille($hauteur, $largeur);
$basedir = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].dirname($_SERVER["DOCUMENT_URI"]);

if (isset($_GET["grille"]) || $_GET["grille"] == "") {
    $id = htmlspecialchars($_GET["grille"]);
} else {
    do {
        $id = uniqid();
    } while (!$grille->genere($id));
    $grille_valide = true;

    $_GET["grille"] = $id;
    header("Location: $basedir/?" . http_build_query($_GET));
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
    <link rel="icon" type="image/svg+xml" href="favicons/favicon.svg">
    <link rel="icon" type="image/png" href="favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="🄼🄾🅃🅂 🄲🅁🄾🄸🅂🄴🅂" />
    <link rel="manifest" href="site.webmanifest" />
    <meta property="og:title" content="🄼🄾🅃🅂▣🄲🅁🄾🄸🅂🄴🅂"/>
    <meta property="og:type" content="game"/>
    <meta property="og:url" content="<?=$basedir?>"/>
    <meta property="og:image" content="<?=$basedir?>/thumbnail.php?grille=<?=$id?>&lignes=<?=$hauteur?>&colonnes=<?=$largeur?>&largeur=1200&hauteur=630"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height" content="630"/>
    <meta property="og:locale" content="fr_FR"/>
    <meta property="og:site_name" content="<?=$_SERVER["HTTP_HOST"]?>"/>
</head>

<?php
$grille_valide = $grille_valide || $grille->load($id) || $grille->genere($id);

mt_srand(crc32($id));
if ($grille_valide) {
    $definitions_horizontales = [];
    for ($y = 0; $y < $hauteur; $y++) {
        $definitions_horizontales[$y] = [];
        foreach ($grille->lignes[$y] as $mot) {
            $definitions = $grille->dico[strlen($mot)][$mot];
            if (count($definitions)) {
                $definition = $definitions[mt_rand(0, count($definitions) - 1)];
                if (strpos($definition, "#") !== false) {
                    [$definition, $nb_mots] = explode("#", $definition);
                    $nb_mots = " <small>($nb_mots mots)</small>";
                } else {
                    $nb_mots = "";
                }
                if (strpos($definition, "@") !== false) {
                    [$definition, $auteur] = explode("@", $definition);
                    $auteur = " <small><em>$auteur</em></small>";
                } else {
                    $auteur = "";
                }
                $definitions_horizontales[$y][] = $definition;
            }
        }
    }
    $definitions_verticales = [];
    for ($x = 0 ; $x < $largeur; $x++) {
        $definitions_verticales[$x] = [];
        foreach ($grille->colonnes[$x] as $mot) {
            $definitions = $grille->dico[strlen($mot)][$mot];
            if (count($definitions)) {
                $definition = $definitions[mt_rand(0, count($definitions) - 1)];
                if (strpos($definition, "#") !== false) {
                    [$definition, $nb_mots] = explode("#", $definition);
                    $nb_mots = " <small>($nb_mots mots)</small>";
                } else {
                    $nb_mots = "";
                }
                if (strpos($definition, "@") !== false) {
                    [$definition, $auteur] = explode("@", $definition);
                    $auteur = " <small><em>$auteur</em></small>";
                } else {
                    $auteur = "";
                }
                $definitions_verticales[$x][] = $definition . $nb_mots . $auteur;
            }
        }
    }
}
?>

<body>
    <form id="grilleForm" method="get" location=".">
        <h1 class="large width">
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
        </h1>
        <h1 class="small width">Mots■croisés</h1>
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
                                            <input id="<?= chr($x + 65) . ($y + 1) ?>" type="text" maxlength="1" size="1" pattern="[A-Z]" placeholder="<?= $grille[$y][$x] ?>"
                                                title="<?= strip_tags("→ " . implode("\n→ ", $definitions_horizontales[$y]) . "\n↓ " . implode("\n↓ ", $definitions_verticales[$x])) ?>" />
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
                        <?php foreach ($definitions_horizontales as $y => $definitions): ?>
                            <li>
                                <?php if (count($definitions)): ?>
                                    <?php if (count($definitions) == 1): ?>
                                        <?= $definitions[0] ?>
                                    <?php else: ?>
                                        <ol>
                                            <?php foreach ($definitions as $definition) : ?>
                                                <li><?= $definition ?></li>
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
                        <?php foreach ($definitions_verticales as $x => $definitions): ?>
                            <li>
                                <?php if (count($definitions)): ?>
                                    <?php if (count($definitions) == 1): ?>
                                        <?= $definitions[0] ?>
                                    <?php else: ?>
                                        <ol>
                                            <?php foreach ($definitions as $definition) : ?>
                                                <li><?= $definition ?></li>
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