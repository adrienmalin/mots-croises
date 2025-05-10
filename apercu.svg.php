<?php

$lignes   = isset($_GET['lignes']) ? (int)$_GET['lignes'] : 8;
$colonnes = isset($_GET['colonnes']) ? (int)$_GET['colonnes'] : 8;

$bordure = 2;
$marge = $bordure / 2;
$cote = 20;

// Dimensions du SVG
$width = $colonnes * $cote; // Largeur proportionnelle au nombre de colonnes
$height = $lignes * $cote;  // Hauteur proportionnelle au nombre de lignes
$rectRadius = 7;        // Rayon des coins arrondis du rectangle

// Création du document XML
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

// Élément SVG principal
$svg = $doc->createElement('svg');
$svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
$svg->setAttribute('viewBox', -$marge . " " . -$marge . " " . ($width + $bordure) . " " . ($height + $bordure));
$svg->setAttribute('width', $width + $bordure);
$svg->setAttribute('height', $height + $bordure);
$doc->appendChild($svg);

// Rectangle arrondi
$rect = $doc->createElement('rect');
$rect->setAttribute('x', 0);
$rect->setAttribute('y', 0);
$rect->setAttribute('width', $width);
$rect->setAttribute('height', $height);
$rect->setAttribute('rx', $rectRadius);
$rect->setAttribute('ry', $rectRadius);
$rect->setAttribute('fill', 'white');
$rect->setAttribute('stroke', 'black');
$rect->setAttribute('stroke-width', $bordure);
$svg->appendChild($rect);

// Lignes verticales
for ($i = 1; $i < $colonnes; $i++) {
    $x = $i * $cote;
    $line = $doc->createElement('line');
    $line->setAttribute('x1', $x);
    $line->setAttribute('y1', $marge);
    $line->setAttribute('x2', $x);
    $line->setAttribute('y2', $height - $marge);
    $line->setAttribute('stroke', '#000');
    $line->setAttribute('stroke-width', 1);
    $svg->appendChild($line);
}

// Lignes horizontales
for ($i = 1; $i < $lignes; $i++) {
    $y = $i * $cote;
    $line = $doc->createElement('line');
    $line->setAttribute('x1', $marge);
    $line->setAttribute('y1', $y);
    $line->setAttribute('x2', $width - $marge);
    $line->setAttribute('y2', $y);
    $line->setAttribute('stroke', '#000');
    $line->setAttribute('stroke-width', 1);
    $svg->appendChild($line);
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
                $rect = $doc->createElement('rect');
                $rect->setAttribute('x', $x * $cote);
                $rect->setAttribute('y', $y * $cote);
                $rect->setAttribute('width', $cote);
                $rect->setAttribute('height', $cote);
                $rect->setAttribute('fill', 'black');
                $svg->appendChild($rect);
            }
        }
    }
}

header('Content-Type: image/svg+xml');
echo $doc->saveXML();