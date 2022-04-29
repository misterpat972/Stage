<?php
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

//******************************* mes déclarations de variables *******************************/
// -------> fichier htaccess----//
$fichier_htaccess = $local_path . "/" . ".htaccess"; 
// -------> fichier wp-config----//
$fichier_wp_config = $local_path . "/" . "wp-config.php"; 
// -------> fichier sql----//
$fichier_sql = $local_path . "/" . "dev_stargate.sql"; 
// -------> la nouvelle chaine de caractère dans le fichier .sql ----// 
$new_prefixe = $_POST['newprefixe'];
// -------> nouveau nom du fichier sql ----// 
$nouveau_nom_sql = $_POST['newname_sql']; 

$dbname = $_POST['nombase']; // -------> nom de la base de données ----//
$host = $_POST['nomhost'];  // -------> nom de l'hôte ----//    
$user = $_POST['userbase']; // -------> nom de l'utilisateur ----//
$password = empty($_POST['passwordbase']) ? "" : $_POST['passwordbase']; // -------> nouveau mot de passe ----//
$define1 = "define( 'DB_NAME',";
$define2 = "define( 'DB_USER',";
$define3 = "define( 'DB_PASSWORD',";
$define4 = "define( 'DB_HOST',";

//******************************* mes fonctions *******************************/
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
echo "STEP1: copyFTP <br>";
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
echo "STEP2: get_htaccess <br>";
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
	if($prefixe[1]!=""){
		return($prefixe[1]);
	}else{
		return(FALSE);
	}
}
$prefixe = get_wp_config_content($fichier_wp_config, $new_prefixe);
echo "STEP3: get_wp_config <br>";
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
if($prefixe!=FALSE){
	get_sql_content($fichier_sql, $prefixe, $new_prefixe, $nouveau_nom_sql); // -------> appel de la fonction get_sql_content() ----//
}
echo "STEP4: get_get_sql <br>";
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
$fichier_sql = $local_path."/".$nouveau_nom_sql;
create_database($fichier_sql, $dbname, $host, $user, $password); // -------> appel de la fonction create_database ----//
echo "STEP5: create_database <br>";


//*************************************** mise à jour du fichier wp_config_bdd ********************//
/*----------------update_wp_config_bdd_const()----------------------
 à pour but de mettre à jour le fichier wp-config_bdd.php avec les nouvelles valeurs de la base de données
*/
function update_wp_config_bdd_const($fichier_wp_config, $new_param, $define_old_param)
{
    try {
        //******************************** ouvre et lit dans le fichier *****************************// 
        $current = file($fichier_wp_config); // -------> recopie l'integralité du fichier dans la variable $current ----//
        $new_file = "";
        foreach ($current as $line) {
            if (strpos($line,  $define_old_param) !== false) {               
                $dpname = explode("'", $line); // -------> explode la chaine de caractère à partir du simple quote ----//
                $new_line = str_replace($dpname[3], $new_param, $line); // -------> remplace la chaine de caractère par la nouvelle a partir de l'explode simple quote ----//
                $line = $new_line; // -------> remplace la ligne par la nouvelle ligne ----//
            }
            $new_file .= $line; // -------> concatène la nouvelle ligne à la variable $new_file ----//
        }
        // -------> fait la réecriture dans le fichie_wp_config ----//
        file_put_contents($fichier_wp_config, $new_file);
    } catch (Exception $e) {
        echo 'Exception reçue : ',  $e->getMessage(), "\n";
    }
    if ($dpname[3] != "") {
        return ($dpname[3]);
    } else {
        return (FALSE);
    }
}
 // -------> appel de la fonction update_wp_config_bdd_const() ----//
update_wp_config_bdd_const($fichier_wp_config, $dbname, $define1);
update_wp_config_bdd_const($fichier_wp_config, $user, $define2);
update_wp_config_bdd_const($fichier_wp_config, $password, $define3);
update_wp_config_bdd_const($fichier_wp_config, $host, $define4);
echo "STEP6: update_wp_config_bdd_const <br>";  


//***************************************si les fichiers nommés existe ils seront supprimés définitivement ********************//
if (file_exists($nouveau_nom_sql)) {
	unlink($nouveau_nom_sql);
	echo "SUPPR : ".$nouveau_nom_sql." <br>";
}
if (file_exists("dev_stargate.sql")) {
	unlink("dev_stargate.sql");
	echo "SUPPR : dev_stargate.sql <br>";
}
if (file_exists("index2.php")) {
	unlink("index2.php");
	echo "SUPPR : index2.php <br>";
}
if (file_exists($fichier)) {
	unlink($fichier);
	echo "SUPPR : ".$fichier." <br>";
}
echo "FIN PROCESS DUPPLICATION SITE :)";