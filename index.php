<?php
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_reporting', E_ALL);

include_once "dico.php";
include_once "Grille.php";

const HAUTEUR_PAR_DEFAUT = 6;
const LARGEUR_PAR_DEFAUT = 6;

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
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <form id="grilleForm" class="grille">
        <table>
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
                                <input type="text" maxlength="1" size="1" name="<?= $l . $c ?>" value=" " disabled />
                            <?php else: ?>
                                <input type="text" maxlength="1" size="1" name="<?= $l . $c ?>" required pattern="[A-Z]" />
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>
    </form>
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

    <script>
        const inputs = Array.from(grilleForm.querySelectorAll('input[type="text"]'))
        let largeur = <?= $largeur ?>;
        let nb_cases = inputs.length
        let index = 0
        inputs.forEach(input => {
            input.index = index++
            input.onfocus = function() {
                this.select();
            }
            input.oninput = function() {
                this.value = this.value.toUpperCase()
                if (!input.checkValidity()) {
                    input.value = ""
                }
                if (grilleForm.checkValidity()) {
                    window.setTimeout(() => alert("Grille validée !"), 100)
                }
            }
            input.onkeydown = function(event) {
                switch (event.key) {
                    case "ArrowUp":
                        inputs[(input.index - largeur + nb_cases) % nb_cases].focus()
                        break;
                    case "ArrowDown":
                        inputs[(input.index + largeur) % nb_cases].focus()
                        break;
                    case "ArrowLeft":
                        inputs[(input.index - 1 + nb_cases) % nb_cases].focus()
                        break;
                    case "ArrowRight":
                        inputs[(input.index + 1) % nb_cases].focus()
                        break;
                }
            }
        })
    </script>

</html>