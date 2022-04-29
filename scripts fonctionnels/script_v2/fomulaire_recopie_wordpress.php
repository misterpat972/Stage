<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Envoi du script sur le serveur cible</title>
</head>

<body>
    <?php if (empty($_POST["sub_recopie"])) : ?>
        <div class="container">
            <!---------------------------------- FORMULAIRE ----------------------------------->
            <h1 class="mt-5" style="text-align: center;">Connexion et envoi du script sur le serveur cible</h1>
            <form class="mt-5 " action="" method="post">
                <!---Connexion au serveur FTP cible -->
                <div class="form-group p-2">
                    <label for="host">Host Serveur FTP cible</label>
                    <!---Adresse du serveur FTP cible -->
                    <input type="text" class="form-control" id="host" name="host" placeholder="Serveur FTP cible" required>
                    <!--- exemple adresse serveur FTP cible -->
                    <small id="emailHelp" class="form-text text-muted">Exemple: ftp.exemple.com</small>
                </div>
                <div class="form-group p-2">
                    <label for="login">Login FTP cible</label>
                    <!---Login FTP cible -->
                    <input type="text" class="form-control" id="login" name="login" placeholder="Login" required>
                    <!--- exemple Login FTP cible -->
                    <small id="emailHelp" class="form-text text-muted">Exemple: Votre login FTP cible</small>
                </div>
                <div class="form-group p-2">
                    <label for="password">Mot de passe FTP cible</label>
                    <!---Mot de passe FTP cible -->
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                    <!--- exemple Mot de passe FTP cible -->
                    <small id="passwordHelp" class="form-text text-muted">Exemple: Vous devez utiliser le même mot de passe que celui de votre serveur FTP cible.</small>
                </div>
                <div class="form-group p-2">
                    <label for="port">Port du FTP cible</label>
                    <!---Port du FTP cible -->
                    <input type="text" class="form-control" id="port" name="port" placeholder="Port" required>
                    <!---exemple 21 -->
                    <small id="emailHelp" class="form-text text-muted">Exemple: Port par défaut : 21</small>
                </div>
                <div class="form-group p-2">
                    <label for="chemin_distant">Chemin distant du futur site</label>
                    <!---Port du FTP cible -->
                    <input type="text" class="form-control" id="chemin_distant" name="chemin_distant" placeholder="chemin_distant" required>
                    <!---exemple 21 -->
                    <small id="emailHelp" class="form-text text-muted">Exemple: /public_html/besthairstyle.org/...</small>
                </div>
                <hr style="background-color: green; height: 5px;">
                <h3 class="mt-5" style="text-align: center;">Informations wp-config && bdd</h3>
                <div class="form-group p-2">
                    <!-- label préfixe  -->
                    <label for="newprefixe">Nouveau préfixe wp-config && Bdd</label>
                    <!--- input du nouveau préfixe -->
                    <input type="text" class="form-control" name="newprefixe" id="newprefixe" placeholder="Nouveau préfixe">
                    <!-- exemple wp_ -->
                    <small id="prefixeHelp" class="form-text text-muted">Exemple : wp_</small>
                </div>
                <div class="form-group p-2">
                    <label for="nomhost">Host Base de donnée</label>
                    <!--- input du nom de l'host -->
                    <input type="text" class="form-control" name="nomhost" id="nomhost" placeholder="Host Base de donnée">
                    <!--- exemple localhost -->
                    <small id="nomhostHelp" class="form-text text-muted">Exemple : localhost</small>
                </div>
                <div class="form-group p-2">
                    <div class="form-group p-2">
                        <label for="nombase">Nom de la Base de donnée</label>
                        <!--- input du nom de la base de donnée -->
                        <input type="text" class="form-control" name="nombase" id="nombase" placeholder="Nom de la Base de donnée">
                        <!-- exemple djibou -->
                        <small id="nombaseHelp" class="form-text text-muted">Exemple : djibou</small>
                    </div>
                    <div class="form-group p-2">
                        <label for="userbase">Nom Utilisateur</label>
                        <!--- input du nom de l'utilisateur -->
                        <input type="text" class="form-control" name="userbase" id="userbase" placeholder="Nom Utilisateur">
                        <!-- exemple root -->
                        <small id="userbaseHelp" class="form-text text-muted">Exemple : root</small>
                    </div>
                    <div class="form-group p-2">
                        <label for="passwordbase">Mot de passe</label>
                        <input type="text" class="form-control" name="passwordbase" id="passwordbase" placeholder="Mot de passe">
                    </div>
                    <div class="form-group p-2">
                        <button type="submit" class="btn btn-primary mt-2 p-2" name="sub_recopie" id="sub_recopie" value="sub_ok">Executer</button>
                    </div>
            </form>
        </div>

    <?php elseif (isset($_POST['sub_recopie']) && $_POST['sub_recopie'] == "sub_ok") : ?>
        <!-- connexion FTP au serveur cible -->
        <?php $ftp = ftp_connect($_POST['host'], $_POST['port']); ?>
        <!-- connexion au serveur FTP cible -->
        <?php $login = ftp_login($ftp, $_POST['login'], $_POST['password']); ?>
        <!-- connexion au serveur FTP cible -->
        <?php if (!$ftp || !$login) : ?>
            <div class="alert alert-danger mt-5" role="alert">
                <h4 class="alert-heading">Erreur de connexion au serveur FTP cible</h4>
                <p>Vérifiez que vous avez bien rempli les champs correctement</p>
                <hr>
                <p class="mb-0">Vous pouvez retourner en arrière en cliquant sur le bouton ci-dessous</p>
                <a href="fomulaire_recopie_wordpress.php" type="button" class="btn btn-primary">
                    Retour
                </a>
            </div>
        <?php else : ?>
            <div class="alert alert-success mt-5" role="alert">
                <h4 class="alert-heading">Connexion au serveur FTP cible réussie</h4>
                <p>Vous pouvez continuer l'installation en cliquant sur le bouton ci-dessous</p>
            </div>

            <!--si connexion réussie on envoi le script recopie_wordpress_webosity.php sur le serveur cible -->
            <?php
            if ($ftp || $login) {
                $file = 'recopie_wordpress_webosity.php';
                $remote_file = $_POST['chemin_distant'] . '/' . $file;
                function ftp_upload($file, $remote_file, $ftp)
                {
                    if (file_exists($file)) {
                        if (ftp_put($ftp, $remote_file, $file, FTP_ASCII)) {
                            echo "uploaded réussi de $file.";
                                //envois des données du formulaire vers le serveur cible en cURL
                                $data = array(                                                                       
                                    'newprefixe' => $_POST['newprefixe'],
                                    'nomhost' => $_POST['nomhost'],
                                    'nombase' => $_POST['nombase'],
                                    'userbase' => $_POST['userbase'],
                                    'passwordbase' => $_POST['passwordbase'],
                                    'sub_recopie' => 'u3bwE6C4nC6C'
                                );
                                $url = 'http://' . $_POST['host'] . ':' . $_POST['port'] . $_POST['chemin_distant'] . '/' . $file;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $server_output = curl_exec($ch);
                                curl_close($ch);
                                //affichage du résultat de l'envoi
                                echo $server_output;


                        } else {
                            echo "upload échoué de $file.";
                        }
                    } else {
                        echo "$file n'existe pas.";
                    }
                }
                ftp_upload($file, $remote_file, $ftp);
            }

            ?>

        <?php endif; ?>
    <?php endif; ?>
</body>