<?php

const HAUTEUR_PAR_DEFAUT = 6;
const LARGEUR_PAR_DEFAUT = 6;

echo "<!--\n";
var_dump($_SERVER);
echo "\n-->\n";


$id = filter_input(INPUT_GET, 'grille', FILTER_VALIDATE_REGEXP, [
    "options" => [
        "regexp" => "/^[a-f0-9]{13}$/"
    ]
]);
if (!$id) {
    $_GET["grille"] = uniqid();
    header("Location: " . $_SERVER['REQUEST_URI'] . "?" . http_build_query($_GET));
    exit;
}


include_once "dico.php";
include_once "Grille.php";


$hauteur = filter_input(INPUT_GET, 'lignes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => HAUTEUR_PAR_DEFAUT,
        "min_range" => 2,
        "max_range" => 30
    ]
]);
$largeur = filter_input(INPUT_GET, 'colonnes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => LARGEUR_PAR_DEFAUT,
        "min_range" => 2,
        "max_range" => 30
    ]
]);

$grille = new Grille($hauteur, $largeur, $id);

?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title>Mots croisés</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico">
</head>

<body>
    <form id="grilleForm" method="get" location=".">
        <h1>
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
        <table class="grille">
            <tr>
                <th></th>
                <?php for ($c = 0; $c < $largeur; $c++): ?>
                    <th><?= chr($c + 65) ?></th>
                <?php endfor; ?>
            </tr>
            <?php for ($l = 0; $l < $hauteur; $l++): ?>
                <tr>
                    <th><?= $l + 1 ?></th>
                    <?php for ($c = 0; $c < $largeur; $c++): ?>
                        <td class="case <?= $grille->grille[$l][$c] == " " ? "noire" : "blanche" ?>">
                            <?php if ($grille->grille[$l][$c] == " "): ?>
                                <input type="text" maxlength="1" size="1" value=" " disabled />
                            <?php else: ?>
                                <input type="text" maxlength="1" size="1" pattern="[A-Z]" />
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
        <div class="definitions">
            <div class="horizontales">
                <h2>Horizontalement</h2>
                <ol>
                    <?php for ($l = 0; $l < $hauteur; $l++): ?>
                        <li><?= $dico[$grille->get_ligne($l, $largeur)] ?></li>
                    <?php endfor; ?>
                </ol>
            </div>
            <div class="verticales">
                <h2>Verticalement</h2>
                <ol type="A">
                    <?php for ($c = 0; $c < $largeur; $c++): ?>
                        <li><?= $dico[$grille->get_colonne($c, $hauteur)] ?></li>
                    <?php endfor; ?>
                </ol>
            </div>
        </div>

        <input type="hidden" id="lignes" name="lignes" value="<?= $hauteur ?>" />
        <input type="hidden" id="colonnes" name="colonnes" value="<?= $largeur ?>" />
        <input type="hidden" id="solution_hashee" value="<?= $grille->hash() ?>" />
        <button type="submit">Nouvelle grille</button>
    </form>

    <script src="script.js"></script>
</body>

</html>