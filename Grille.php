<?php
include_once "dico.php";


class Grille implements ArrayAccess {
    public $grille;
    public $hauteur;
    public $largeur;
    private $lettres_suivantes;
    private $positions;
    private $nb_positions;
    public $lignes = [];
    public $colonnes = [];

    public function __construct($hauteur, $largeur)
    {
        $this->hauteur   = $hauteur;
        $this->largeur   = $largeur;
        $this->grille    = array_fill(0, $hauteur, array_fill(0, $largeur, ''));

        $this->positions = [];
        for ($y = 0; $y < $hauteur; $y++) {
            for ($x = 0; $x < $largeur; $x++)
                $this->positions[] = [$x, $y];
        }
        $this->nb_positions = count($this->positions);

        $this->lettres_suivantes = tries(max($hauteur, $largeur));
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

    public function gen_grilles($i = 0, $lettres_suivantes_ligne = NULL)
    {
        [$x, $y] = $this->positions[$i];

        if ($x == 0) {
            $lettres_suivantes_ligne = $this->lettres_suivantes[$this->largeur];
        }

        $lettres_suivantes_colonne = $this->lettres_suivantes[$this->hauteur];
        for ($y2 = 0; $y2 < $y; $y2++)
            $lettres_suivantes_colonne = $lettres_suivantes_colonne->noeud[$this->grille[$y2][$x]];
        $lettres_communes = array_intersect(
            array_keys($lettres_suivantes_ligne->noeud),
            array_keys($lettres_suivantes_colonne->noeud)
        );
        foreach($lettres_communes as $lettre => $_) {
            $lettres_communes[$lettre] = count($lettres_suivantes_colonne[$lettre]) * count($lettres_suivantes_colonne[$lettre]);
        }
        usort($lettres_communes, function ($a, $b) {
            return mt_rand(-1, 1);
        });

        foreach ($lettres_communes as $lettre) {
            $this->grille[$y][$x] = $lettre;

            $mots = [];
            if ($x == $this->largeur - 1) $mots = explode(" ", $this->get_ligne($y, $this->largeur));
            else if ($lettre == " ") $mots = explode(" ", $this->get_ligne($y, $x));
            else $mots = [];
            $this->lignes[$y] = array_filter($mots, function($mot) {
                return strlen($mot) >= 2;
            });
            if (count($this->lignes[$y])) {
                $mot = array_pop($this->lignes[$y]);
                if (strlen($mot > 2) && in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue;
                else $this->lignes[$y][] = $mot;
            }

            if ($y == $this->hauteur - 1) {
                $mots = explode(" ", $this->get_colonne($x, $this->hauteur));
                foreach ($mots as $rang => $mot) {
                    if (strlen($mot) < 2) continue;
                    if (strlen($mot > 2) && in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue 2;
                    else $this->colonnes[$x][$rang] = $mot;
                }
            } else {
                $this->colonnes[$x] = [];
            }

            if ($i < $this->nb_positions - 1) {
                yield from $this->gen_grilles($i + 1, $lettres_suivantes_ligne->noeud[$lettre]);
            } else {
                yield $this;
            }
        }
    }

    public function genere() {
        session_regenerate_id();
        $id = session_id();
        mt_srand(crc32($id));

        $grilles = $this->gen_grilles();
        $grilles->current();

        if ($grilles->valid()) {
            $this->save();
            return $id;
        } else {
            return false;
        }
    }

    public function hash()
    {
        $string = "";
        foreach ($this->grille as $ligne)
            $string .= implode("", $ligne);
        return hash('sha256', $string);
    }

    public function save() {
        session_start(["use_cookies" => false]);

        $_SESSION["$this->largeur,$this->hauteur"] = implode(
            "",
            array_map(
                function ($ligne) {
                    return implode("", $ligne);
                },
                $this->grille
            )
        );
    }

    public function load($id) {
        session_id($id);
        session_start(["use_cookies" => false]);

        if (!isset($_SESSION["$this->largeur,$this->hauteur"])) {
            return false;
        }

        foreach (str_split($_SESSION["$this->largeur,$this->hauteur"], $this->largeur) as $y => $ligne) {
            foreach(str_split($ligne) as $x => $lettre) {
                $this->grille[$y][$x] = $lettre;
            }
        }
        
        for ($y = 0; $y < $this->hauteur; $y++) {
            $mots = explode(" ", $this->get_ligne($y, $this->largeur));
            $this->lignes[$y] = array_filter($mots, function($mot) {
                return strlen($mot) >= 2;
            });
        }
        
        for ($x = 0; $x < $this->largeur; $x++) {
            $mots = explode(" ", $this->get_colonne($x, $this->hauteur));
            $this->colonnes[$x] = array_filter($mots, function($mot) {
                return strlen($mot) >= 2;
            });
        }

        return true;
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
