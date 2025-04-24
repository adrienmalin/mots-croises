<?php

include_once "dico.php";


const MIN_LETTRES = 1;


class Grille {
    public $grille;
    public $hauteur;
    public $largeur;
    private $grilles;
    private $mots_commencant_par;
    private $mots_utilises = [];

    public function __construct($hauteur, $largeur) {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, '.'));

        if ($hauteur == $largeur) {
            $dimensions = [$hauteur];
        } else {
            $dimensions = [$hauteur, $largeur];
        }
        $this->mots_commencant_par = [];
        foreach ($dimensions as $dimension) {
            $this->mots_commencant_par[$dimension] = [];
            foreach(mots_espaces($dimension, MIN_LETTRES) as $mot) {
                for ($i = 0; $i <= $dimension; $i++) {
                    $debut = substr($mot, 0, $i);
                    if (!isset($this->mots_commencant_par[$dimension][$debut])) {
                        $this->mots_commencant_par[$dimension][$debut] = [];
                    }
                    $this->mots_commencant_par[$dimension][$debut][] = $mot;
                }
            }
        }
        $this->grilles = $this->generateur();
        $this->grilles->current();
    }

    public function get_ligne($l, $max = 100) {
        $ligne = "";
        $min = min($this->largeur, $max);
        for ($i = 0; $i < $min; $i++) {
            $ligne .= $this->grille[$l][$i];
        }
        return $ligne;
    }

    public function set_ligne($l, $mot) {
        for ($i = 0; $i < strlen($mot); $i++) {
            $this->grille[$l][$i] = $mot[$i];
        }
    }

    public function get_colonne($c, $max = 100) {
        $colonne = "";
        $min = min($this->hauteur, $max);
        for ($i = 0; $i < $min; $i++) {
            $colonne .= $this->grille[$i][$c];
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
        foreach ($this->mots_commencant_par[$this->largeur][$this->get_ligne($l, $l)] as $mot_lig) {
            $this->set_ligne($l, $mot_lig);
            $ok = true;
            for ($c = $l; $c < $this->largeur; $c++) {
                if (!isset($this->mots_commencant_par[$this->hauteur][$this->get_colonne($c, $l+1)])) {
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
        foreach ($this->mots_commencant_par[$this->hauteur][$this->get_colonne($c, $c + 1)] as $mot_col) {
            if (isset($this->mots_utilises[$mot_col])) {
                continue;
            }
            $this->set_colonne($c, $mot_col);
            $ok = true;
            for ($l = $c; $l < $this->hauteur; $l++) {
                if (!isset($this->mots_commencant_par[$this->largeur][$this->get_ligne($l, $c+1)])) {
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
}