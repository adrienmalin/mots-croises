<?php
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('error_reporting', E_ALL);

$default_lignes = 3;
$default_colonnes = 4;

$hauteur = filter_input(INPUT_GET, 'lignes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => $default_lignes,
        "min_range" => 2,
        "max_range" => 10
    ]
]);
$largeur = filter_input(INPUT_GET, 'colonnes', FILTER_VALIDATE_INT, [
    "options" => [
        "default" => $default_colonnes,
        "min_range" => 2,
        "max_range" => 10
    ]
]);

$dico = [];
if (($handle = fopen("dico.csv", "r")) !== FALSE) {
    $header = fgetcsv($handle, 0, "\t");
    while (($ligne = fgetcsv($handle, 0, "\t")) !== FALSE) {
        if (count($ligne) >= 2) {
            $mot = $ligne[0];
            $definition = $ligne[1];
            $dico[$mot] = $definition;
        }
    }
    fclose($handle);
}

$mots_de_n_lettres = [];
foreach ($dico as $mot => $definition) {
    $n = strlen($mot);
    if (!isset($mots_de_n_lettres[$n])) {
        $mots_de_n_lettres[$n] = [];
    }
    $mots_de_n_lettres[$n][] = $mot;
}
foreach ($mots_de_n_lettres as $n => $liste_mots) {
    shuffle($mots_de_n_lettres[$n]);
}

$mots_par_position = [];
foreach ([$hauteur, $largeur] as $n) {
    $mots_par_position[$n] = [];
    foreach ($mots_de_n_lettres[$n] as $mot) {
        foreach (str_split($mot) as $i => $lettre) {
            if (!isset($mots_par_position[$n][$i])) {
                $mots_par_position[$n][$i] = [];
            }
            if (!isset($mots_par_position[$n][$i][$lettre])) {
                $mots_par_position[$n][$i][$lettre] = [];
            }
            $mots_par_position[$n][$i][$lettre][] = $mot;
        }
    }
}

function pire_contrainte($tests, $nb_lettres, $i, $mot)
{
    global $mots_par_position;
    $nb_mots_min = PHP_INT_MAX;
    $pire_contrainte = 0;
    foreach ($tests as $test) {
        if (
            !array_key_exists($i, $mots_par_position[$nb_lettres]) ||
            !array_key_exists($mot[$test], $mots_par_position[$nb_lettres][$i])
        ) {
            return -1;
        } else {
            $nb_mots = count($mots_par_position[$nb_lettres][$i][$mot[$test]]);
            if ($nb_mots < $nb_mots_min) {
                $pire_contrainte = $test;
                $nb_mots_min = $nb_mots;
            }
        }
    }
    return $pire_contrainte;
}


class Grille
{
    public $hauteur;
    public $largeur;
    public $grille;
    public $lignes_restantes;
    public $colonnes_restantes;

    public function __construct($hauteur, $largeur)
    {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, '.'));
        $this->lignes_restantes = range(0, $hauteur - 1);
        $this->colonnes_restantes = range(0, $largeur - 1);
    }

    public function get_ligne($l)
    {
        return implode("", $this->grille[$l]);
    }

    public function set_ligne($l, $mot)
    {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$l][$i] = $mot[$i];
        }
    }
    public function get_colonne($c)
    {
        $colonne = "";
        for ($i = 0; $i < $this->hauteur; $i++) {
            $colonne .= $this->grille[$i][$c];
        }
        return $colonne;
    }
    public function set_colonne($c, $mot)
    {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$i][$c] = $mot[$i];
        }
    }
    
    public function genere()
    {
        global $mots_de_n_lettres;

        $l = $this->largeur / 2;
        array_splice($this->lignes_restantes, $l, 1);
        foreach ($mots_de_n_lettres[$this->largeur] as $mot_lig) {
            $this->set_ligne($l, $mot_lig);
            yield from $this->trouve_une_colonne($l, $mot_lig);
        }
        $this->lignes_restantes[] = $l;
        $this->grille[$l] = array_fill(0, $this->largeur, '.');
    }

    public function trouve_une_colonne($l, $mot_lig)
    {
        global $mots_par_position;

        $c = pire_contrainte($this->colonnes_restantes, $this->hauteur, $l, $mot_lig);
        if ($c == -1) {
            return;
        }
        $colonne = $this->get_colonne($c);
        array_splice($this->colonnes_restantes, $c, 1);
        foreach ($mots_par_position[$this->hauteur][$l][$mot_lig[$c]] as $mot_col) {
            if ($mot_col == $colonne || preg_match("/^$colonne$/", $mot_col)) {
                $this->set_colonne($c, $mot_col);
                if (count($this->lignes_restantes)) {
                    yield from $this->trouve_une_ligne($c, $mot_col);
                } else if (count($this->colonnes_restantes)) {
                    yield from $this->trouve_une_colonne($l, $mot_lig);
                } else {
                    yield;
                }
            }
        }
        $this->colonnes_restantes[] = $c;
        $this->set_colonne($c, $colonne);
    }

    public function trouve_une_ligne($c, $mot_col)
    {
        global $mots_par_position;

        $l = pire_contrainte($this->lignes_restantes, $this->largeur, $c, $mot_col);
        if ($l == -1) {
            return;
        }
        $ligne = $this->get_ligne($l);
        array_splice($this->lignes_restantes, $l, 1);
        foreach ($mots_par_position[$this->largeur][$c][$mot_col[$l]] as $mot_lig) {
            if ($mot_lig == $ligne || preg_match("/^$ligne$/", $mot_lig)) {
                $this->set_ligne($l, $mot_lig);
                if (count($this->colonnes_restantes)) {
                    yield from $this->trouve_une_colonne($l, $mot_lig);
                } else if (count($this->lignes_restantes)) {
                    yield from $this->trouve_une_ligne($c, $mot_col);
                } else {
                    yield;
                }
            }
        }
        $this->lignes_restantes[] = $l;
        $this->set_ligne($l, $ligne);
    }

    public function affiche()
    {
        echo "<table>";
        echo "<tr><th></th>";
        for ($c = 0; $c < $this->largeur; $c++) {
            echo "<th>" . chr($c + 65) . "</th>";
        }
        echo "</tr>";
        for ($l = 0; $l < $this->hauteur; $l++) {
            echo "<tr><th>" . $l . "</th>";
            for ($c = 0; $c < $this->largeur; $c++) {
                echo "<td>" . $this->grille[$l][$c] . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

$grille = new Grille($hauteur, $largeur);
$grille->genere()->current();

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

        td {
            width: 30px;
            height: 30px;
            text-align: center;
            border: 1px solid black;
        }
    </style>
</head>

<body>
    <table>
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
                    <td><?= $grille->grille[$l][$c] ?></td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>

</html>