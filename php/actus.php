<?php 
  session_start();
  ob_start();
  require_once('../php/bibli_gazette.php');

  $isLogged = cp_is_logged();

  // --- Database interactions  --- 

  $db = cp_db_connecter();

  $query = 'SELECT arID,arTitre,arResume,arDatePublication FROM article ORDER BY arDatePublication DESC, rand()';
  
  $res = cp_db_execute($db,$query);

  mysqli_close($db);

  $numberArticle = count($res);
  $activeButton = isset($_GET['buttonPage']) ? cp_str_isInt(cp_decrypt_url($_GET['buttonPage'],1)[0]) ? intval(cp_decrypt_url($_GET['buttonPage'],1)[0]) : header("Location: ../index.php") : 1;

  cp_print_beginPage('actus','L\'actu',1,$isLogged);
  cpl_print_button_pages($numberArticle, $activeButton);
  cpl_print_actus($res, $activeButton, $numberArticle);
  cp_print_endPage();

  // #####################################################################################################################

  /**
   * Printing page actus in the gazette website
   * 
   * @param mixed $articles 
   * @param mixed $button 
   * @param mixed $numberArticle 
   * @return void 
   */
  function cpl_print_actus($articles, $button, $numberArticle) {
    $groupedArticles = cpl_group_article_by_date($articles, $button, $numberArticle);
    foreach ($groupedArticles as $date => $articles) {
      cpl_print_section($articles,$date);
    }
  }

  function cpl_print_section($articles, $date) {
    echo '<section class="type1">',
   '<h2>',cpl_date_section($date),'</h2>';
    foreach ($articles as $article) {
      cpl_print_article($article['arID'],$article['arTitre'],$article['arResume']);
    }
   echo '</section>';
  }

  /**
   * Printing article actus in section in the gazette website 
   * 
   * @param string $id 
   * @param string $titre 
   * @param string $resume 
   * @return void 
   */
  function cpl_print_article($id,$titre,$resume) {
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

  /**
   * Printing navigating buttons in the gazette website 
   * 
   * @param int $numArticle The number of article find
   * @param int $buttonSelected The selected button
   * @return void 
   */
  function cpl_print_button_pages($numArticle, $buttonSelected){
    if($numArticle != 0) {
      echo '<section>',
      '<p>Pages : </p>';
      for ($i=0; $i < $numArticle/4; $i++) {
        if($i+1 == $buttonSelected) {
          echo '<a id="linkDown">',$i+1,'</a>';
        } else {
          echo '<a href="actus.php?buttonPage=',cp_encrypt_url([$i+1]),'">',$i+1,'</a>';
        }
      }
      echo '</section>';
    }
  }

  /**
   * Group four article by date determined by the selected button
   * 
   * @param array $articles All articles of gazette website
   * @param int $button The button that is selected
   * @return array Articles grouped by date
   */
  function cpl_group_article_by_date($articles, $button, $numberArticle) {
    $result = [];
    for ($i=4*($button-1); $i < 4*($button-1)+4 && $i < $numberArticle; $i++) {
      $date = cpl_get_year_and_month($articles[$i]['arDatePublication']);
      $result[$date][] = $articles[$i];
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
?>