<?php

include_once "dico.php";


function melanger_cles($tableau) {
    uksort($tableau, function($a, $b) {
        return mt_rand(-1, 1);
    });
    return $tableau;
}


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

        $this->lettres_suivantes = [];
        foreach ($hauteur == $largeur ? [$hauteur] : [$hauteur, $largeur] as $longueur) {
            $this->lettres_suivantes[$longueur] = [];
            foreach (mots_espaces($longueur) as $mot) {
                $ref = &$this->lettres_suivantes[$longueur];
                for ($i = 0; $i < $longueur; $i++) {
                    $lettre = $mot[$i];
                    if (!isset($ref[$lettre])) {
                        $ref[$lettre] = [];
                    }
                    $ref = &$ref[$lettre];
                }
                $ref = [];
            }
        }

        $this->positions = [];
        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++) {
                $this->positions[] = [$x, $y];
            }
        }
        $this->nb_positions = count($this->positions);

        $this->grilles = $this->generateur($id);
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

    public function generateur($id = "")
    {
        mt_srand($id == ""? null : crc32($id));
        
        $mots_utilises = [];
        $pile = [];

        $lettres_communes = melanger_cles(array_intersect_key(
            $this->lettres_suivantes[$this->largeur],
            $this->lettres_suivantes[$this->hauteur]
        ));
        foreach ($lettres_communes as $lettre => $_) {
            $pile[] = [0, $lettre];
        }

        while (!empty($pile)) {
            [$i, $lettre] = array_pop($pile);
            [$x, $y] = $this->positions[$i];
            $this->grille[$y][$x] = $lettre;

            if ($i == $this->nb_positions - 1) {
                yield $this;
                continue;
            }

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
            [$x, $y] = $this->positions[$i];
            $lettres_suivantes_ligne = $this->lettres_suivantes[$this->largeur];
            for ($x2 = 0; $x2 < $x; $x2++) {
                $lettres_suivantes_ligne = $lettres_suivantes_ligne[$this->grille[$y][$x2]];
            }

            $lettres_suivantes_colonne = $this->lettres_suivantes[$this->hauteur];
            for ($y2 = 0; $y2 < $y; $y2++) {
                $lettres_suivantes_colonne = $lettres_suivantes_colonne[$this->grille[$y2][$x]];
            }
            
            $lettres_communes = melanger_cles(array_intersect_key(
                $lettres_suivantes_ligne,
                $lettres_suivantes_colonne
            ));
            foreach ($lettres_communes as $lettre => $_) {
                $pile[] = [$i, $lettre, $lettres_suivantes_ligne[$lettre]];
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
