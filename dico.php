<?php


const MIN_LETTRES_MOT_1 = 2;
const MIN_LETTRES_MOT_2 = 1;

$dico = [];
if (($lecteur = fopen("dico.csv", "r")) !== FALSE) {
    $header = fgetcsv($lecteur, 0, "\t");
    while (($ligne = fgetcsv($lecteur, 0, "\t")) !== FALSE) {
        if (substr($ligne[0], 0, 1) != "#" && count($ligne) >= 3) {
            [$mot, $definition, $auteur] = $ligne;
            $mot = strtoupper($mot);
            if ($auteur) {
                $definition .= " <small><em>$auteur</em></small>";
            }
            $nb_espaces = substr_count($mot, ' ');
            if ($nb_espaces > 0) {
                $definition .= " <small>(" . ($nb_espaces + 1) . " mots)</small>";
            }
            if (strlen($definition)) {
                $dico[$mot] = [$definition];
            } else {
                $dico[$mot] = [];
            }
        }
    }
    fclose($lecteur);
}

$mots_de_n_lettres = [];
foreach ($dico as $mot => $definition) {
    $n = strlen($mot);
    if (!isset($mots_de_n_lettres[$n])) {
        $mots_de_n_lettres[$n] = [];
    }
    $mots_de_n_lettres[$n][] = $mot;
    if (!empty($definitions)) {
        $dico[$mot] = [$definitions[array_rand($definitions)]];
    }
}

function mots_espaces($longueur)
{
    global $mots_de_n_lettres;
    global $dico;

    $nb_mots = 0;
    foreach ($mots_de_n_lettres[$longueur] as $mot) {
        yield $mot;
    }
    for ($i = MIN_LETTRES_MOT_1; $longueur - $i - 1 >= MIN_LETTRES_MOT_2; $i++) {
        foreach ($mots_de_n_lettres[$i] as $mot1) {
            foreach (mots_espaces($longueur - $i - 1) as $mot2) {
                if ($mot1 != $mot2) {
                    $dico["$mot1 $mot2"] = array_merge($dico[$mot1], $dico[$mot2]);
                    yield "$mot1 $mot2";
                    $dico["$mot2 $mot1"] = array_merge($dico[$mot2], $dico[$mot1]);
                    yield "$mot2 $mot1";
                }
            }
        }
    }
}
