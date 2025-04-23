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

mots = defaultdict(set)
for mot in dico:
    mots[len(mot)].add(mot)


def mots_de_n_lettres(n):
    for mot in mots[n]:
        yield mot
    # for mot in mots[n-1]:
    #     yield f"{mot} "
    #     yield f" {mot}"
    for i in range(2, ceil(n / 2)):
        for mot1, mot2 in product(mots[i], mots_de_n_lettres(n - i - 1)):
            yield f"{mot1} {mot2}"
            yield f"{mot2} {mot1}"
        # for mot1, mot2 in product(mots[i], mots_de_n_lettres(n - i - 2)):
        #     yield f" {mot1} {mot2}"
        #     yield f"{mot2} {mot1} "
        # for mot1, mot2 in product(mots[i-1], mots_de_n_lettres(n - i - 1)):
        #     yield f" {mot1} {mot2}"
        #     yield f"{mot2} {mot1} "


class Ligne:
    def __init__(self, grille):
        self.grille = grille

    def __getitem__(self, n):
        return "".join(self.grille[n][:n])

    def __setitem__(self, n, mot):
        self.grille[n] = list(mot)


class Colonne:
    def __init__(self, grille):
        self.grille = grille

    def __getitem__(self, n):
        return "".join(ligne[n] for ligne in self.grille[:n+1])

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

        self.mot_commencant_par = defaultdict(lambda: defaultdict(list))
        for dimension in (hauteur,) if hauteur == largeur else (hauteur, largeur):
            for mot in mots_de_n_lettres(dimension):
                for i in range(dimension+1):
                    self.mot_commencant_par[dimension][mot[:i]].append(mot)

        self.grilles = self.genere_grilles()
        next(self.grilles)

    def __iter__(self):
        return self

    def __next__(self):
        return next(self.grilles)

    def genere_grilles(self):
        print(f"Grille({self.hauteur}, {self.largeur})")
        yield from self.trouve_une_ligne(0)

    def trouve_une_ligne(self, i):
        for mot in self.mot_commencant_par[self.largeur][self.ligne[i]]:
            self.ligne[i] = mot
            if i < self.largeur:
                yield from self.trouve_une_colonne(i)
            elif i + 1 < self.hauteur:
                yield from self.trouve_une_ligne(i + 1)
            else:
                yield self

    def trouve_une_colonne(self, i):
        for mot in self.mot_commencant_par[self.hauteur][self.colonne[i]]:
            self.colonne[i] = mot
            if i + 1 < self.hauteur:
                yield from self.trouve_une_ligne(i + 1)
            elif i + 1 < self.largeur:
                yield from self.trouve_une_colonne(i + 1)
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

    for i in range(2, 14):
        with Timer():
            print(Grille(i, i))
        with Timer():
            print(Grille(i + 1, i))
        with Timer():
            print(Grille(i, i + 1))
