<?php
//essai d'api rest

function get($resource, array $params = array()) {
    $apiUrl = 'http://wixiweb.fr/wp-json';
    $json = file_get_contents($apiUrl . $resource . '?'. http_build_query($params));
    $data = json_decode($json);
    return $data;    
}
$api = get('/');
echo $api->name . '<br>';
echo $api->description . '<br>';

$pages = get('/wp/v2/pages', array(
    'orderby' => 'id',
    'order' => 'asc',
    'search' => 'normandie',
));

foreach($pages as $page){
    //récupération des données de la page
    echo 'page ' . $page->id . ': ' . $page->slug;   
      //récupération des résumés de la page
    echo substr($page->excerpt->rendered, 0, 100) . '...<br>';
}




