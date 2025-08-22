<?php
include_once "dico.php";


const ECART_TYPE = 5;


$randmax = mt_getrandmax() + 1;
function gaussienne($moyenne = 0, $ecartType = 1.0): float {
    global $randmax;

    $u = (mt_rand() + 1) / $randmax;
    $v = (mt_rand() + 1) / $randmax;

    $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);
    return $z * $ecartType + $moyenne;
}


class Grille implements ArrayAccess
{
    public $grille;
    public $hauteur;
    public $largeur;
    private $dico;
    private $positions;
    private $nb_positions;
    private $lignes = [];
    private $colonnes = [];
    public $valide = false;
    public $definitions = [];

    public function __construct($hauteur, $largeur)
    {
        $this->hauteur   = $hauteur;
        $this->largeur   = $largeur;
        $this->grille    = array_fill(0, $hauteur, array_fill(0, $largeur, ''));
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

    public function genere($id)
    {
        mt_srand(crc32($id));

        if (!isset($this->dico)) {
            $this->dico = mots_espaces(max($this->hauteur, $this->largeur));
        }

        if (!isset($this->positions)) {
            $this->positions = [];
            for ($y = 0; $y < $this->hauteur; $y++) {
                for ($x = 0; $x < $this->largeur; $x++)
                    $this->positions[] = [$x, $y];
            }
            $this->nb_positions = count($this->positions);
        }

        $grilles = $this->gen_grilles();
        $grilles->current();

        if ($grilles->valid()) {
            $this->definitions = [
                "horizontales" => [],
                "verticales" => []
            ];
            foreach($this->lignes as $y => $mots) {
                $this->definitions["horizontales"][$y] = [];
                foreach($mots as $mot) {
                    $definitions = $this->dico[strlen($mot)][$mot];
                    if (count($definitions)) {
                        $this->definitions["horizontales"][$y][] = $definitions[mt_rand(0, count($definitions) - 1)];
                    }
                }
            }
            foreach($this->colonnes as $x => $mots) {
                $this->definitions["verticales"][$x] = [];
                foreach($mots as $mot) {
                    $definitions = $this->dico[strlen($mot)][$mot];
                    if (count($definitions)) {
                        $this->definitions["verticales"][$x][] = $definitions[mt_rand(0, count($definitions) - 1)];
                    }
                }
            }
            $this->save($id);
            return true;
        } else {
            return false;
        }
    }

    public function gen_grilles($i = 0, $lettres_ligne = NULL)
    {
        [$x, $y] = $this->positions[$i];

        // Recherche de la prochaine lettre possible sur la case courante
        // en ligne
        if ($x == 0) {
            $lettres_ligne = $this->dico[$this->largeur];
        }

        // en colonne
        $lettres_colonne = $this->dico[$this->hauteur];
        for ($y2 = 0; $y2 < $y; $y2++) {
            $lettres_colonne = $lettres_colonne->branches[$this->grille[$y2][$x]];
        }
        $lettres_communes = array_intersect_key(
            $lettres_ligne->branches,
            $lettres_colonne->branches
        );
        foreach ($lettres_communes as $lettre => $_) {
            $lettres_communes[$lettre] = count($lettres_ligne->branches[$lettre]) * count($lettres_colonne->branches[$lettre]) * gaussienne(1, ECART_TYPE);
        }
        uksort($lettres_communes, function($a, $b) use ($lettres_communes) {
            return $lettres_communes[$b] <=> $lettres_communes[$a];
        });
        $lettres_communes = array_slice($lettres_communes, 0, 3);

        foreach ($lettres_communes as $lettre => $_) {
            $this->grille[$y][$x] = $lettre;

            // Omission des lettres isolées
            if ($lettre == CASE_NOIRE) {
                if (($y < 2 || $this->grille[$y - 2][$x] == CASE_NOIRE)
                    && ($y < 1 || $x == 0 || $this->grille[$y - 1][$x - 1] == CASE_NOIRE)
                    && ($y < 1 || $x + 1 >= $this->largeur || $this->grille[$y - 1][$x + 1] == CASE_NOIRE)
                ) continue;
                if ($y == $this->hauteur - 1
                    && ($x < 2 || $this[$y][$x - 2] == CASE_NOIRE)
                    && ($x < 1 || $this[$y - 1][$x - 1] == CASE_NOIRE)
                ) continue;
            } else if ($x == $this->largeur - 1 && $y == $this->hauteur - 1
             && $this[$y][$x - 1] == CASE_NOIRE
             && $this[$y-1][$x] == CASE_NOIRE
            ) continue;

            // Omission des doublons
            $mots = [];
            if ($x == $this->largeur - 1) $mots = explode(CASE_NOIRE, $this->get_ligne($y, $this->largeur));
            else if ($lettre == CASE_NOIRE) $mots = explode(CASE_NOIRE, $this->get_ligne($y, $x));
            else $mots = [];
            $this->lignes[$y] = array_filter($mots, function ($mot) {
                return strlen($mot) >= 2;
            });
            if (count($this->lignes[$y])) {
                $mot = array_pop($this->lignes[$y]);
                if (strlen($mot > 2) && in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue;
                else $this->lignes[$y][] = $mot;
            }

            if ($y == $this->hauteur - 1) {
                $mots = explode(CASE_NOIRE, $this->get_colonne($x, $this->hauteur));
                foreach ($mots as $rang => $mot) {
                    if (strlen($mot) < 2) continue;
                    if (strlen($mot > 2) && in_array($mot, array_merge(...$this->lignes, ...$this->colonnes))) continue 2;
                    else $this->colonnes[$x][$rang] = $mot;
                }
            } else {
                $this->colonnes[$x] = [];
            }

            if ($i < $this->nb_positions - 1) {
                yield from $this->gen_grilles($i + 1, $lettres_ligne->branches[$lettre]);
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

    public function __toString() {
        return implode(
            PHP_EOL,
            array_map(
                function ($ligne) {
                    return implode("", $ligne);
                },
                $this->grille
            )
        );
    }

    public function __serialize(): array {
        return [
            "grille" => $this->grille,
            "definitions" => $this->definitions
        ];
    }

    public function __unserialize(array $data): void {
        $this->grille = $data["grille"];
        $this->definitions = $data["definitions"];
    }

    public function save($id)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        session_id("$this->largeur,$this->hauteur,$id");
        session_start(["use_cookies" => false]);

        $_SESSION = serialize($this);
    }

    public function load($id)
    {
        session_id("$this->largeur,$this->hauteur,$id");
        session_start(["use_cookies" => false]);

        if (!isset($_SESSION["grille"])) {
            return false;
        }

        unserialize($_SESSION);

        return true;
    }


    public function offsetExists(mixed $offset): bool
    {
        return isset($this->grille[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->grille[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->grille[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->grille[$offset]);
    }
}
