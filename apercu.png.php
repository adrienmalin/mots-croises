<?php

$largeur  = isset($_GET['largeur']) ? (int)$_GET['largeur'] : 200;
$hauteur  = isset($_GET['hauteur']) ? (int)$_GET['hauteur'] : 200;
$lignes   = isset($_GET['lignes']) ? (int)$_GET['lignes'] : 8;
$colonnes = isset($_GET['colonnes']) ? (int)$_GET['colonnes'] : 8;

$image = imagecreatetruecolor($largeur, $hauteur);
imagesavealpha($image, true);
$blanc = imagecolorallocate($image, 255, 255, 255);
$noir  = imagecolorallocate($image, 0, 0, 0);
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

// Calculer la taille et la position des cases
$min_dimension = min($largeur, $hauteur);
if ($min_dimension <= 16) {
    $bordure_exterieure = 0;
} else if ($min_dimension < 32) {
    $bordure_exterieure = 1;
} else if ($min_dimension <= 96) {
    $bordure_exterieure = 2;
} else if ($min_dimension <= 600) {
    $bordure_exterieure = 3;
} else {
    $bordure_exterieure = 6;
}
$cote   = (int)min(($largeur - 2 * $bordure_exterieure) / $colonnes, ($hauteur - 2 * $bordure_exterieure) / $lignes);
if ($cote < 3) {
    $bordure_interieure = 0;
} else if ($min_dimension < 600) {
    $bordure_interieure = 1;
} else {
    $bordure_interieure = 2;
}
$haut   = (int)(($hauteur - $lignes * $cote - 2 * $bordure_exterieure) / 2) + (int)$bordure_exterieure;
$gauche = (int)(($largeur - $colonnes * $cote - 2 * $bordure_exterieure) / 2) + $bordure_exterieure;
$bas    = $haut + $lignes * $cote - $bordure_interieure;
$droite = $gauche + $colonnes * $cote - $bordure_interieure;

// Remplir l'image avec un fond transparent
imagefill($image, 0, 0, $transparent);

// Dessiner les bordures extérieures (3 pixels d'épaisseur)
$marge1 = ceil($bordure_exterieure / 2);
$marge2 = floor($bordure_exterieure / 2);
imagesetthickness($image, $bordure_exterieure);
imagerectangle($image, $gauche - $marge1, $haut - $marge1, $droite + $marge2 -  1, $bas + $marge2 - 1, $noir);
imagefilledrectangle($image, $gauche, $haut, $droite - 1, $bas - 1, $blanc);

// Dessiner les lignes et colonnes internes (1 pixel d'épaisseur)
if ($bordure_interieure >= 1) {
    imagesetthickness($image, $bordure_interieure);
    for ($x = $gauche + $cote - ceil($bordure_interieure / 2); $x < $droite; $x += $cote) {
        imageline($image, $x, $haut, $x, $bas, $noir); // Lignes verticales
    }
    for ($y = $haut + $cote - ceil($bordure_interieure / 2); $y < $bas; $y += $cote) {
        imageline($image, $gauche, $y, $droite, $y, $noir); // Lignes horizontales
    }
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
                imagefilledrectangle($image, $gauche + $x * $cote, $haut + $y * $cote, $gauche + ($x + 1) * $cote - 1, $haut + ($y + 1) * $cote - 1, $noir);
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