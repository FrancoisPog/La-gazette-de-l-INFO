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
  cp_print_beginPage('actus','L\'actu',1,$isLogged);
  if($res != null){
    $numberArticle = count($res);
    $activeButton = isset($_GET['buttonPage']) ? cp_str_isInt(cp_decrypt_url($_GET['buttonPage'],1)[0]) ? intval(cp_decrypt_url($_GET['buttonPage'],1)[0]) : header("Location: ../index.php") : 1;

    
    cpl_print_button_pages($numberArticle, $activeButton);
    cpl_print_actus($res, $activeButton, $numberArticle);
  }
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
      cp_print_sortedArticlesSection($articles,$date);
    }
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
      $date = substr($articles[$i]['arDatePublication'],0,6);
      $result[$date][] = $articles[$i];
    }
    return $result;
  }
 
