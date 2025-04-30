<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);


if (!isset($_GET["grille"])) {
    $_GET["grille"] = uniqid();
    header("Location: " . dirname($_SERVER['DOCUMENT_URI']) . "?" . http_build_query($_GET));
    exit;
} else {
    $id = htmlspecialchars($_GET["grille"]);
}


include_once "dico.php";
include_once "Grille.php";


const LARGEUR_PAR_DEFAUT = 7;
const HAUTEUR_PAR_DEFAUT = 6;


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
$grille->current();


?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title>Mots croisés</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <?php if ($grille->valid()): ?>
            <table class="grille">
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
                            <td class="case <?= $grille->grille[$y][$x] == " " ? "noire" : "blanche" ?>">
                                <?php if ($grille->grille[$y][$x] == " "): ?>
                                    <input type="text" maxlength="1" size="1" value=" " disabled />
                                <?php else: ?>
                                    <input type="text" maxlength="1" size="1" pattern="[A-Z]"/>
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
                        <?php for ($y = 0; $y < $hauteur; $y++): ?>
                            <li>
                                <?php $definitions = $dico[$grille->get_ligne($y, $largeur)] ?>
                                <?php if (count($definitions) == 1): ?>
                                    <?= $definitions[0] ?>
                                <?php else: ?>
                                    <ol>
                                        <?php foreach ($dico[$grille->get_ligne($y, $largeur)] as $definition) : ?>
                                            <li><?= $definition ?></li>
                                        <?php endforeach ?>
                                    </ol>
                                <?php endif ?>
                            </li>
                        <?php endfor; ?>
                    </ol>
                </div>
                <div class="verticales">
                    <h2>Verticalement</h2>
                    <ol type="A">
                        <?php for ($x = 0; $x < $largeur; $x++): ?>
                            <li>
                                <?php $definitions = $dico[$grille->get_colonne($x, $hauteur)] ?>
                                <?php if (count($definitions) == 1): ?>
                                    <?= $definitions[0] ?>
                                <?php else: ?>
                                    <ol>
                                        <?php foreach ($dico[$grille->get_colonne($x, $hauteur)] as $definition) : ?>
                                            <li><?= $definition ?></li>
                                        <?php endforeach ?>
                                    </ol>
                                <?php endif ?>
                            </li>
                        <?php endfor; ?>
                    </ol>
                </div>
            </div>
        <?php else: ?>
            <h3 class="erreur">Erreur de génération de la grille</h3>
        <?php endif ?>

        <input type="hidden" id="lignes" <?php if (isset($_GET["lignes"])): ?>name="lignes" <?php endif ?>value="<?= $hauteur ?>" />
        <input type="hidden" id="colonnes" <?php if (isset($_GET["colonnes"])): ?>name="colonnes" <?php endif ?>value="<?= $largeur ?>" />
        <input type="hidden" id="solution_hashee" value="<?= $grille->hash() ?>" />
        <button type="submit"><img src="favicon.ico">Nouvelle grille</button>
    </form>

    <script src="script.js"></script>
</body>

</html>