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
            $longueur = strlen($mot);
            if ($auteur) {
                $definition .= " <small><em>$auteur</em></small>";
            }
            if (!isset($dico[$longueur])) {
                $dico[$longueur] = [];
            }
            if (!isset($dico[$longueur][$mot])) {
                $dico[$longueur][$mot] = [];
            }
            $dico[$longueur][$mot][] = $definition;
        }
    }
    fclose($lecteur);
}

function mots_espaces($longueur)
{
    global $dico;

    $nb_mots = 0;
    foreach ($dico[$longueur] as $mot => $definition) {
        yield $mot;
    }
    for ($i = MIN_LETTRES_MOT_1; $longueur - $i - 1 >= MIN_LETTRES_MOT_2; $i++) {
        foreach ($dico[$i] as $mot1 => $definition) {
            foreach (mots_espaces($longueur - $i - 1) as $mot2) {
                if ($mot1 != $mot2) {
                    yield "$mot1 $mot2";
                    yield "$mot2 $mot1";
                }
            }
        }
    }
}
