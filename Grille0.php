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

    public function get_ligne($y, $largeur) {
        $ligne = "";
        for ($x = 0; $x < $largeur; $x++) {
            $ligne .= $this->grille[$y][$x];
        }
        return $ligne;
    }

    public function set_ligne($y, $mot) {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$y][$i] = $mot[$i];
        }
    }

    public function get_colonne($x, $hauteur) {
        $colonne = "";
        for ($y = 0; $y < $hauteur; $y++) {
            $colonne .= $this->grille[$y][$x];
        }
        return $colonne;
    }

    public function set_colonne($x, $mot) {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$i][$x] = $mot[$i];
        }
    }
    
    public function generateur() {
        yield from $this->trouve_une_ligne(0);
        $this->grille = array_fill(0, $this->hauteur, array_fill(0, $this->largeur, ' '));
    }

    private function trouve_une_ligne($y) {
        global $mots_de_n_lettres;
        $largeur = min($y, $this->largeur);
        $hauteur = min($y + 1, $this->hauteur);
        foreach ($this->debuts[$this->largeur][$this->get_ligne($y, $largeur)] as $mot_lig) {
            $this->set_ligne($y, $mot_lig);
            $ok = true;
            for ($x = $y; $x < $this->largeur; $x++) {
                if (!isset($this->debuts[$this->hauteur][$this->get_colonne($x, $hauteur)])) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $this->mots_utilises[$mot_lig] = true;
            if ($y < $this->largeur) {
                yield from $this->trouve_une_colonne($y);
            } else if ($y + 1 < $this->hauteur) {
                yield from $this->trouve_une_ligne($y + 1);
            } else {
                yield $this;
            }
            unset($this->mots_utilises[$mot_lig]);
        }
    }

    private function trouve_une_colonne($x) {
        global $mots_de_n_lettres;
        $hauteur = min($x + 1, $this->hauteur);
        $largeur = min($x + 1, $this->largeur);
        foreach ($this->debuts[$this->hauteur][$this->get_colonne($x, $hauteur)] as $mot_col) {
            if (isset($this->mots_utilises[$mot_col])) {
                continue;
            }
            $this->set_colonne($x, $mot_col);
            $ok = true;
            for ($y = $x; $y < $this->hauteur; $y++) {
                if (!isset($this->debuts[$this->largeur][$this->get_ligne($y, $largeur)])) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $this->mots_utilises[$mot_col] = true;
            if ($x +1 < $this->hauteur) {
                yield from $this->trouve_une_ligne($x + 1);
            } else if ($x + 1 < $this->largeur) {
                yield from $this->trouve_une_colonne($x + 1);
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