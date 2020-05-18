<?php 

ob_start();
session_start();
require_once('bibli_gazette.php');



function cpl_print_search_page($isLogged){
    cp_print_beginPage('recherche','Recherche',1,$isLogged);

    echo '<section id="search">',
            '<h2>Rechercher des articles</h2>',
            '<p>Les critères de recherches doivent faire au moins 3 caractères pour être pris en compte.</p>',
            '<form action="recherche.php" method="GET">',
                '<input type="text" name="search_keys">',
                cp_print_button('submit','Rechercher','btnSearch'),
            '</form>',


        '</section>',
        

    cp_print_endPage();
}

function cpl_fetch_article($search_keys){
    $db = cp_db_connecter();

    $query = 'SELECT * 
                FROM article
                WHERE 1 ';

    foreach($search_keys as $key){
        $query.= 'AND (arTitre LIKE \'%'.$key.'%\' OR arResume LIKE \'%'.$key.'%\' OR arTexte LIKE \'%'.$key.'%\' )';
    }

    $articles = cp_db_execute($db,$query);

    mysqli_close($db);

    return $articles;
}

function cpl_search_process(){
    cp_check_param($_GET,['btnSearch','search_keys']) or cp_session_exit('../index.php');

    $search_keys = explode(' ',$_GET['search_keys']);

    $articles = cpl_fetch_article($search_keys);

    var_dump($articles);

}





$isLogged = cp_is_logged();

if(isset($_GET['btnSearch'])){
    cpl_search_process();
}else{
    cpl_print_search_page($isLogged);
}