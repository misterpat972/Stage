<?php
//$url_ref = "https://bikehike.org/riding-and-health/";
//$nb_pages = 5;
$url_ref = $_POST['url'];
$nb_pages = $_POST['pages'] ?: "";


function page($url_ref)
{
  $url = $url_ref;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  $resp = curl_exec($curl);
  curl_close($curl);

  $html = new DOMDocument();
  @$html->loadHTML($resp);
  $xpath = new DOMXPath($html);
  $title = $xpath->query('//h2[@class="entry-title"] | //h2[@class="entry-title"]/a/@href');
  //$liens = $xpath->query('//h2[@class="entry-title"]/a/@href'); 
  //$pageNext = $xpath->query('//a[@class="page-numbers"]/@href');

  // titre //
  $page = 1;
  $z = 1;
  echo '<h2>page: ' . $page . '</h2>';
  foreach ($title as $titre) {
    echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
  }
  // lien des pages suivantes //
  //echo "<h4>page suivante:</h4> " . $pageNext->item(0)->nodeValue;
}
page($url_ref);

if ($nb_pages) {
  // pages suivantes //
  $page = 1;
  while ($page != $nb_pages) {
    $page++;
    $url =  $url_ref . "page/" . $page . "/";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $resp = curl_exec($curl);
    curl_close($curl);

    $html = new DOMDocument();
    @$html->loadHTML($resp);
    $xpath = new DOMXPath($html);
    $title = $xpath->query('//h2[@class="entry-title"] | //h2[@class="entry-title"]/a/@href');
    //$liens = $xpath->query('//h2[@class="entry-title"]/a/@href');
    
   // $pageNext = $xpath->query('//a[@class="page-numbers"]/@href');

    $z = 1;
    // titre //
    echo '<h2>page ' . $page . '</h2>';
    foreach ($title as $titre) {
      echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
    }  
  }
}




if(!empty($_POST['execarticle'])){
    if(!empty($_POST['article'])){
          echo $_POST['article'];
        $url_ref = $_POST['article'];
        $curl = curl_init($url_ref);
        curl_setopt($curl, CURLOPT_URL, $url_ref);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $resp = curl_exec($curl);
      
        curl_close($curl);

        $html = new DOMDocument();
        @$html->loadHTML($resp);
        $xpath = new DOMXPath($html);
        $title = $xpath->query('//@title');
        $content = $xpath->query('//div[@class="entry-content"]');
        $date = $xpath->query('//meta[@property="og:description"]');
        $author = $xpath->query('//h1[@class="entry-title"]');
        $image = $xpath->query('//div[@class="post-thumbnail"]/img/@src');       
        $content = $xpath->query('//div[@class="entry-content"]');
        
        $z = 1;
        // titre //
        echo '<h2>article</h2>';
        foreach ($title as $titre) {
          echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
        }
    }  

}







// $i = 1;
// foreach($liens as $lien){
//   echo  'lien:'. $i++ .'<br>'. $lien->nodeValue . '<br><br>';
// }


//  preg_match('/(https?:\/\/|www.)[a-zA-Z0-9_\.\/?=&-]+/', $url, $match);





// permet la récupération des liens des produits //
// foreach($html->getElementsByTagName('a') as $link) {
//     echo $link->getAttribute('href') . '<br>';
// }


// permet de connaitre le path de chaque item // 
// for ($i = 0; $i < $liens->length; $i++) {
//   $lien = $liens->item($i);
//   $url = $lien->getNodePath('href');
//   echo "$url<br>";
// }










?>


<!---------script qui permet de savoir combien d'articles qu'il y a dans la page 
<script>
var selector = 'div.article > a';
var list = document.querySelectorAll(selector), i;

for (i = 0; i < list.length; ++i) {
  console.log(list[i].getAttribute('href'));
}
</script>  ---------->