<?php

include_once "dico.php";


class Grille
{
    public $grille;
    public $hauteur;
    public $largeur;
    private $grilles;
    private $lettres_suivantes;
    private $positions;
    private $nb_positions;

    public function __construct($hauteur, $largeur, $id = "")
    {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, ''));

        if ($id == "") {
            mt_srand();
        } else {
            mt_srand(crc32($id));
        }
        $this->lettres_suivantes = [];
        foreach ($hauteur == $largeur ? [$hauteur] : [$hauteur, $largeur] as $longueur) {
            $this->lettres_suivantes[$longueur] = [];
            foreach (mots_espaces($longueur) as $mot) {
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

    public function get_ligne($y, $largeur)
    {
        $ligne = "";
        for ($x = 0; $x < $largeur; $x++) {
            $ligne .= $this->grille[$y][$x];
        }
        return $ligne;
    }

    public function get_colonne($x, $hauteur)
    {
        $colonne = "";
        for ($y = 0; $y < $hauteur; $y++) {
            $colonne .= $this->grille[$y][$x];
        }
        return $colonne;
    }

    public function generateur()
    {
        $mots_utilises = [];
        $pile = [];
        $lettres_possibles = array_intersect_assoc(
            $this->lettres_suivantes[$this->largeur][""],
            $this->lettres_suivantes[$this->hauteur][""]
        );
        foreach ($lettres_possibles as $lettre => $_) {
            $pile[] = [0, $lettre];
        }

        while (!empty($pile)) {
            [$i, $lettre] = array_pop($pile);
            [$x, $y] = $this->positions[$i];
            $this->grille[$y][$x] = $lettre;

            if ($x == $this->largeur - 1) {
                $mots_utilises[$y] = $this->get_ligne($y, $x);
            } else {
                unset($mots_utilises[$y]);
            }
            if ($y == $this->hauteur - 1) {
                if (in_array($this->get_colonne($x, $y), $mots_utilises)) {
                    continue;
                }
            }

            $i++;
            if ($i == $this->nb_positions) {
                yield $this;
                continue;
            }

            [$x, $y] = $this->positions[$i];
            $lettres_possibles = array_intersect_assoc(
                $this->lettres_suivantes[$this->largeur][$this->get_ligne($y, $x)],
                $this->lettres_suivantes[$this->hauteur][$this->get_colonne($x, $y)]
            );
            foreach ($lettres_possibles as $lettre => $_) {
                $pile[] = [$i, $lettre];
            }
        }
    }

    public function current()
    {
        return $this->grilles->current();
    }

    public function valid()
    {
        return $this->grilles->valid();
    }

    public function hash()
    {
        $string = "";
        foreach ($this->grille as $ligne) {
            $string .= implode("", $ligne);
        }
        return hash('sha256', $string);
    }
}
