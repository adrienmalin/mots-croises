<?php
include_once "Trie.php";


const MIN_PREMIER_MOT = 1;
const MIN_MOTS_SUIVANTS = 1;


$nb_mots = 0;

function dico($longueur_max) {
    global $nb_mots;

    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Upper(); :: NFC;', Transliterator::FORWARD);
    
    $dico = [[""]];
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
            $mot = str_replace("-", " ", $mot);
            
            $mot = $transliterator->transliterate($mot);
            if (strpos($mot, " ") !== false) {
                $mots = explode(" ", $mot);
                $nb_mots = count($mots);
                $mot = implode("", $mots);
                foreach($definitions as $i => $definition) {
                    $definitions[$i] .= " ($nb_mots mots)";
                }
            }

            $longueur = strlen($mot);
            $dico[$longueur][$mot] = $definitions;
        }
        fclose($lecteur);
    }
    
    return $dico;
}

function mots_espaces($longueur_max) {
    global $nb_mots;

    $dico = dico($longueur_max);
    for ($longueur = 1; $longueur <= $longueur_max; $longueur++) {
        for ($position_espace = MIN_PREMIER_MOT; $position_espace + MIN_MOTS_SUIVANTS < $longueur; $position_espace++) {
            $mots_suivants = $dico[$longueur - $position_espace - 1];
            foreach ($dico[$position_espace]->arrayIterator() as $premier_mot => $definition) {
                $premier_mot[] = " ";
                $dico[$longueur]->arraySet($premier_mot, $mots_suivants);
            }
        }
    }
    return $dico;
}
