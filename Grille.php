<?php

include_once "dico.php";


function melanger_cles($tableau)
{
    uksort($tableau, function ($a, $b) {
        return mt_rand(-1, 1);
    });
    return $tableau;
}


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
        $lettres_communes = melanger_cles(array_intersect_key(
            $lettres_suivantes_ligne,
            $lettres_suivantes_colonne
        ));

        foreach ($lettres_communes as $lettre => $_) {
            $this->grille[$y][$x] = $lettre;

            $this->lignes[$y] = [];
            if ($x == $this->largeur - 1) {
                foreach (explode(" ", $this->get_ligne($y, $this->largeur)) as $rang => $mot) {
                    if (strlen($mot) == 1) continue;
                    if (in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue 2;
                    $this->lignes[$y][$rang] = $mot;
                }
            }
            $this->colonnes[$x] = [];
            if ($y == $this->hauteur - 1) {
                foreach (explode(" ", $this->get_colonne($x, $this->hauteur)) as $rang => $mot) {
                    if (strlen($mot) == 1) continue;
                    if (in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue 2;
                    $this->colonnes[$x][$rang] = $mot;
                }
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
