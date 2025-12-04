<?php


class Trie implements ArrayAccess, IteratorAggregate, Countable
{
    public array $branches = [];
    private $nb_branches = 0;

    public function arraySet($cles, $valeur)
    {
        $cle = $cles[0];
        $this->nb_branches++;
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            $this->branches[$cle] = $valeur;
        } else {
            if (!isset($this->branches[$cle]))
                $this->branches[$cle] = new Trie();
            $this->branches[$cle]->arraySet($cles, $valeur);
        }
    }

    public function arrayExists($cles)
    {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            return isset($this->branches[$cle]);
        } else {
            return isset($this->branches[$cle]) && $this->branches[$cle]->arrayExists($cles);
        }
    }

    public function &arrayGet($cles)
    {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            return $this->branches[$cle];
        } else {
            return $this->branches[$cle]->arrayGet($cles);
        }
    }

    public function arrayUnset($cles)
    {
        $cle = $cles[0];
        $cles = array_slice($cles, 1);
        if ($cles == []) {
            unset($this->branches[$cle]);
            $this->nb_branches--;
        } else {
            $this->branches[$cle]->arrayUnset($cles);
            $this->nb_branches--;
            if (count($this->branches[$cle]) == 0) {
                unset($this->branches[$cle]);
            }
        }
    }

    // ArrayAccess
    public function offsetSet($array, $valeur): void
    {
        if (is_string($array)) {
            $array = str_split($array);
        }
        $this->arraySet($array, $valeur);
    }

    public function offsetExists($array): bool
    {
        if (is_string($array)) {
            $array = str_split($array);
        }
        return $this->arrayExists($array);
    }

    public function &offsetGet($array): mixed
    {
        if (is_string($array)) {
            $array = str_split($array);
        }
        return $this->arrayGet($array);
    }

    public function offsetUnset($array): void
    {
        if (is_string($array)) {
            $array = str_split($array);
        }
        $this->arrayUnset($array);
    }

    // IteratorAggregate
    public function getIterator(): Traversable
    {
        foreach ($this->branches as $cle => $branche) {
            if ($branche instanceof Trie) {
                foreach ($branche->getIterator() as $sous_cles => $feuille) {
                    yield array_merge([$cle], $sous_cles) => $feuille;
                }
            } else {
                yield [$cle] => $branche;
            }
        }
    }

    // Countable
    public function count(): int
    {
        return $this->nb_branches;
    }
}
