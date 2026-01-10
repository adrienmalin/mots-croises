<?php

include_once "Trie.php";


const CASE_NOIRE = " ";


function dico($longueur_max) {
    $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Upper(); :: NFC;', Transliterator::FORWARD);
    
    $dico = [];
    for ($longueur = 0; $longueur <= $longueur_max; $longueur++) {
        $dico[] = new Trie();
    }
    foreach (str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ") as $lettre) {
        $dico[1][$lettre] = [];
    }

    foreach (yaml_parse_file('dico.yaml') as $mot => $definitions) {
        $mot = str_replace("-", CASE_NOIRE, $mot);
        $mot = $transliterator->transliterate($mot);
        if (strpos($mot, CASE_NOIRE) !== false) {
            $mots = explode(CASE_NOIRE, $mot);
            $nb_mots = count($mots);
            $mot = implode("", $mots);
        } else {
            $nb_mots = 1;
        }

        if (strlen($mot) > $longueur_max) continue;
        
        $dico[strlen($mot)][$mot] = [];
        if (count($definitions)) {
            $definition = $definitions[mt_rand(0, count($definitions) - 1)];
            if (is_array($definition)) {
                foreach ($definition as $auteur => $def) {
                    $dico[strlen($mot)][$mot]["definition"] = $def;
                    $dico[strlen($mot)][$mot]["auteur"] = $auteur;
                }
            } else if (is_string($definition)) {
                $dico[strlen($mot)][$mot]["definition"] = $definition;
            }
        }
        if ($nb_mots > 1) $dico[strlen($mot)][$mot]["nb_mots"] = $nb_mots;
    }
    
    return $dico;
}

function mots_espaces($longueur_max) {
    $longueur_min = 1;
    $dico = dico($longueur_max);
    for ($longueur = 3; $longueur <= $longueur_max; $longueur++) {
        //$longueur_min = $longueur == $longueur_max ? 1 : 2;
        for ($position_espace = $longueur - 2; $position_espace >= $longueur_min; $position_espace--) {
            $mots_suivants = $dico[$longueur - $position_espace - 1];
            foreach ($dico[$position_espace] as $premier_mot => $definition) {
                $premier_mot[] = CASE_NOIRE;
                $dico[$longueur][$premier_mot] = $mots_suivants;
            }
        }
    }
    return $dico;
}
