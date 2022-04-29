<?php session_start();

if (isset($_POST['sub_recopie']) && $_POST['sub_recopie'] =='u3bwE6C4nC6C'){
	//******************************** connexion FTP *****************************/ 
	$ftp_server = "reblochon.o2switch.net";
	$ftp_user_name = "djiboutea";
	$ftp_user_pass = 'vn35R4QxNbcq';
	$port = 21;

	//on se connecte au serveur FTP
	$conn_id = ftp_connect($ftp_server, $port);
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
	ftp_pasv($conn_id, true);
	ftp_set_option($conn_id, FTP_TIMEOUT_SEC, 5000);
	if(!$conn_id || !$login_result) {
		echo "Connexion impossible au serveur FTP!";
		exit;
	}else{
		echo "Connexion au serveur FTP réussie!</br>";
	}
	// charge le dossier courant d'exécution du script comme repertoire par défaut
	$local_path = getcwd();
	$distant_path = "/public_html/webosity.fr/stargate";
	// $_SERVER['script_filename] est le chemin relatif ou est exécuté le script
	$fichier = $_SERVER['SCRIPT_FILENAME'];
	//******************************** fin connexion FTP *****************************/

	//******************************* mes déclarations de variables *******************************/


	//-----------> variables permettant la création du fichier dump.sql <-----------//
	//$distant_path = "/public_html/webosity.fr/stargate";
	$script_local_path = "script_dump.php";
	//-----------> variables fonction get_dump <-----------//
	$dump_serveur = $distant_path . "/" . "dump.sql";
	$script_dump = $distant_path . "/" . "script_dump.php";
	$dump_local = "dump.sql";
	//-----------> variables fonction send_script <-----------//
	$script_distant_path = $distant_path . "/" . "script_dump.php";
	$dump_distant_path = $distant_path . "/" . "dump.sql";



	// -------> fichier htaccess----//
	$fichier_htaccess = $local_path . "/" . ".htaccess"; 
	// -------> fichier wp-config----//
	$fichier_wp_config = $local_path . "/" . "wp-config.php"; 
	// -------> fichier sql new FTP <----//
	// *$fichier_sql = $local_path . "/" . "dev_stargate.sql";* 
	$fichier_sql = $local_path . "/" . "dump.sql"; 
	// -------> la nouvelle chaine de caractère dans le fichier .sql ----// 
	$new_prefixe = $_POST['newprefixe'];
	// -------> nouveau nom du fichier sql ----// 
	$nouveau_nom_sql = 'dump'; 
	
	// -------> nom de la base de données ----//
	$dbname = $_POST['nombase']; 
	// -------> nom de l'hôte ----//
	$host = $_POST['nomhost']; 
	// -------> nom de l'utilisateur ----//     
	$user = $_POST['userbase'];
	// -------> nouveau mot de passe ----// 
	$password = empty($_POST['passwordbase']) ? "" : $_POST['passwordbase']; 
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
					mkdir($local_path . '/' . $elt['name'], 0777, true);
					copyFTP($distant_path . '/' . $elt['name'], $local_path . '/' . $elt['name'], $conn_id);
					//******* si fichier Parent ou commun on traite pas ******/
				}
			}
		}
	}
	copyFTP($distant_path, $local_path, $conn_id);
	echo "STEP1: copyFTP <br>";

	////////////////// CREATION DU FICHIER DUMP.SQL ///////////////////////
	$contenu_dump_script_dump = '<?php
	$database = "djiboutea_stargate";
	$user = "djiboutea_root";
	$pass = "VGqQTuKwMBIz";
	$host = "localhost";
		$dir = dirname(__FILE__) . "/dump.sql";    
	$result = exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$dir} 2>&1");
	?>';
	$fichier_script = fopen("script_dump.php", "w");
	$fichier_script2 = fwrite($fichier_script, $contenu_dump_script_dump);
	fclose($fichier_script);

	/////////////////// DECLARATION DES FONCTIONS ///////////////////////
	//-----------> fonction permettant de uploader le fichier script_dump.php sur le serveur modèle <-----------//
	function send_script($conn_id, $script_distant_path, $script_local_path)
	{

		if (file_exists($script_local_path)) {
			$upload = ftp_put($conn_id, $script_distant_path, $script_local_path, FTP_BINARY);
			if (!$upload) {
				echo "Upload échoué!";
				exit;
			} else {
				echo "Upload réussi! </br>";
			}
			unlink("script_dump.php");
		} else {
			echo "Le fichier n'existe pas";
		}
	}
	send_script($conn_id, $script_distant_path, $script_local_path);
	echo "STEP2: send_script <br>";


	//----------------> fonction de récupération dump.sql  <-----------------//
	//////////////////////////////////////////////////////////////////////////////////
	// cette fonction est utilisée récupérer la dump.sql créé sur le serveur modèle //  
	//////////////////////////////////////////////////////////////////////////////////
	function get_dump($conn_id, $dump_serveur, $dump_local, $script_dump)
	{
		//on récupère le dump.sql du serveur modèle //
		$result = ftp_get($conn_id, $dump_local, $dump_serveur, FTP_BINARY);
		if ($result) {
			echo "Succès: get_dump à bien récupéré le dump.sql du serveur modèle <br>";
			//suppression du dump.sql du serveur modèle //
			$result = ftp_delete($conn_id, $dump_serveur);
			if ($result) {
				echo "Succès: get_dump à bien supprimé le dump.sql du serveur modèle <br>";
			} else {
				echo "Erreur: get_dump n'a pas pu supprimer le dump.sql du serveur modèle <br>";
			}// une fois le dump.sql supprimé du serveur modèle, on supprime le script_dump.php du serveur modèle //
			$result = ftp_delete($conn_id, $script_dump);
			if ($result) {
				echo "Succès: get_dump à bien supprimé script_dump.php du serveur modèle <br>";
			} else {
				echo "Erreur: get_dump n'a pas pu supprimer le script_dump.php du serveur modèle <br>";
			}
		} else {

			echo "Erreur: la récupération n'a pas pu s'effectuer <br>";
		}
	}	 	
	


	//-----------> fonction d'execution du script_dump.php <-----------//
	////////////////////////////////////////////////////////////////////////////////////////
	// cette fonction est utilisée pour exécuter le script_dump.php sur le serveur modèle //  
	////////////////////////////////////////////////////////////////////////////////////////
	function exec_script()
	{
		$url = "https://webosity.fr/stargate/script_dump.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);		       
		curl_close($ch);		
	}
	exec_script();
	echo "STEP3: exec_script <br>";
	//-----------> on tchèk si le fichier dump.sql existe <-----------//
	$return_dump_exist = ftp_size($conn_id, $dump_distant_path);
	//-----------> si le fichier dump.sql existe, on le télécharge sur le nouveau serveur <-----------//
	switch ($return_dump_exist) {
	case -1:
		echo "Le fichier dump.sql n'existe pas";
		break;
	case 0:
		echo "Le fichier dump.sql est vide";
		break;
	default:
		echo "Le fichier dump.sql existe";
		get_dump($conn_id, $dump_serveur, $dump_local, $script_dump);
		break;
	}
	echo "STEP4: get_dump <br>";
	

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
	echo "STEP5: get_htaccess <br>";

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
	echo "STEP6: get_wp_config <br>";

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
	echo "STEP7: get_get_sql <br>";

	//*************************************** Fonction excution de la création de la base de donnée ********************//
	function create_database($fichier_sql, $dbname, $host, $user, $password)
	{
		$mysqli = new mysqli($host, $user, $password, $dbname); // -------> connexion à la base de données ----//
		if ($mysqli->connect_errno) {
			echo "Échec lors de la connexion à MySQL  : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; // -------> si erreur de connexion ----//
		}

		$sql = file_get_contents($fichier_sql); // -------> recopie l'integralité du fichier dans la variable $sql ----//
		$mysqli->multi_query($sql);
		//La requete sur MySQL se fait de manière asynchrone
		// En utilisant un DO...WHILE, on impose à PHP d'attendre l'execution complète de la requete sur le moteur SQL
		// SINON, on ne peut pas faire de traitement supplémentaire dans la BDD type UPDATE,INSERT tant que le multi_query n'a pas fini son execution
		do {
			/* store the result set in PHP */
			if ($result = $mysqli->store_result()) {
				while ($row = $result->fetch_row()) {
					printf("%s\n", $row[0]);
				}
			}
			/* print divider */
			if ($mysqli->more_results()) {
				printf("-----------------\n");
			}
		} while ($mysqli->next_result());
		//MAINTENANT, on est sur que le multi_query a fini son execution	
		$mysqli->close(); // -------> ferme la connexion à la base de données ----//
	}
	$fichier_sql = $local_path."/".$nouveau_nom_sql;
	create_database($fichier_sql, $dbname, $host, $user, $password); // -------> appel de la fonction create_database ----//
	echo "STEP8: create_database <br>";

	//*************************************** Fonction qui permet de get l'url en cours ********************//

	function url(){
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
			$url = "https://";
		} else {
			$url = "http://";
		}
		$url .= $_SERVER['HTTP_HOST'];
		$url .= dirname($_SERVER['PHP_SELF']);
		return $url;
	}
	$url = url();

	//*************************************** Fonction qui permet l'ajout de l'url dans la base de donnée dans la table option ********************//
	function tableoption($host, $user, $password, $dbname, $new_prefixe, $url){
		$mysqli = new mysqli($host, $user, $password, $dbname); // -------> connexion à la base de données ----//
			if ($mysqli->connect_errno) {
				echo "Échec lors de la connexion à MySQL  : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error; // -------> si erreur de connexion ----//
			}
		
		$query_UPD = "UPDATE ".$new_prefixe."options SET option_value = '".$url."' WHERE option_name = 'siteurl'";
		$result = $mysqli->query($query_UPD); // -------> récupération de la base de données courante ----//
		if (!$result) {
			echo "Échec sur UPDATE |".$query_UPD."| : (" . $mysqli->errno . ") " . $mysqli->error; // -------> si erreur de récupération ----//
		}
		$query_UPD = "UPDATE ".$new_prefixe."options SET option_value = '".$url."' WHERE option_name = 'home'";
		$result = $mysqli->query($query_UPD); // -------> récupération de la base de données courante ----//
		if (!$result) {
			echo "Échec sur UPDATE |".$query_UPD."| : (" . $mysqli->errno . ") " . $mysqli->error; // -------> si erreur de récupération ----//
		}
	}
	tableoption($host, $user, $password, $dbname, $new_prefixe, $url);

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
	echo "STEP9: update_wp_config_bdd_const <br>";  


	//***************************************si les fichiers nommés existe ils seront supprimés définitivement ********************//
	if (file_exists($nouveau_nom_sql)) {
		unlink($nouveau_nom_sql);
		echo "SUPPR : ".$nouveau_nom_sql." <br>";
	}
	if (file_exists("dump.sql")) {
		unlink("dump.sql");
		echo "SUPPR : dump.sql <br>";
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
}
?>