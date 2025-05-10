<?php

// Paramètres de largeur, hauteur, lignes et colonnes
$largeur  = isset($_GET['largeur']) ? (int)$_GET['largeur'] : 200;  // Valeur par défaut : 200
$hauteur  = isset($_GET['hauteur']) ? (int)$_GET['hauteur'] : 200;  // Valeur par défaut : 200
$lignes   = isset($_GET['lignes']) ? (int)$_GET['lignes'] : 8;      // Valeur par défaut : 4
$colonnes = isset($_GET['colonnes']) ? (int)$_GET['colonnes'] : 8;  // Valeur par défaut : 4
$bordure  = 6;

// Créer une image vide
$image = imagecreate($largeur, $hauteur);

// Couleurs
$blanc = imagecolorallocate($image, 255, 255, 255);
$noir  = imagecolorallocate($image, 0, 0, 0);

// Remplir l'image avec un fond blanc
imagefill($image, 0, 0, $blanc);

// Calculer la taille et la position des cases
$cote   = (int)min(($largeur - 2 * $bordure) / $colonnes, ($hauteur - 2 * $bordure) / $lignes);
$haut   = (int)(($hauteur - $lignes * $cote - 2 * $bordure) / 2) + $bordure;
$gauche = (int)(($largeur - $colonnes * $cote - 2 * $bordure) / 2) + $bordure;
$bas    = $haut + $lignes * $cote;
$droite = $gauche + $colonnes * $cote;

// Dessiner les bordures extérieures (3 pixels d'épaisseur)
imagesetthickness($image, $bordure);
imagerectangle($image, $gauche, $haut, $droite, $bas, $noir);

// Dessiner les lignes et colonnes internes (1 pixel d'épaisseur)
imagesetthickness($image, 2);
for ($x = $gauche; $x <= $droite; $x += $cote) {
    imageline($image, $x, $haut, $x, $bas, $noir); // Lignes verticales
}
for ($y = $haut; $y <= $bas; $y += $cote) {
    imageline($image, $gauche, $y, $droite, $y, $noir); // Lignes horizontales
}

// Noicir les cases
if (isset($_GET["grille"])) {
    include_once "Grille.php";

    $grille = new Grille($lignes, $colonnes);
    $id = htmlspecialchars($_GET["grille"]);
    $grille->load($id) || $grille->genere($id);

    for ($y = 0; $y < $lignes; $y++) {
        for ($x = 0; $x < $colonnes; $x++) {
            if ($grille[$y][$x] == CASE_NOIRE) {
                imagefilledrectangle($image, $gauche + $x * $cote, $haut + $y * $cote, $gauche + ($x + 1) * $cote, $haut + ($y + 1) * $cote, $noir);
            }
        }
    }
}

// Envoyer l'image au navigateur
header('Content-Type: image/png');
imagepng($image);

// Libérer la mémoire
imagedestroy($image);
?>