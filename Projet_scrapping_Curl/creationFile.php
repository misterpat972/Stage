<?php
// permet de récupérer le code source d'une page web et de le stocker dans un fichier html //
$url = "https://bikehike.org/riding-and-health/";
// $page = fopen('groupon.html', 'w');
// $curl = curl_init($url);
// curl_setopt($curl, CURLOPT_URL, $url);
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
// $msg = 'ma page contient : ';
// $msg .= $page;
// curl_setopt($curl, CURLOPT_FILE, $page);
// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
// $resp = curl_exec($curl);
// curl_close($curl);
// fclose($page);
//**********************************************************//





// exemple de code pour récupérer les liens des images //
// $img = $xpath->query('//div[@class="cui-image-lazy-container cui-image-hover-zoom"]/img[2]/@src');
//$img = $xpath->query('//div[@class="pb-content"]/a/img/@data-lazy-src');
 // lien des images //
//  $y = 1;
//  foreach ($img as $image) {
//    echo 'image ' . $y++ . ": " . $image->nodeValue . ' <br>';
//  }
// $pagination = $xpath->query('//*[@id="pull-pagination "]/nav/div/div[2]/ul/li/div[3]/a/@href');
//*********************************************************//




// fonction qui permet de récupérer les images en .jpg //
function save_images($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    $raw = curl_exec($ch);
    curl_close($ch);

    $html = new DOMDocument();
    @$html->loadHTML($raw);
    $xpath = new DOMXPath($html);
    $img = $xpath->query('//div[@class="pb-content"]/a/img/@data-lazy-src');
    $img_url = array();
    foreach ($img as $image) {
        $img_url[] = $image->nodeValue;
    }
    if (!empty($img_url)) {
        foreach ($img_url as $url) {
            $ch = curl_init($url); 
            curl_setopt($ch, CURLOPT_HEADER, 0); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // retourne le contenu téléchargé dans une chaine //
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            $raw = curl_exec($ch); // $raw contient le contenu de l'image //
            curl_close($ch); 
            $file = fopen('img/' . basename($url), 'w'); // ouverture du fichier en écriture, (basename) permet de récupérer le nom de l'image //
            fwrite($file, $raw); // écriture du fichier //
            fclose($file); // fermeture du fichier //
        }
    }
}
save_images($url);
//*********************************************************//



// fonction qui permet de récupérer également les images en .jpg//
// $i = 1;
// foreach ($img_url as $url) {
//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_HEADER, 0);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
//     $raw = curl_exec($ch);
//     curl_close($ch);
//     $file = fopen('img/image' . $i . '.jpg', 'w');
//     fwrite($file, $raw);
//     fclose($file);
//     $i++;
// }
//*********************************************************//