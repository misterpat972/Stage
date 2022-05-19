<?php
// $url_article = $_POST['article'] ?: "";
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
    $h2 = $xpath->query('//div[@class="entry-content"]/h2');
    $title = $xpath->query('//title');
    $content = $xpath->query('//div[@class="entry-content"]');
    $meta = $xpath->query('//meta[@property="og:description"]/@content');
    $titre_h1 = $xpath->query('//h1[@class="entry-title"]');
    $image = $xpath->query('//div[@class="post-thumbnail"]/img/@data-lazy-srcset');
    
    // title //
    echo '<h2>Balise Title: </h2>';
    echo '<p>' . $title->item(0)->nodeValue . '</p>';


    // meta //
    echo '<h2>Meta d\'escription:</h2>';
    echo '<meta>' . $meta->item(0)->nodeValue . '</meta>';

    // image //
    echo '<h2>Image:</h2>';
    foreach ($image as $image) {
        echo '<pre>' . $image->nodeValue . '</pre>';
    }
    // titre h1 //
    echo '<h2>Titre h1:</h2>';
    echo '<h1>' . $titre_h1->item(0)->nodeValue . '</h1>';


    //affichage du code source de la page html 
    echo '<h2>Contenu:</h2>';
    $code_source = array();
    foreach ($content->item(0)->childNodes as $node) {        
        if ($node->nodeName != 'div') {
            //supprimer les class //
            $removeAttributes = preg_replace('/class="(.*?)"/', '', $node->ownerDocument->saveHTML($node));
            $removeAttributes = preg_replace('/id="(.*?)"/', '', $removeAttributes);
            //push dans le tableau //
            array_push($code_source, $removeAttributes);
        }
        echo '<pre>' . $node->ownerDocument->saveHTML($node) . '</pre>';
    }
    if ($code_source != null) {
        //enregistrement du code source dans un fichier
        $file = fopen('code_source.txt', 'w');
        foreach ($code_source as $code) {
            fwrite($file, $code);
        }
        fclose($file);
    }
          
}
get_article($url_article);


