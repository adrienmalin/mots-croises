<?php

include_once "dico.php";


class Grille {
    public $grille;
    public $hauteur;
    public $largeur;
    private $grilles;
    private $lettres_suivantes;
    private $positions;
    private $nb_positions;
    private $mots_utilises = [];

    public function __construct($hauteur, $largeur, $id="") {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, ''));

        if ($id == "") {
            mt_srand();
        } else {
            mt_srand(crc32($id));
        }
        $this->lettres_suivantes = [];
        foreach ($hauteur == $largeur? [$hauteur]: [$hauteur, $largeur] as $longueur) {
            $this->lettres_suivantes[$longueur] = [];
            foreach(mots_espaces($longueur) as $mot) {
                for ($i = 0; $i <= $longueur; $i++) {
                    $debut = substr($mot, 0, $i);
                    if (!isset($this->lettres_suivantes[$longueur][$debut])) {
                        $this->lettres_suivantes[$longueur][$debut] = [];
                    }
                    $this->lettres_suivantes[$longueur][$debut][substr($mot, $i, 1)] = true;
                }
            }
        }
        mt_srand();

        $this->positions = [];
        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++) {
                $this->positions[] = [$x, $y];
            }
        }
        $this->nb_positions = count($this->positions);
        
        $this->grilles = $this->generateur();
    }

    public function get_ligne($y, $largeur) {
        $ligne = "";
        for ($x = 0; $x < $largeur; $x++) {
            $ligne .= $this->grille[$y][$x];
        }
        return $ligne;
    }

    public function get_colonne($x, $hauteur) {
        $colonne = "";
        for ($y = 0; $y < $hauteur; $y++) {
            $colonne .= $this->grille[$y][$x];
        }
        return $colonne;
    }
    
    public function generateur($index=0) {
        if ($index == $this->nb_positions) {
            yield $this;
            return;
        }

        [$x, $y] = $this->positions[$index];

        $lettres_possibles = array_intersect_assoc(
            $this->lettres_suivantes[$this->largeur][$this->get_ligne($y, $x)],
            $this->lettres_suivantes[$this->hauteur][$this->get_colonne($x, $y)]
        );

        foreach ($lettres_possibles as $lettre => $_) {
            $this->grille[$y][$x] = $lettre;

            $mot_ligne = NULL;
            if ($x == $this->largeur - 1) {
                $mot_ligne = $this->get_ligne($y, $x);
                if (isset($this->mots_utilises[$mot_ligne])) {
                    continue;
                } else {
                    $this-> mots_utilises[$mot_ligne] = true;
                }
            }
            $mot_colonne = NULL;
            if ($y == $this->hauteur - 1) {
                $mot_colonne = $this->get_colonne($x, $y);
                if (isset($this->mots_utilises[$mot_colonne])) {
                    continue;
                } else {
                    $this-> mots_utilises[$mot_colonne] = true;
                }
            }
            
            yield from $this->generateur($index + 1);

            if ($mot_ligne) {
                unset($this-> mots_utilises[$mot_ligne]);
            }
            if ($mot_colonne) {
                unset($this-> mots_utilises[$mot_colonne]);
            }
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