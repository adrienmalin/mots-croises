<?php


class Trie implements ArrayAccess, IteratorAggregate, Countable
{
    public array $noeud = [];
    private $nb_branches = 0;

    public function offsetSet($cles, $valeur): void {
        if (!count($cles)) {
            throw new \OutOfBoundsException("Liste de clés vide.");
        }
        $cle = array_shift($cles);
        if (!isset($this->noeud[$cle])) $this->noeud[$cle] = new Trie();
        $this->nb_branches++;
        if (count($cles)) {
            $this->noeud[$cle]->offsetSet($cles, $valeur);
        } else {
            $this->noeud[$cle] = $valeur;
        }
    }

    // ArrayAccess
    public function offsetExists($cles): bool {
        if (!count($cles)) {
            return false;
        }
        $cle = array_shift($cles);
        if (count($cles)) {
            return $this->noeud[$cle]->offsetExists($cles);
        } else {
            return isset($this->noeud[$cles[0]]);
        }
    }

    public function offsetGet($cles): mixed {
        if (!count($cles)) {
            throw new \OutOfBoundsException("Liste de clés vide.");
        }
        $cle = array_shift($cles);
        if (!isset($this->noeud[$cle])) $this->noeud[$cle] = new Trie();
        if (count($cles)) {
            return $this->noeud[$cle]->offsetGet($cles);
        } else {
            return $this->noeud[$cle];
        }
    }

    public function offsetUnset($cles): void {
        if ($this->offsetExists($cles)) {
            $cle = array_shift($cles);
            $this->nb_branches--;
            if (count($cles)) {
                $this->noeud[$cle]->offsetUnset($cles);
                if (count($this->noeud[$cle]) == 0) {
                    unset($this->noeud[$cle]);
                }
            } else {
                unset($this->noeud[$cle]);
            }
        }
    }

    // IteratorAggregate
    public function getIterator(): Generator {
        foreach ($this->noeud as $cle => $branche) {
            if ($branche instanceof Trie) {
                foreach($branche as $sous_cles => $feuille) {
                    yield [$cle, ...$sous_cles] => $feuille;
                }
            } else {
                yield $cle => $branche;
            }
        }
    }

    // Countable
    public function count(): int {
        return $this->nb_branches;
    }
}


