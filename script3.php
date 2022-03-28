<?php
include('../index2.php');

$local_path = getcwd();// -------> récupère le répertoire de travail courant ----//
$prefixe_bdd = $_POST['préfixe']; // -------> chaine de caractère à remplacer ----//
$prefixe_choisi = $_POST['newpréfixe']; // -------> la nouvelle chaine de caractère ----//
$nouveau_nom_sql = $_POST['newname_sql']; // -------> actualisation du .sql----//
$fichier_sql = $local_path . "/" . "dev_stargate.sql"; // -------> fichier sql actuel .sql----//

//******************************** ouvre et lit dans le fichier *****************************/ 
$current = file_get_contents($fichier_sql);
$current = str_replace($prefixe_bdd, $prefixe_choisi, $current); // -------> remplace la chaine de caractère par une autre ----//
$update = file_put_contents($fichier_sql, $current); // -------> fait la réecriture dans le fichier ----//
if ($update) {
    rename($fichier_sql, $nouveau_nom_sql); // -------> si ok on renome le fihier .sql----//
}

$_SESSION['message'] = 'modification effectuée avec succcès';
header('location:../index2.php');
