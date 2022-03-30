<?php
$local_path = getcwd(); // -------> récupère le répertoire de travail courant ----//
$fichier_sql = $local_path . "/" . "dev_stargate.sql"; // -------> fichier sql actuel .sql----//
$dbname = $_POST['nombase']; // -------> nouveau nom de la base de données ----//
$host = $_POST['nomhost'];  // -------> nouveau nom de l'hôte ----//    
$user = $_POST['userbase']; // -------> nouveau nom de l'utilisateur ----//
$password = empty($_POST['passwordbase']) ? "" : $_POST['passwordbase']; // -------> nouveau mot de passe ----//

// *********************** connexion et execution bdd *********************//
if (!empty($_POST)) {

    $mysqli = new mysqli($host, $user, $password, $dbname); // -------> connexion à la base de données ----//
    if ($mysqli->connect_errno) {
        echo "Échec lors de la connexion à MySQL  : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; // -------> si erreur de connexion ----//
    }

    $sql = file_get_contents($fichier_sql);
    if (!$mysqli->multi_query($sql)) {
        echo "Échec lors de l'exécution de la requête : (" . $mysqli->errno . ") " . $mysqli->error; // -------> si erreur d'exécution ----//
    }

    $mysqli->close(); // -------> ferme la connexion à la base de données ----//
}






function sql_exucute($fichier_sql, $dbname, $host, $user, $password)
{
    $mysqli = new mysqli($host, $user, $password, $dbname); // -------> connexion à la base de données ----//
    if ($mysqli->connect_errno) {
        echo "Échec lors de la connexion à MySQL  : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; // -------> si erreur de connexion ----//
    }

    $sql = file_get_contents($fichier_sql);
    if (!$mysqli->multi_query($sql)) {
        echo "Échec lors de l'exécution de la requête : (" . $mysqli->errno . ") " . $mysqli->error; // -------> si erreur d'exécution ----//
    }

    $mysqli->close(); // -------> ferme la connexion à la base de données ----//
}
sql_exucute($fichier_sql, $dbname, $host, $user, $password);