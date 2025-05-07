<?php
include_once "Trie.php";


const MIN_PREMIER_MOT = 1;
const MIN_MOTS_SUIVANTS = 1;


$dico = [[""]];
if (($lecteur = fopen("dico.csv", "r")) !== FALSE) {
    $header = fgetcsv($lecteur, 0, "\t");
    while (($ligne = fgetcsv($lecteur, 0, "\t")) !== FALSE) {
        if ($ligne[0] == NULL || substr($ligne[0], 0, 1) == "#") continue;
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
        $mot = str_split(strtoupper($mot));
        $longueur = count($mot);
        if (!isset($dico[$longueur])) $dico[$longueur] = new Trie();
        if (!isset($dico[$longueur][$mot])) $dico[$longueur][$mot] = [];
        if (strlen($definition)) $dico[$longueur][$mot][] = $definition;
    }
    fclose($lecteur);
}

function tries($longueur_max) {
    global $dico;

    $_tries = [[]];
    for ($longueur = 1; $longueur <= $longueur_max; $longueur++) {
        $_tries[$longueur] = $dico[$longueur];
        for ($position_espace = MIN_PREMIER_MOT; $position_espace + MIN_MOTS_SUIVANTS < $longueur; $position_espace++) {
            $mots_suivants = $_tries[$longueur - $position_espace - 1];
            foreach ($dico[$position_espace] as $premier_mot => $definition) {
                $premier_mot[] = " ";
                $_tries[$longueur][$premier_mot] = $mots_suivants;
            }
        }
    }
    return $_tries;
}
