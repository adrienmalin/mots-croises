<?php


const MIN_LETTRES = 0;
const MAX_MOTS = 100000;


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

function fisherYatesShuffle(&$items)
{
    for ($i = count($items) - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        $tmp = $items[$i];
        $items[$i] = $items[$j];
        $items[$j] = $tmp;
    }
}

function mots_espaces($longueur) {
    global $mots_de_n_lettres;
    global $dico;

    $nb_mots = 0;
    fisherYatesShuffle($mots_de_n_lettres[$longueur]);
    foreach($mots_de_n_lettres[$longueur] as $mot) {
        yield $mot;
        if (++$nb_mots > MAX_MOTS) {
            return;
        }
    }
    for ($i = 2; $longueur - $i - 1 >= MIN_LETTRES; $i++) {
        foreach ($mots_de_n_lettres[$i] as $mot1) {
            foreach (mots_espaces($longueur - $i - 1) as $mot2) {
                if ($mot1 != $mot2) {
                    $dico["$mot1 $mot2"] = $dico[$mot1] && $dico[$mot2] ? "{$dico[$mot1]}<br/>{$dico[$mot2]}." : $dico[$mot1] . $dico[$mot2];
                    yield "$mot1 $mot2";
                    $dico["$mot2 $mot1"] = $dico[$mot2] && $dico[$mot1] ? "{$dico[$mot2]}<br/>{$dico[$mot1]}." : $dico[$mot2] . $dico[$mot1];
                    yield "$mot2 $mot1";
                    $nb_mots += 2;
                    if ($nb_mots > MAX_MOTS) {
                        return;
                    }
                }
            }
        }
    }
}