<?php


const MIN_LETTRES_MOT_1 = 2;
const MIN_LETTRES_MOT_2 = 0;
const MAX_MOTS = 1000000;

$dico = [[]];
if (($lecteur = fopen("dico.csv", "r")) !== FALSE) {
    $header = fgetcsv($lecteur, 0, "\t");
    while (($ligne = fgetcsv($lecteur, 0, "\t")) !== FALSE) {
        if ($ligne[0] == NULL || substr($ligne[0], 0, 1) == "#") {
            continue;
        }
        switch(count($ligne)) {
            case 1:
                [$mot] = $ligne;
                $definition = "";
                break;
            case 2:
                [$mot, $definition] = $ligne;
                break;
            case 3:
                [$mot, $definition, $auteur] = $ligne;
                $definition .= " <small><em>$auteur</em></small>";
                break;
        }
        $mot = strtoupper($mot);
        $longueur = strlen($mot);
        if (!isset($dico[$longueur])) {
            $dico[$longueur] = [];
        }
        if (!isset($dico[$longueur][$mot])) {
            $dico[$longueur][$mot] = [];
        }
        if (strlen($definition)) {
            $dico[$longueur][$mot][] = $definition;
        }
    }
    fclose($lecteur);
}

function mots_espaces($longueur, $nb_mots_restants=MAX_MOTS)
{
    global $dico;

    foreach ($dico[$longueur] as $mot => $definition) {
        yield $mot;
        if (--$nb_mots_restants <= 0) return;
    }
    for ($i = MIN_LETTRES_MOT_1; $longueur - $i - 1 >= MIN_LETTRES_MOT_2; $i++) {
        foreach ($dico[$i] as $mot1 => $definition) {
            foreach (mots_espaces($longueur - $i - 1, $nb_mots_restants) as $mot2) {
                if ($mot1 != $mot2) {
                    yield "$mot1 $mot2";
                    if (--$nb_mots_restants <= 0) return;
                    yield "$mot2 $mot1";
                    if (--$nb_mots_restants <= 0) return;
                }
            }
        }
    }
}
