<?php 
  session_start();
  ob_start();
  require_once('../php/bibli_gazette.php');

  // --- Database interactions  --- 

  $db = cp_db_connecter();

  $query = 'SELECT arID,arTitre,arResume,arDatePublication,arDateModification FROM article ORDER BY arDateModification DESC, arDatePublication DESC, rand()';
  
  $res = cp_db_execute($db,$query);

  mysqli_close($db);
  
  $numberArticle = count($res);
  $activeButton = isset($_POST['buttonPage']) ? cp_str_isInt($_POST['buttonPage']) ? intval($_POST['buttonPage']) : 1 : 1;

  cp_print_beginPage('actus','L\'actu',1);
  cpl_print_button_pages($numberArticle, $activeButton);
  cpl_print_actus($res, $activeButton, $numberArticle);
  cp_print_endPage();

  // #########################################################

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
    echo '<article>',
    '<img src="../upload/',$id,'.jpg" alt="',$titre,'">',
    "<h3>$titre</h3>",
    "<p>$resume</p>",
    '<a href="../php/article.php?id=',$id,'">Lire l\'article</a>',
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
      echo '<section> <form method="POST" action="actus.php">',
      '<p>Pages : </p>';
      for ($i=0; $i < $numArticle/4; $i++) { 
        echo '<input type="submit" value="',$i+1,'" name="buttonPage" ',$i==($buttonSelected-1) ? 'disabled' : '','></input>';
      }
      echo '</form> </section>';
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
      if($date = cpl_get_year_and_month($articles[$i]['arDateModification'])) {
        $result[$date][] = $articles[$i];
      } else {
        $date = cpl_get_year_and_month($articles[$i]['arDatePublication']);
        $result[$date][] = $articles[$i];
      }
    }
    var_dump($result);
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
    $moisTab = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','06'=>'Mai','07'=>'Juin','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
    $mois = substr($date,4);
    $annee = substr($date,0,4);
    return $moisTab[$mois] . ' ' . $annee; 
  }
?>