<?php

include_once "Trie.php";


const CASE_NOIRE = " ";


function dico($longueur_max) {
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Upper(); :: NFC;', Transliterator::FORWARD);
    
    $dico = [];
    for ($longueur = 0; $longueur <= $longueur_max; $longueur++) {
        $dico[] = new Trie();
    }
    if (($lecteur = fopen("dico.csv", "r")) !== FALSE) {
        $entete = fgetcsv($lecteur, 0, "\t");
        while (($ligne = fgetcsv($lecteur, 0, "\t")) !== FALSE) {
            if (
                $ligne[0] == NULL
                || substr($ligne[0], 0, 1) == "#"
                || strlen($ligne[0]) > $longueur_max
            ) continue;

            $mot = $ligne[0];
            $definitions = array_slice($ligne, 1);

            $mot = str_replace("-", CASE_NOIRE, $mot);
            $mot = $transliterator->transliterate($mot);
            if (strpos($mot, CASE_NOIRE) !== false) {
                $mots = explode(CASE_NOIRE, $mot);
                $nb_mots = count($mots);
                $mot = implode("", $mots);
                foreach($definitions as $i => $definition) {
                    $definitions[$i] = "$definition#$nb_mots";
                }
            }

            $dico[strlen($mot)][$mot] = $definitions;
        }
        fclose($lecteur);
    }
    
    return $dico;
}

function mots_espaces($longueur_max) {
    $dico = dico($longueur_max);
    for ($longueur = $longueur_max; $longueur >= 2; $longueur--) {
        for ($position_espace = $longueur - 2; $position_espace >= 1; $position_espace--) {
            $mots_suivants = $dico[$longueur - $position_espace - 1];
            foreach ($dico[$position_espace] as $premier_mot => $definition) {
                $premier_mot[] = CASE_NOIRE;
                $dico[$longueur][$premier_mot] = $mots_suivants;
            }
        }
    }
    return $dico;
}
