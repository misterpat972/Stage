<?php
/*
fichier : script2.php sert à se connecter au serveur FTP distant, modifier le fichier .htaccess, modifier le fichier wp-config.php, modifier le fichier .SQL et par la suite creer une base de donnée.
*/

//******************************** connexion FTP *****************************/ 
$ftp_server = "reblochon.o2switch.net";
$ftp_user_name = "djiboutea";
$ftp_user_pass = 'vn35R4QxNbcq';
$port = 21;

//on se connecte au serveur FTP
$conn_id = ftp_connect($ftp_server, $port);
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
ftp_pasv($conn_id, true);
// charge le dossier courant d'exécution du script comme repertoire par défaut
$local_path = getcwd();
$distant_path = "/public_html/webosity.fr/stargate";
// $_SERVER['script_filename] est le chemin relatif ou est exécuté le script
$fichier = $_SERVER['SCRIPT_FILENAME'];
//******************************** fin connexion FTP *****************************/


/*----------------FONCTION copyFTP()----------------------
Le but de cette fonction est de recopier l'intégralité d'un répertoire distant
et de tout ses fichiers et sous dossiers de manière récursive
$distant_path => dossier distant à partir duquel démarre la recopie
$local_path => dossier local ou sont recopiés les fichiers et dossiers
$conn_id => instance de connexion FTP
*/
function copyFTP($distant_path, $local_path, $conn_id)
{
    //********************** on liste les fichiers présents (ici à la racine) dans un tableau ****************/
    $remote_elements = ftp_mlsd($conn_id,  $distant_path);
    if (!empty($remote_elements)) {
        //************************ foreach des éléments ok *********************/
        foreach ($remote_elements as $elt) {
            //******************** si fichier on le recupère ***************************/
            if ($elt['type'] == 'file') {
                ftp_get($conn_id, $local_path . '/' . $elt['name'], $distant_path . "/" . $elt['name']);
                //******************** si dossier simple on le récupère *********************/    
            } elseif ($elt['type'] == 'dir') {
                mkdir($local_path . '/' . $elt['name'], 0777, true) or die('dossier déjà existant');
                copyFTP($distant_path . '/' . $elt['name'], $local_path . '/' . $elt['name'], $conn_id);
                //******* si fichier Parent ou commun on traite pas ******/

            }
        }
    }
}
copyFTP($distant_path, $local_path, $conn_id);



//******************************* mes déclarations de variables *******************************/
$fichier_htaccess = $local_path . "/" . "htaccess"; // -------> fichier htaccess----//

$fichier_wp_config = $local_path . "/" . "wp-config.php"; // -------> fichier wp-config----//

$fichier_sql = $local_path . "/" . "dev_stargate.sql"; // -------> fichier sql----//
$prefixe_bdd = $_POST['préfixe']; // -------> chaine de caractère à remplacer dans le fichier .sql ----//
$new_prefixe = $_POST['newprefixe']; // -------> la nouvelle chaine de caractère dans le fichier .sql ----// 
$nouveau_nom_sql = $_POST['newname_sql']; // -------> nouveau nom du fichier sql ----//

$dbname = $_POST['nombase']; // -------> nom de la base de données ----//
$host = $_POST['nomhost'];  // -------> nom de l'hôte ----//    
$user = $_POST['userbase']; // -------> nom de l'utilisateur ----//
$password = empty($_POST['passwordbase']) ? "" : $_POST['passwordbase']; // -------> nouveau mot de passe ----//





//*************************************** Fonction modification d'une chaine de caractère fichier .htaccess ********************//

function get_htaccess_content($fichier_htaccess)
{
    $prefixe = "dev/stargate/"; // -------> chaine de caractère à remplacer ----//
    $new_prefixe = ""; // -------> la nouvelle chaine de caractère ----//

    try {
        $htaccess_content = file_get_contents($fichier_htaccess); // -------> recopie l'integralité du fichier dans la variable $htacces_content ----//
        $current = str_replace($prefixe, $new_prefixe, $htaccess_content); // -------> remplace la chaine de caractère précédente par la nouvelle chaine de caractère ----//
        $update = file_put_contents($fichier_htaccess, $current); // -------> écrit la nouvelle chaine de caractère dans le fichier htaccess ----//      
        if (!$update) {
            throw new Exception('problème de modification du fichier htaccess'); // -------> si problème de modification du fichier htaccess, on lance une exception ----//
        }
    } catch (Exception $e) {
        echo 'Exception reçue : ',  $e->getMessage(), "\n";
    }
}
get_htaccess_content($fichier_htaccess); // -------> appel de la fonction htaccess ----//








//*************************************** Fonction modification d'une chaine de caractère fichier wp-config ********************//


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





//*************************************** Fonction modification d'une chaine de caractère fichier .sql ********************//

function get_sql_content($fichier_sql, $prefixe_bdd, $prefixe_choisi, $nouveau_nom_sql)
{
    $sql_content = file_get_contents($fichier_sql); // -------> recopie l'integralité du fichier dans la variable $sql_content ----//
    $current = str_replace($prefixe_bdd, $prefixe_choisi, $sql_content); // -------> remplace la chaine de caractère par la nouvelle ----//
    $update = file_put_contents($fichier_sql, $current); // -------> fait la réecriture dans le fichier htaccess ----//    

    if ($update) {
        rename($fichier_sql, $nouveau_nom_sql); // -------> renomme le fichier sql ----//
        //$_SESSION['message'] = 'modification effectuée avec succcès';
        //header('location:../index2.php');
    } else {
        $_SESSION['erreur'] = 'problème de modification';
    }
}
get_sql_content($fichier_sql, $prefixe_bdd, $new_prefixe, $nouveau_nom_sql); // -------> appel de la fonction get_sql_content() ----//






//*************************************** Fonction excution de la création de la base de donnée ********************//

function create_database($fichier_sql, $dbname, $host, $user, $password)
{
    $mysqli = new mysqli($host, $user, $password, $dbname); // -------> connexion à la base de données ----//
    if ($mysqli->connect_errno) {
        echo "Échec lors de la connexion à MySQL  : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; // -------> si erreur de connexion ----//
    }

    $sql = file_get_contents($fichier_sql); // -------> recopie l'integralité du fichier dans la variable $sql ----//
    if (!$mysqli->multi_query($sql)) {
        echo "Échec lors de l'exécution de la requête : (" . $mysqli->errno . ") " . $mysqli->error; // -------> si erreur d'exécution ----//
    }

    $mysqli->close(); // -------> ferme la connexion à la base de données ----//
}
create_database($fichier_sql, $dbname, $host, $user, $password); // -------> appel de la fonction sql_exec() ----//




//***************** condition qui permet de supprimer le script en cours une fois exécuté **************/

//*
if (file_exists($fichier)) {
    unlink($fichier);
}
//*/ ---> sert à commenter la ligne si on veut garder le script en cours après exécution --------//