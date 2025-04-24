<?php

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

$mots_de_n_lettres = [[]];
foreach ($dico as $mot => $definition) {
    $n = strlen($mot);
    if (!isset($mots_de_n_lettres[$n])) {
        $mots_de_n_lettres[$n] = [];
    }
    $mots_de_n_lettres[$n][] = $mot;
}
foreach ($mots_de_n_lettres as $n => $mots) {
    shuffle($mots_de_n_lettres[$n]);
}

function mots_espaces($max, $min=0) {
    global $mots_de_n_lettres;
    global $dico;

    foreach($mots_de_n_lettres[$max] as $mot) {
        yield $mot;
    }
    for ($i = ceil($max / 2); $max - $i -1 >= $min; $i++) {
        foreach ($mots_de_n_lettres[$i] as $mot1) {
            foreach (mots_espaces($max - $i -1, $min) as $mot2) {
                if ($mot1 != $mot2) {
                    $dico["$mot1 $mot2"] = $dico[$mot1] && $dico[$mot2] ? "{$dico[$mot1]}<br/>{$dico[$mot2]}." : $dico[$mot1] . $dico[$mot2];
                    yield "$mot1 $mot2";
                    $dico["$mot2 $mot1"] = $dico[$mot2] && $dico[$mot1] ? "{$dico[$mot2]}<br/>{$dico[$mot1]}." : $dico[$mot2] . $dico[$mot1];
                    yield "$mot2 $mot1";
                }
            }
        }
    }
}