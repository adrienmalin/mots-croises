<?php
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

$mots = [];
foreach ($dico as $mot => $definition) {
    $nb_lettres = strlen($mot);
    if(!isset($mots[$nb_lettres])) {
        $mots[$nb_lettres] = [];
    }
    $mots[$nb_lettres][] = $mot;
}
foreach ($mots as $nb_lettres => $liste_mots) {
    shuffle($mots[$nb_lettres]);
}

$dimensions = [$hauteur, $largeur];
$mots_par_position = [];
foreach($dimensions as $nb_lettres) {
    $mots_par_position[$nb_lettres] = [];
    foreach($mots[$nb_lettres] as $mot) {
        foreach(str_split($mot) as $i => $lettre) {
            if (!isset($mots_par_position[$nb_lettres][$i])) {
                $mots_par_position[$nb_lettres][$i] = [];
            }
            if (!isset($mots_par_position[$nb_lettres][$i][$lettre])) {
                $mots_par_position[$nb_lettres][$i][$lettre] = [];
            }
            $mots_par_position[$nb_lettres][$i][$lettre][] = $mot;
        }
    }
}

$grille = [];
for($l = 0; $l < $hauteur; $l++) {
    $grille[$l] = array_fill(0, $largeur, '.');
}

function get_ligne($l) {
    global $grille;
    return implode("", $grille[$l]);
}

function set_ligne($l, $mot) {
    global $grille;
    for($i = 0; $i < strlen($mot); $i++) {
        $grille[$l][$i] = $mot[$i];
    }
}

function get_colonne($c) {
    global $grille;
    $colonne = "";
    for($i = 0; $i < count($grille); $i++) {
        $colonne .= $grille[$i][$c];
    }
    return $colonne;
}

function set_colonne($c, $mot) {
    global $grille;
    for($i = 0; $i < strlen($mot); $i++) {
        $grille[$i][$c] = $mot[$i];
    }
}

$lignes_restantes = range(0, $hauteur-1);
$colonnes_restantes = range(0, $largeur-1);

function genere() {
    global $mots;
    global $grille;
    global $lignes_restantes;
    global $largeur;

    $l = $largeur / 2;
    array_splice($lignes_restantes, $l, 1);
    foreach($mots[$largeur] as $mot_lig) {
        set_ligne($l, $mot_lig);
        yield from trouve_une_colonne($l, $mot_lig);
    }
    $lignes_restantes[] = $l;
    $grille[$l] = array_fill(0, $largeur, '.');
}

function pire_contrainte($tests, $nb_lettres, $i, $mot) {
    global $mots_par_position;
    $nb_mots_min = PHP_INT_MAX;
    $pire_contrainte = 0;
    foreach($tests as $test) {
        if(
            !array_key_exists($i, $mots_par_position[$nb_lettres]) ||
            !array_key_exists($mot[$test], $mots_par_position[$nb_lettres][$i])
        ) {
            return -1;
        } else {
            $nb_mots = count($mots_par_position[$nb_lettres][$i][$mot[$test]]);
            if($nb_mots < $nb_mots_min) {
                $pire_contrainte = $test;
                $nb_mots_min = $nb_mots;
            }
        }
    }
    return $pire_contrainte;
}

function trouve_une_colonne($l, $mot_lig) {
    global $grille;
    global $colonnes_restantes;
    global $lignes_restantes;
    global $hauteur;
    global $mots_par_position;
    
    $c = pire_contrainte($colonnes_restantes, $hauteur, $l, $mot_lig);
    if ($c == -1) {
        return;
    }   
    $colonne = get_colonne($c);
    array_splice($colonnes_restantes, $c, 1);
    foreach ($mots_par_position[$hauteur][$l][$mot_lig[$c]] as $mot_col) {
        if ($mot_col == $colonne || preg_match("/^$colonne$/", $mot_col)) {
            set_colonne($c, $mot_col);
            if (count($lignes_restantes)) {
                yield from trouve_une_ligne($c, $mot_col);
            } else if (count($colonnes_restantes)) {
                yield from trouve_une_colonne($l, $mot_lig);
            } else {
                yield;
            }
        }
    }
    $colonnes_restantes[] = $c;
    set_colonne($c, $colonne);
}

function trouve_une_ligne($c, $mot_col) {
    global $grille;
    global $colonnes_restantes;
    global $lignes_restantes;
    global $largeur;
    global $mots_par_position;

    $l = pire_contrainte($lignes_restantes, $largeur, $c, $mot_col);
    if ($l == -1) {
        return;
    }
    $ligne = get_ligne($l);
    array_splice($lignes_restantes, $l, 1);
    foreach ($mots_par_position[$largeur][$c][$mot_col[$l]] as $mot_lig) {
        if ($mot_lig == $ligne || preg_match("/^$ligne$/", $mot_lig)) {
            set_ligne($l, $mot_lig);
            if (count($colonnes_restantes)) {
                yield from trouve_une_colonne($l, $mot_lig);
            } else if (count($lignes_restantes)) {
                yield from trouve_une_ligne($c, $mot_col);
            } else {
                yield;
            }
        }
    }
    $lignes_restantes[] = $l;
    set_ligne($l, $ligne);
}

genere()->current();

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
            <?php for($c=0; $c<$largeur; $c++): ?>
                <th><?=chr($c+65)?></th>
            <?php endfor; ?>
        </tr>
        <?php for($l=0; $l<$hauteur; $l++): ?>
            <tr>
                <th><?=$l?></th>
                <?php for($c=0; $c<$largeur; $c++): ?>
                    <td><?=$grille[$l][$c]?></td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>
</html>