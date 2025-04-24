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

def melange(iterable):
    liste = list(iterable)
    return sample(liste, len(liste))

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

        self.mots_de_n_lettres = {
            hauteur: set(mots_de_n_lettres(hauteur)),
            largeur: set(mots_de_n_lettres(largeur)),
        }
        self.mots_par_position = defaultdict(lambda: defaultdict(list))
        for nb_lettres in (self.largeur, self.hauteur):
            for mot in self.mots_de_n_lettres[nb_lettres]:
                for i, lettre in enumerate(mot):
                    self.mots_par_position[nb_lettres][(i, lettre)].append(mot)

        self.generations = self.genere()
        try:
            next(self)
        except StopIteration:
            pass

    def __iter__(self):
        return self

    def __next__(self):
        return next(self.generations)

    def genere(self):
        self.lignes_restantes = set(range(self.hauteur))
        self.colonnes_restantes = set(range(self.largeur))

        l = 0
        self.lignes_restantes.remove(l)
        for mot_lig in self.mots_de_n_lettres[self.largeur]:
            if ' ' in mot_lig:
                continue
            self.ligne[l] = mot_lig
            yield from self.trouve_une_colonne(l, mot_lig)
        #self.ligne[l] = "." * self.largeur
        #self.lignes_restantes.add(l)

    def trouve_une_colonne(self, l, mot_lig):
        #print((len(self.colonnes_restantes) + len(self.lignes_restantes)) / (self.largeur + self.hauteur))
        #print(self)
        c = min(
            self.colonnes_restantes,
            key=lambda c: len(self.mots_par_position[self.hauteur][(l, mot_lig[c])])
        )
        if not self.mots_par_position[self.hauteur][(l, mot_lig[c])]:
            return
        colonne = self.colonne[c]
        self.colonnes_restantes.remove(c)
        pattern = compile(rf"\b{colonne}\b")
        for mot_col in self.mots_par_position[self.hauteur][(l, mot_lig[c])]:
            if colonne == mot_col or ('.' in colonne and pattern.match(mot_col)):
                self.colonne[c] = mot_col
                if self.lignes_restantes:
                    yield from self.trouve_une_ligne(c, mot_col)
                elif self.colonnes_restantes:
                    yield from self.trouve_une_colonne(l, mot_lig)
                else:
                    yield self
        self.colonne[c] = colonne
        self.colonnes_restantes.add(c)

    def trouve_une_ligne(self, c, mot_col):
        l = min(
            self.lignes_restantes,
            key=lambda l: len(self.mots_par_position[self.largeur][(c, mot_col[l])])
        )
        if not self.mots_par_position[self.largeur][(c, mot_col[l])]:
            return
        ligne = self.ligne[l]
        self.lignes_restantes.remove(l)
        pattern = compile(rf"\b{ligne}\b")
        for mot_lig in self.mots_par_position[self.largeur][(c, mot_col[l])]:
            if ligne == mot_lig or ('.' in ligne and pattern.match(mot_lig)):
                self.ligne[l] = mot_lig
                if self.colonnes_restantes:
                    yield from self.trouve_une_colonne(l, mot_lig)
                elif self.lignes_restantes:
                    yield from self.trouve_une_ligne(c, mot_col)
                else:
                    yield self
        self.ligne[l] = ligne
        self.lignes_restantes.add(l)

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

    with Timer():
        print(Grille(5, 5))
