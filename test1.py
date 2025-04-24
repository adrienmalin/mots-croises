import csv
from re import compile, match
from random import choice, sample, randrange
from collections import defaultdict
from math import ceil
from itertools import product, chain


dico = defaultdict(list)
with open("dico.csv", "r", encoding="utf-8") as fichier:
    for mot, definition in csv.reader(fichier, delimiter="\t"):
        if not mot.startswith("#"):
            dico[mot].append(definition)

mots_de_n_lettres = defaultdict(set)
for mot in dico:
    mots_de_n_lettres[len(mot)].add(mot)


def mots_espaces(n):
    for mot in mots_de_n_lettres[n]:
        yield mot
    # for mot in mots_de_n_lettres[n-1]:
    #     yield f"{mot} "
    #     yield f" {mot}"
    for i in range(1, ceil(n / 2)):
        for mot1, mot2 in product(mots_de_n_lettres[i], mots_espaces(n - i - 1)):
            yield f"{mot1} {mot2}"
            yield f"{mot2} {mot1}"
        # for mot1, mot2 in product(mots_de_n_lettres[i], mots_espaces(n - i - 2)):
        #     yield f" {mot1} {mot2}"
        #     yield f"{mot2} {mot1} "
        # for mot1, mot2 in product(mots_de_n_lettres[i-1], mots_espaces(n - i - 1)):
        #     yield f" {mot1} {mot2}"
        #     yield f"{mot2} {mot1} "


class Ligne:
    def __init__(self, grille):
        self.grille = grille

    def __getitem__(self, n):
        return "".join(self.grille[n])

    def __setitem__(self, n, mot):
        self.grille[n] = list(mot)


class Colonne:
    def __init__(self, grille):
        self.grille = grille

    def __getitem__(self, n):
        return "".join(ligne[n] for ligne in self.grille)

    def __setitem__(self, n, mot):
        for i, char in enumerate(mot):
            self.grille[i][n] = char


class Grille:
    def __init__(self, hauteur, largeur):
        self.hauteur = hauteur
        self.largeur = largeur
        self.grille = [["." for _ in range(largeur)] for _ in range(hauteur)]
        self.ligne = Ligne(self.grille)
        self.colonne = Colonne(self.grille)

        self.mots_commencant_par = defaultdict(lambda: defaultdict(list))
        for dimension in (hauteur,) if hauteur == largeur else (hauteur, largeur):
            for mot in mots_espaces(dimension):
                for i in range(dimension+1):
                    self.mots_commencant_par[dimension][mot[:i]].append(mot)

        self.grilles = self.genere_grilles()
        next(self.grilles)

    def __iter__(self):
        return self

    def __next__(self):
        return next(self.grilles)

    def genere_grilles(self):
        print(f"Grille({self.hauteur}, {self.largeur})")
        yield from self.trouve_une_ligne(0)

    def trouve_une_ligne(self, l):
        for mot in self.mots_commencant_par[self.largeur][self.ligne[l][:l]]:
            self.ligne[l] = mot
            if all(
                self.colonne[c][:l+1] in self.mots_commencant_par[self.hauteur]
                for c in range(l, self.largeur)
            ):
                if l < self.largeur:
                    yield from self.trouve_une_colonne(l)
                elif l + 1 < self.hauteur:
                    yield from self.trouve_une_ligne(l + 1)
                else:
                    yield self

    def trouve_une_colonne(self, c):
        for mot in self.mots_commencant_par[self.hauteur][self.colonne[c][:c+1]]:
            self.colonne[c] = mot
            if all(
                self.ligne[l][:c+1] in self.mots_commencant_par[self.largeur]
                for l in range(c, self.largeur)
            ):
                if c + 1 < self.hauteur:
                    yield from self.trouve_une_ligne(c + 1)
                elif c + 1 < self.largeur:
                    yield from self.trouve_une_colonne(c + 1)
                else:
                    yield self

    def __str__(self):
        return (
            "   "
            + " ".join(chr(65 + i) for i in range(self.largeur))
            + "\n"
            + "\n".join(
                f"{i + 1:2} " + " ".join(ligne) for i, ligne in enumerate(self.grille)
            )
        )

    def __repr__(self):
        return self.__str__()


if __name__ == "__main__":
    import time

    class Timer:
        def __enter__(self):
            self.start = time.time()
            return self

        def __exit__(self, *exc_info):
            end = time.time()
            print(f"Execution time: {end - self.start:.2f} seconds")

    for n in range(2, 14):
        with Timer():
            print(Grille(n, n))
