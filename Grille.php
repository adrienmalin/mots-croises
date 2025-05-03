<?php

include_once "dico.php";


class Grille implements Iterator, ArrayAccess {
    public $grille;
    public $hauteur;
    public $largeur;
    private $grilles;
    private $lettres_suivantes;
    private $positions;
    private $nb_positions;
    public $lignes;
    public $colonnes;

    public function __construct($hauteur, $largeur, $id = "")
    {
        $this->hauteur = $hauteur;
        $this->largeur = $largeur;
        $this->grille = array_fill(0, $hauteur, array_fill(0, $largeur, ''));
        $this->lignes = [];
        $this->colonnes = [];

        $this->lettres_suivantes = [];
        foreach ($hauteur == $largeur ? [$hauteur] : [$hauteur, $largeur] as $longueur) {
            $this->lettres_suivantes[$longueur] = [];
            foreach (mots_espaces($longueur, $hauteur == $largeur ? MAX_MOTS : MAX_MOTS/2) as $mots) {
                $mot = implode(" ", $mots);
                $ref = &$this->lettres_suivantes[$longueur];
                for ($i = 0; $i < $longueur; $i++) {
                    $lettre = $mot[$i];
                    if (!isset($ref[$lettre])) $ref[$lettre] = [];
                    $ref = &$ref[$lettre];
                }
                $ref = [];
            }
        }

        $this->positions = [];
        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++)
                $this->positions[] = [$x, $y];
        }
        $this->nb_positions = count($this->positions);

        mt_srand($id == "" ? null : crc32($id));
        $this->grilles = $this->generateur();
    }

    public function get_ligne($y, $largeur)
    {
        $ligne = "";
        for ($x = 0; $x < $largeur; $x++) 
            $ligne .= $this->grille[$y][$x];
        return $ligne;
    }

    public function get_colonne($x, $hauteur)
    {
        $colonne = "";
        for ($y = 0; $y < $hauteur; $y++)
            $colonne .= $this->grille[$y][$x];
        return $colonne;
    }

    public function generateur($i = 0)
    {
        global $dico;

        if ($i == $this->nb_positions) {
            yield $this;
            return;
        }

        [$x, $y] = $this->positions[$i];

        $lettres_suivantes_ligne = $this->lettres_suivantes[$this->largeur];
        for ($x2 = 0; $x2 < $x; $x2++)
            $lettres_suivantes_ligne = $lettres_suivantes_ligne[$this->grille[$y][$x2]];
        $lettres_suivantes_colonne = $this->lettres_suivantes[$this->hauteur];
        for ($y2 = 0; $y2 < $y; $y2++)
            $lettres_suivantes_colonne = $lettres_suivantes_colonne[$this->grille[$y2][$x]];
        $lettres_communes = array_intersect_key(
            $lettres_suivantes_ligne,
            $lettres_suivantes_colonne
        );
        uksort($lettres_communes, function ($a, $b) {
            return mt_rand(-1, 1);
        });

        foreach ($lettres_communes as $lettre => $_) {
            $this->grille[$y][$x] = $lettre;

            $mots = [];
            if ($x == $this->largeur - 1) $mots = explode(" ", $this->get_ligne($y, $this->largeur));
            else if ($lettre == " ") $mots = explode(" ", $this->get_ligne($y, $x));
            $mots = array_filter($mots, function($mot) {
                return strlen($mot) > 1;
            });
            if (count($mots) >= 1) {
                $dernier_mot = array_pop($mots);
                $this->lignes[$y] = $mots;
                if (in_array($dernier_mot, array_merge(...$this->lignes, ...$this->colonnes))) continue;
                else $this->lignes[$y][] = $dernier_mot;
            }


            if ($y == $this->hauteur - 1) {
                $mots = explode(" ", $this->get_colonne($x, $this->hauteur));
                foreach ($mots as $rang => $mot) {
                    if (strlen($mot) <= 1) continue;
                    if (in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue 2;
                    else $this->colonnes[$x][$rang] = $mot;
                }
            } else {
                $this->colonnes[$x] = [];
            }

            if ($i < $this->nb_positions) {
                yield from $this->generateur($i + 1);
            } else {
                yield $this;
            }
        }
    }

    public function hash()
    {
        $string = "";
        foreach ($this->grille as $ligne)
            $string .= implode("", $ligne);
        return hash('sha256', $string);
    }

    public function current(): mixed
    {
        return $this->grilles->current();
    }

    public function key(): mixed {
        return $this->grilles->key();
    }

    public function next(): void {
        $this->grilles->next();
    }

    public function rewind(): void {
        $this->grilles->rewind();
    }

    public function valid(): bool
    {
        return $this->grilles->valid();
    }
    
    public function offsetExists(mixed $offset): bool {
        return isset($this->grille[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->grille[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        $this->grille[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->grille[$offset]);
    }

}
