<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <title>Scraping</title>
</head>
<body>
<div class="container"> 
    <h1 class="mt-5" style="text-align: center;">Scraper la pages</h1> 
<form class="mt-5" method="POST" action="script.php">    
  <div class="mb-3">
    <label for="url" class="form-label">Url du site</label>
    <input type="text" class="form-control" name="url" id="url" aria-describedby="urlHelp">
    <div id="urlHelp" class="form-text">Ex: https://bikehike.org/</div>
  </div>
  <div class="mb-3 col-md-2">
    <label for="pages" class="form-label">Page Maxi à scrapper</label>
    <input type="text" class="form-control" name="pages" id="pages" aria-describedby="pagesHelp">
    <div id="pagesHelp" class="form-text">Ex: 5</div>
  </div>  
  <button name="execurl" type="submit" class="btn btn-primary">Infos page</button>
</form>
</div>  


<div class="container">
  <h2 class="mt-5" style="text-align: center;">Scraper l'article</h2>
<form action="scriptearticle.php" method="POST">
  <div class="mb-3">
    <label for="article" class="form-label">Lien de l'article</label>
    <input type="text" class="form-control" name="article" id="article" aria-describedby="articleHelp">
    <div id="articleHelp" class="form-text">Ex: https://bikehike.org/travel-tips-during-covid-2022/</div>
  </div>    

<div class="col md-4 text-center" >
<button type="submit" name="execarticle" class="btn btn-success mt-5 ">Scraper un article</button>
</div>
</form>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>