<?php
//*************************************** modification d'une chaine de caractère fichier htaccess ********************//
 $local_path = getcwd(); // -------> récupère le répertoire de travail courant ----//
// $prefixe = "dev/stargate/"; // -------> chaine de caractère à remplacer ----//
// $new_prefixe = ""; // -------> la nouvelle chaine de caractère ----//
 $fichier_htaccess = $local_path . "/" . ".htaccess"; // -------> fichier htaccess----//

// //******************************** ouvre et lit dans le fichier *****************************// 
// $current = file_get_contents($fichier_htaccess); // -------> recopie l'integralité du fichier dans la variable $current ----//
// $current = str_replace($prefixe, $new_prefixe, $current); // -------> remplace la chaine de caractère par la nouvelle ----//
// $update = file_put_contents($fichier_htaccess, $current); // -------> fait la réecriture dans le fichier htaccess ----//
// if (!$update) {
//     $_SESSION['erreur'] = 'problème de modification';
// }


function get_htaccess_content($fichier_htaccess)
{   $prefixe = "dev/stargate/";
    $new_prefixe = "";

    $htaccess_content = file_get_contents($fichier_htaccess);
    $current = str_replace($prefixe, $new_prefixe, $htaccess_content);
    $update = file_put_contents($fichier_htaccess, $current);     

    if (!$update) {
        $_SESSION['erreur'] = 'problème de modification';
    }
}

get_htaccess_content($fichier_htaccess);
