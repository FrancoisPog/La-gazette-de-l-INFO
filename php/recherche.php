<?php 

ob_start();
session_start();
require_once('bibli_gazette.php');


/**
 * Print the search page
 * @param boolean $isLogged True if the user is logged in, otherwise else
 * @param String $error     The optional error message
 * @param Array $articles   The optional array of sorted articles
 */
function cpl_print_search_page($isLogged, $error = '',$articles = []){
    cp_print_beginPage('recherche','Recherche',1,$isLogged);

    echo '<section id="searchSection">',
            '<h2>Rechercher des articles</h2>',
            '<p>Les critères de recherches doivent faire au moins 3 caractères pour être pris en compte.</p>',
            ($error != '')?'<p class="error">'.$error.'</p>':'',
            '<form action="recherche.php" method="GET">',
                '<input type="text" name="search_keys" value="',(isset($_GET['search_keys']))?cp_db_protect_outputs($_GET['search_keys']):'','">',
                cp_print_button('submit','Rechercher','btnSearch'),
            '</form>',
        '</section>';

    if(isset($_GET['btnSearch']) && $error == ''){
        if($articles == null){
            echo '<section>',
                    '<h2>Aucun article trouvé</h2>',
                    '<p>Aucun article ne correspond à vos critères de recherche.</p>',
                '</section>';
        }

        foreach($articles as $month => $articleByMonth){
           cp_print_sortedArticlesSection($articleByMonth,$month);
        }
    }
    cp_print_endPage();
}

/**
 * Fetch articles in database
 * @param Array $search_keys    The array of search keys
 * @return Array                The array of articles resulting from search
 */
function cpl_fetch_article($search_keys){
    $db = cp_db_connecter();

    $query = 'SELECT * 
                FROM article
                WHERE 1 ';

    foreach($search_keys as $key){
        $query.= 'AND (arTitre LIKE \'%'.$key.'%\' OR arResume LIKE \'%'.$key.'%\' )';
    }

    $query .= 'ORDER BY arDatePublication DESC';

    $articles = cp_db_execute($db,$query);

    mysqli_close($db);

    return $articles;
}

 /**
   * Group articles by date
   * 
   * @param array $articles The articles to group
   * @return array Articles grouped by date
   */
  function cpl_group_article($articles) {
    $result = [];
    foreach($articles as $article) {
        $date = substr($article['arDatePublication'],0,6);
        $result[$date][] = $article;
    }
    return $result;
  }

/**
 * Execute the search process
 * @return String The error message if failure
 * @return Array  The grouped articles from search if success
 */
function cpl_search_process(){
    cp_check_param($_GET,['btnSearch','search_keys']) or cp_session_exit('../index.php');

    $search_keys = explode(' ',$_GET['search_keys']);

    foreach($search_keys as $index => $key){
        if($key == ''){
            unset($search_keys[$index]);
            continue;
        }
        if(strlen($key) < 3){
            return "Les critères de recherche doivent être composés d'au moins 3 caractères";
        }
    }
    
    if(count($search_keys) == 0){
        return "Vous devez renseigner au moins un critère de recherche"; 
    }

    $articles = cpl_fetch_article($search_keys);

    if($articles == null){
        return array();
    }

    $articles = cpl_group_article($articles);

    return $articles;
}



// MAIN

$isLogged = cp_is_logged();

if(isset($_GET['btnSearch'])){
    $articles = cpl_search_process();
    (is_array($articles))?cpl_print_search_page($isLogged,'',$articles):cpl_print_search_page($isLogged,$articles);
}else{
    cpl_print_search_page($isLogged);
}