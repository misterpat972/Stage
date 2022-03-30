<?php
//*************************************** modification d'une chaine de caractère fichier wp-config ********************//
$local_path = getcwd(); // -------> récupère le répertoire de travail courant ----//
$prefixe = "sg_"; // -------> chaine de caractère à remplacer ----//
$new_prefixe = "essai_"; // -------> la nouvelle chaine de caractère ----//
$fichier_wp_config = $local_path . "/" . "wp-config.php"; // -------> fichier htaccess----//

// //******************************** ouvre et lit dans le fichier *****************************// 
// $current = file_get_contents($fichier_htaccess); // -------> recopie l'integralité du fichier dans la variable $current ----//
// $current = str_replace($prefixe, $new_prefixe, $current); // -------> remplace la chaine de caractère par la nouvelle ----//
// $update = file_put_contents($fichier_htaccess, $current); // -------> fait la réecriture dans le fichier htaccess ----//
// if (!$update) {
//     $_SESSION['erreur'] = 'problème de modification';
// }


//*************************************** modification d'une chaine de caractère fichier wp-config ********************//
$new_prefixe = $_POST['newprefixe']; // -------> la nouvelle chaine de caractère ----//
function get_wp_config_content($fichier_wp_config, $new_prefixe)
{
    $prefixe_line = "\$table_prefix ="; // -------> chaine de caractère à remplacer ----//; 
    
    try {
        //******************************** ouvre et lit dans le fichier *****************************// 
        $current = file($fichier_wp_config); // -------> recopie l'integralité du fichier dans la variable $current ----//
        $new_file = "";
        foreach ($current as $line) {
            if (strpos($line, $prefixe_line) !== false) {
                $prefixe = explode("'", $line); // -------> explode la chaine de caractère à partir du simple quote ----//
                $new_line = str_replace($prefixe[1], $new_prefixe, $line); // -------> remplace la chaine de caractère par la nouvelle a partir de l'explode simple quote ----//
                $line = $new_line; // -------> remplace la ligne par la nouvelle ligne ----//
            }
            $new_file .= $line; // -------> concatène la nouvelle ligne à la variable $new_file ----//
        }
        file_put_contents($fichier_wp_config, $new_file); // -------> fait la réecriture dans le fichie_wp_config ----//
    } catch (Exception $e) {
        echo 'Exception reçue : ',  $e->getMessage(), "\n";
    }
}
get_wp_config_content($fichier_wp_config, $new_prefixe);
