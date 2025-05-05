<?php


class Trie implements ArrayAccess, Countable //, Iterator
{
    public array $noeud = [];
    private array $cles_en_cours = [];
    private mixed $valeur_en_cours;
    private $marcheur;
    private $nb_branches = 0;

    // ArrayAccess
    public function offsetExists($cles): bool {
        if (!count($cles)) {
            return false;
        }
        $cle = array_shift($cles);
        if (count($cles)) {
            return $this->noeud[$cle]->offsetExists($cles);
        } else {
            return isset($this->noeud[$cles]);
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

    public function offsetSet($cles, $valeur): void {
        if (!count($cles)) {
            throw new \OutOfBoundsException("Liste de clés vide.");
            return;
        }
        $cle = array_shift($cles);
        if (!isset($this->noeud[$cle])) $this->noeud[$cle] = new Trie();
        if (count($cles)) {
            $this->noeud[$cle]->offsetSet($cles, $valeur);
        } else {
            $this->noeud[$cle] = $valeur;
        }
        $this->nb_branches++;
    }

    public function offsetUnset($cles): void {
        if ($this->offsetExists(cles)) {
            $cle = array_shift($cles);
            if (count($cles)) {
                $this->noeud[$cle]->offsetUnset($cles);
            } else {
                unset($this->noeud[$cle]);
            }
            $this->nb_branches--;
        }
    }

    // Countable
    public function count(): int {
        return $this->nb_branches;
    }

/*
    // Iterator
    public function marcheurs(): generator {
        foreach ($this->noeud as $cle => $branche) {
            if ($branche instanceof Trie) {
                foreach($branche as $sous_cles => $feuille) {
                    $this->cles_en_cours = [$cle, ...$sous_cles];
                    yield $feuille;
                }
            } else {
                $this->cles_en_cours = [$cle];
                yield $branche;
            }
        }
    }

    public function current(): mixed {
        return $this->marcheur->current();
    }

    public function key(): array {
        return $this->cles_en_cours;
    }

    public function next(): void {
        $this->marcheur->next();
    }

    public function rewind(): void {
        $this->marcheur = $this->marcheurs();
    }

    public function valid(): bool {
        return $this->marcheur->valid();
    }
*/
}