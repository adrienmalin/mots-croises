<?php


class Trie implements ArrayAccess, IteratorAggregate, Countable {
    public array $branches = [];
    private $nb_branches = 0;

    // ArrayAccess
    public function offsetSet($cles, $valeur): void {
        $this->nb_branches++;
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            $this->branches[$cle] = $valeur;
        } else {
            if (!isset($this->branches[$cle])) $this->branches[$cle] = new Trie();
            $this->branches[$cle]->offsetSet($cles, $valeur);
        }
    }

    public function offsetExists($cles): bool {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            return isset($this->branches[$cle]);
        } else {
            return isset($this->branches[$cle]) && $this->branches[$cle]->offsetExists($cles);
        }
    }

    public function &offsetGet($cles): mixed {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            return $this->branches[$cle];
        } else {
            return $this->branches[$cle]->offsetGet($cles);
        }
    }

    public function offsetUnset($cles): void {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            unset($this->branches[$cle]);
            $this->nb_branches--;
        } else {
            $this->branches[$cle]->offsetUnset($cles);
            $this->nb_branches--;
            if (count($this->branches[$cle]) == 0) {
                unset($this->branches[$cle]);
            }
        }
    }

    // IteratorAggregate
    public function getIterator(): Traversable {
        foreach ($this->branches as $cle => $branche) {
            if ($branche instanceof Trie) {
                foreach($branche as $sous_cles => $feuille) {
                    yield array_merge([$cle], $sous_cles) => $feuille;
                }
            } else {
                yield [$cle] => $branche;
            }
        }
    }

    // Countable
    public function count(): int {
        return $this->nb_branches;
    }
}
