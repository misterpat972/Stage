<?php session_start() ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <title>Lien FTP</title>
</head>

<body>

  <?php if (!empty($_SESSION['message'])) : ?>
    <div id="alert" class="alert alert-success" role="alert">
      <?php echo $_SESSION['message'];
      unset($_SESSION['message'])
      ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['erreur'])) : ?>
    <div id="erreur" class="alert alert-danger" role="alert">
      <?php echo $_SESSION['erreur'];
      unset($_SESSION['erreur'])
      ?>
    </div>
  <?php endif; ?>



  <div class="container">
    <h1 class="mt-5" style="text-align: center;">Connexion PhpMyadmin ALLO QUOI</h1>
    <form class="mt-5 " action="script2.php" method="post">
      <div class="form-group p-2">
        <label for="newprefixe">Nouveau préfixe wp-config && Bdd</label>
        <input type="text" class="form-control" name="newprefixe" id="newprefixe" placeholder="Nouveau préfixe">
      </div>
      <div class="form-group p-2">
        <label for="newname_sql">Nouveau nom .Sql</label>
        <input type="text" class="form-control" name="newname_sql" id="newname_sql" placeholder="Nouveau nom du fichier .sql">
      </div>
      <div class="form-group p-2">
        <label for="nomhost">Host Base de donnée</label>
        <input type="text" class="form-control" name="nomhost" id="nomhost" placeholder="Host Base de donnée">
      </div>
      <div class="form-group p-2">
        <div class="form-group p-2">
          <label for="nombase">Nom de la Base de donnée</label>
          <input type="text" class="form-control" name="nombase" id="nombase" placeholder="Nom de la Base de donnée">
        </div>
        <div class="form-group p-2">
          <label for="userbase">Nom Utilisateur</label>
          <input type="text" class="form-control" name="userbase" id="userbase" placeholder="Nom Utilisateur">
        </div>
        <div class="form-group p-2">
          <label for="passwordbase">Mot de passe</label>
          <input type="text" class="form-control" name="passwordbase" id="passwordbase" placeholder="Mot de passe">
        </div>
        <div class="form-group p-2">
          <button type="submit"  class="btn btn-primary mt-2 p-2">Executer</button>
        </div>
    </form>
  </div>
</body>


<script>
  setInterval(function() {
    document.getElementById("alert").style.display = "none";
  }, 2000);

  setInterval(function() {
    document.getElementById("erreur").style.display = "none";
  }, 2000);
</script>

</html>