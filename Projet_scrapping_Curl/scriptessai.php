<?php
//$url_ref = "https://bikehike.org/riding-and-health/";
//$nb_pages = 5;
// if (!empty($_POST['execurl'])) {
//     $url_ref = $_POST['url'];
//     $nb_pages = $_POST['pages'] ?: "";


//     function page($url_ref)
//     {
//         $url = $url_ref;
//         $curl = curl_init($url);
//         curl_setopt($curl, CURLOPT_URL, $url);
//         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//         $resp = curl_exec($curl);
//         curl_close($curl);

//         $html = new DOMDocument();
//         @$html->loadHTML($resp);
//         $xpath = new DOMXPath($html);
//         $title = $xpath->query('//h2[@class="entry-title"] | //h2[@class="entry-title"]/a/@href');
//         //$liens = $xpath->query('//h2[@class="entry-title"]/a/@href'); 
//         //$pageNext = $xpath->query('//a[@class="page-numbers"]/@href');

//         // titre //
//         $page = 1;
//         $z = 1;
//         echo '<h2>page: ' . $page . '</h2>';
//         foreach ($title as $titre) {
//             echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
//         }
//         // lien des pages suivantes //
//         //echo "<h4>page suivante:</h4> " . $pageNext->item(0)->nodeValue;
//     }
//     page($url_ref);

//     if ($nb_pages) {
//         // pages suivantes //
//         $page = 1;
//         while ($page != $nb_pages) {
//             $page++;
//             $url =  $url_ref . "page/" . $page . "/";
//             $curl = curl_init($url);
//             curl_setopt($curl, CURLOPT_URL, $url);
//             curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//             curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//             curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//             $resp = curl_exec($curl);
//             curl_close($curl);

//             $html = new DOMDocument();
//             @$html->loadHTML($resp);
//             $xpath = new DOMXPath($html);
//             $title = $xpath->query('//h2[@class="entry-title"] | //h2[@class="entry-title"]/a/@href');
//             //$liens = $xpath->query('//h2[@class="entry-title"]/a/@href');

//             // $pageNext = $xpath->query('//a[@class="page-numbers"]/@href');

//             $z = 1;
//             // titre //
//             echo '<h2>page ' . $page . '</h2>';
//             foreach ($title as $titre) {
//                 echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
//             }
//         }
//     }
// }


// if ($_POST['execurl']){

//     $url_ref = $_POST['article'];
//     echo $url_ref;
//     $curl = curl_init($url_ref);
//     curl_setopt($curl, CURLOPT_URL, $url_ref);
//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//     $resp = curl_exec($curl);
//     curl_close($curl);

//     $html = new DOMDocument();
//     @$html->loadHTML($resp);
//     $xpath = new DOMXPath($html);
//     $title = $xpath->query('//title');
//     $content = $xpath->query('//div[@class="entry-content"]');
//     $date = $xpath->query('//meta[@property="og:description"]');
//     $author = $xpath->query('//h1[@class="entry-title"]');
//     $image = $xpath->query('//div[@class="post-thumbnail"]/img/@src');
//     $content = $xpath->query('//div[@class="entry-content"]');

//     $z = 1;
//     // titre //
//     echo '<h2>article</h2>';
//     foreach ($title as $titre) {
//         echo 'titre ' . $z++ . ": " . $titre->nodeValue . ' <br>';
//     }
// }

$url_article = "https://bikehike.org/travel-tips-during-covid-2022/";



function get_article($url_article)
{
  echo "( -scrap de l'article: " . $url_article . " )<br>";
  $curl = curl_init($url_article);
  curl_setopt($curl, CURLOPT_URL, $url_article);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  $resp = curl_exec($curl);
  curl_close($curl);

  $html = new DOMDocument();
  @$html->loadHTML($resp);
  $xpath = new DOMXPath($html);

  // différent d'une DIV //

  
  $code_source = [];  
  //supprimer tout les attributs du DOM //  
  $class = $xpath->query('//@*');     
  foreach ($class as $classes) {  
    
    $classes->parentNode->removeAttribute($classes->nodeName); 
  }
  //on récupère le code source sauf les tagsName == DIV //
  $code_source = $xpath->query('//*[not(self::div)]');
 //echo $code_source->item(0)->nodeValue;

  $code_source[] = $classes->ownerDocument->saveHTML($classes->parentNode);
  //echo $classes->ownerDocument->saveHTML($classes->parentNode);
  if(!empty($code_source)){
    $fichier = fopen("test.html", 'w');
    foreach ($code_source as $code) {    
      fwrite($fichier, $code);
    }
  }
  fclose($fichier);

}
get_article($url_article);







  
  // $shearchNode = $html->getElementsByTagName('div'); 
  // foreach ($shearchNode as $node) {
  //   $node->removeAttribute('class');
  //   $node->removeAttribute('id');
  //   $node->removeAttribute('style');
  //   //si les enfants on des attributs, on les supprime // 
  //   // if ($node->hasAttributes()) {
  //   //   foreach ($node->attributes as $attr) {
  //   //       $node->removeAttribute('class');
  //   //   }
  //   }
  // echo $node->ownerDocument->saveHTML($node);      
      
  //   }