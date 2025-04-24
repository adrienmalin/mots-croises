<?php
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_reporting', E_ALL);

include_once "dico.php";
include_once "Grille.php";

const HAUTEUR_PAR_DEFAUT = 3;
const LARGEUR_PAR_DEFAUT = 4;

$hauteur = filter_input(INPUT_GET, 'lignes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => HAUTEUR_PAR_DEFAUT,
        "min_range" => 2,
        "max_range" => 10
    ]
]);
$largeur = filter_input(INPUT_GET, 'colonnes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => LARGEUR_PAR_DEFAUT,
        "min_range" => 2,
        "max_range" => 10
    ]
]);

$grille = new Grille($hauteur, $largeur);

?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title>Mots croisés</title>
    <style>
        table {
            border-collapse: collapse;
        }

        th, td {
            width: 30px;
            height: 30px;
            text-align: center;
        }

        td {
            border: 1px solid black;
        }

        .case.noire {
            background-color: black;
        }
    </style>
</head>

<body>
    <table class="grille">
        <tr>
            <th></th>
            <?php for ($c = 0; $c < $largeur; $c++): ?>
            <th><?= chr($c + 65) ?></th>
            <?php endfor; ?>
        </tr>
        <?php for ($l = 0; $l < $hauteur; $l++): ?>
        <tr>
            <th><?= $l ?></th>
            <?php for ($c = 0; $c < $largeur; $c++): ?>
            <td class="case <?= $grille->grille[$l][$c]==" "?"noire": "blanche" ?>"><?= $grille->grille[$l][$c] ?></td>
            <?php endfor; ?>
        </tr>
        <?php endfor; ?>
    </table>

</html>