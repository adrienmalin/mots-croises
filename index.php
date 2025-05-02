<?php

if (isset($_GET["grille"])) {
    $id = htmlspecialchars($_GET["grille"]);
} else {
    $_GET["grille"] = uniqid();
    header("Location: " . dirname($_SERVER['DOCUMENT_URI']) . "?" . http_build_query($_GET));
    exit;
}


include_once "dico.php";
include_once "Grille.php";

const HAUTEUR_DEFAUT = 6;
const HAUTEUR_MIN = 2;
const HAUTEUR_MAX = 10;
const LARGEUR_DEFAUT = 6;
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

$grille = new Grille($hauteur, $largeur, $id);
$grille->current();

function melanger_valeurs(&$tableau)
{
    usort($tableau, function ($a, $b) {
        return mt_rand(-1, 1);
    });
}

$definitions_horizontales = [];
foreach ($grille->lignes as $y => $mots) {
    $definitions_horizontales[$y] = [];
    foreach ($mots as $mot) {
        $definitions = $dico[strlen($mot)][$mot];
        if (count($definitions)) {
            $definitions_horizontales[$y][] = array_pop($definitions);
        }
    }
}
$definitions_verticales = [];
foreach ($grille->colonnes as $x => $mots) {
    $definitions_verticales[$x] = [];
    foreach ($mots as $mot) {
        $definitions = $dico[strlen($mot)][$mot];
        if (count($definitions)) {
            $definitions_verticales[$x][] = array_pop($definitions);
        }
    }
}
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title>Mots croisés</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

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
        <h1 class="small width">Mots croisés</h1>
        <div class="grille-et-definitions">
            <?php if ($grille->valid()): ?>
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
                                    <?php if ($grille[$y][$x] == " "): ?>
                                        <td class="case noire">
                                            <input id="<?= chr($x + 65) . ($y + 1) ?>" type="text" maxlength="1" size="1" value=" " disabled />
                                        </td>
                                    <?php else: ?>
                                        <td class="case blanche">
                                            <input id="<?= chr($x + 65) . ($y + 1) ?>" type="text" maxlength="1" size="1" pattern="[A-Z]" placeholder="<?= $grille[$y][$x] ?>"
                                                title="<?= "→ " . strip_tags(implode("\n→ ", $definitions_horizontales[$y])) . "\n↓ " . strip_tags(implode("\n↓ ", $definitions_verticales[$x])) ?>" />
                                        </td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </table>
                </div>
                <div class="definitions horizontales">
                    <h2>Horizontalement</h2>
                    <ol>
                        <?php foreach ($definitions_horizontales as $y => $definitions): ?>
                            <li>
                                <?php if (count($definitions) == 1): ?>
                                    <?= $definitions[0] ?>
                                <?php else: ?>
                                    <ol>
                                        <?php foreach ($definitions as $definition) : ?>
                                            <li><?= $definition ?></li>
                                        <?php endforeach ?>
                                    </ol>
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
                                <?php if (count($definitions) == 1): ?>
                                    <?= $definitions[0] ?>
                                <?php else: ?>
                                    <ol>
                                        <?php foreach ($definitions as $definition) : ?>
                                            <li><?= $definition ?></li>
                                        <?php endforeach ?>
                                    </ol>
                                <?php endif ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php else: http_response_code(500); ?>
                <h3 class="erreur">Erreur de génération de la grille</h3>
            <?php endif ?>
        </div>
        
        <div class="nouvelle-grille">
            <img src="favicon.svg" width="16" height="16">
            <button type="submit">
                Nouvelle grille de
                <input type="number" id="lignes"<?= isset($_GET["lignes"])? 'name="lignes"': "" ?> value="<?= $hauteur ?>" min="<?=HAUTEUR_MIN?>" max="<?=HAUTEUR_MAX?>"/>
                lignes et
                <input type="number" id="colonnes"<?= isset($_GET["colonnes"])? 'name="colonnes"': "" ?> value="<?= $largeur ?>" min="<?=LARGEUR_MIN?>" max="<?=LARGEUR_MAX?>"/>
                colonnes
            </button>
        </div>
    </form>

    <script src="script.js"></script>
</body>

</html>