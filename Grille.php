<?php

include_once "dico.php";


class Grille {
    public $grille;
    public $hauteur;
    public $largeur;
    private $grilles;
    private $debuts;
    private $mots_utilises = [];

    public function __construct($hauteur, $largeur, $id="") {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, '.'));

        if ($id == "") {
            mt_srand();
        } else {
            mt_srand(crc32($id));
        }
        $this->debuts = [];
        foreach ($hauteur == $largeur? [$hauteur]: [$hauteur, $largeur] as $longueur) {
            $this->debuts[$longueur] = [];
            $nb_mots = 0;
            foreach(mots_espaces($longueur) as $mot) {
                for ($i = 0; $i <= $longueur; $i++) {
                    $debut = substr($mot, 0, $i);
                    if (!isset($this->debuts[$longueur][$debut])) {
                        $this->debuts[$longueur][$debut] = [];
                    }
                    $this->debuts[$longueur][$debut][] = $mot;
                }
            }
        }
        mt_srand();
        
        $this->grilles = $this->generateur();
    }

    public function get_ligne($l, $largeur) {
        $ligne = "";
        for ($c = 0; $c < $largeur; $c++) {
            $ligne .= $this->grille[$l][$c];
        }
        return $ligne;
    }

    public function set_ligne($l, $mot) {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$l][$i] = $mot[$i];
        }
    }

    public function get_colonne($c, $hauteur) {
        $colonne = "";
        for ($l = 0; $l < $hauteur; $l++) {
            $colonne .= $this->grille[$l][$c];
        }
        return $colonne;
    }

    public function set_colonne($c, $mot) {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$i][$c] = $mot[$i];
        }
    }
    
    public function generateur() {
        yield from $this->trouve_une_ligne(0);
        $this->grille = array_fill(0, $this->hauteur, array_fill(0, $this->largeur, ' '));
    }

    private function trouve_une_ligne($l) {
        global $mots_de_n_lettres;
        $largeur = min($l, $this->largeur);
        $hauteur = min($l + 1, $this->hauteur);
        foreach ($this->debuts[$this->largeur][$this->get_ligne($l, $largeur)] as $mot_lig) {
            $this->set_ligne($l, $mot_lig);
            $ok = true;
            for ($c = $l; $c < $this->largeur; $c++) {
                if (!isset($this->debuts[$this->hauteur][$this->get_colonne($c, $hauteur)])) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $this->mots_utilises[$mot_lig] = true;
            if ($l < $this->largeur) {
                yield from $this->trouve_une_colonne($l);
            } else if ($l + 1 < $this->hauteur) {
                yield from $this->trouve_une_ligne($l + 1);
            } else {
                yield $this;
            }
            unset($this->mots_utilises[$mot_lig]);
        }
    }

    private function trouve_une_colonne($c) {
        global $mots_de_n_lettres;
        $hauteur = min($c + 1, $this->hauteur);
        $largeur = min($c + 1, $this->largeur);
        foreach ($this->debuts[$this->hauteur][$this->get_colonne($c, $hauteur)] as $mot_col) {
            if (isset($this->mots_utilises[$mot_col])) {
                continue;
            }
            $this->set_colonne($c, $mot_col);
            $ok = true;
            for ($l = $c; $l < $this->hauteur; $l++) {
                if (!isset($this->debuts[$this->largeur][$this->get_ligne($l, $largeur)])) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $this->mots_utilises[$mot_col] = true;
            if ($c +1 < $this->hauteur) {
                yield from $this->trouve_une_ligne($c + 1);
            } else if ($c + 1 < $this->largeur) {
                yield from $this->trouve_une_colonne($c + 1);
            } else {
                yield $this;
            }
            unset($this->mots_utilises[$mot_col]);
        }
    }

    public function current() {
        return $this->grilles->current();
    }

    public function valid() {
        return $this->grilles->valid();
    }

    public function hash() {
        $string = "";
        foreach ($this->grille as $ligne) {
            $string .= implode("", $ligne);
        }
        return hash('sha256', $string);
    }
}