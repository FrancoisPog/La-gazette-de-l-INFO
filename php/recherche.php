<?php 

ob_start();
session_start();
require_once('bibli_gazette.php');



function cpl_print_search_page($isLogged, $error = '',$articles = []){
    cp_print_beginPage('recherche','Recherche',1,$isLogged);

    echo '<section id="search">',
            '<h2>Rechercher des articles</h2>',
            '<p>Les critères de recherches doivent faire au moins 3 caractères pour être pris en compte.</p>',
            ($error != '')?'<p class="error">'.$error.'</p>':'',
            '<form action="recherche.php" method="GET">',
                '<input type="text" name="search_keys" value="',(isset($_GET['search_keys']))?cp_db_protect_outputs($_GET['search_keys']):'','">',
                cp_print_button('submit','Rechercher','btnSearch'),
            '</form>',
        '</section>';

    
    foreach($articles as $month => $articleByMonth){
        echo '<section>',
                '<h2>',cpl_date_section($month),'</h2>';
        foreach($articleByMonth as $article){
            $id = $article['arID'];
            $titre = $article['arTitre'];
            $resume = $article['arResume'];
            if(file_exists("../upload/$id.jpg")){
                $picture = "../upload/$id.jpg";
              }else{
                $picture = "../images/none.jpg";
              }
              echo '<article>',
                    '<img src="',$picture,'" alt="',$titre,'" title="',$titre,'">',
                    "<h3>$titre</h3>",
                    "<p>$resume</p>",
                    '<a href="../php/article.php?data=',cp_encrypt_url([$id]),'">Lire l\'article</a>',
                  '</article>';
        }
        echo '</section>';
    }
        

    cp_print_endPage();
}

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
   * Group four article by date determined by the selected button
   * 
   * @param array $articles All articles of gazette website
   * @param int $button The button that is selected
   * @return array Articles grouped by date
   */
  function cpl_group_article_by_date($articles) {
    $result = [];
    foreach($articles as $article) {
      $date = cpl_get_year_and_month($article['arDatePublication']);
      $result[$date][] = $article;
    }
    return $result;
  }

  /**
   * Get year and month of a date with this format YYYYMMDD
   * 
   * @param string $date The date
   * @return string The month and the year or null
   */
  function cpl_get_year_and_month($date) {
    if ($date == null) {
      return null;
    }
    return substr($date,0,6);
  }

  /**
   * Parsing date for section
   * @param int $date The date to parse
   * @return String   The date in correct format for section
   */
  function cpl_date_section($date) {
    $moisTab = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
    $mois = substr($date,4);
    $annee = substr($date,0,4);
    return $moisTab[$mois] . ' ' . $annee; 
  }

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

    $articles = cpl_group_article_by_date($articles);

    return $articles;
}





$isLogged = cp_is_logged();

if(isset($_GET['btnSearch'])){
    $articles = cpl_search_process();
    (is_array($articles))?cpl_print_search_page($isLogged,'',$articles):cpl_print_search_page($isLogged,$articles);
}else{
    cpl_print_search_page($isLogged);
}