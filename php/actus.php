<?php 
  session_start();
  ob_start();
  require_once('../php/bibli_gazette.php');

  // --- Database interactions  --- 

  $db = fp_db_connecter();

  $query = 'SELECT arID/*,arTitre,arResume*/ FROM article ORDER BY arDateModification DESC, arDatePublication DESC, rand()';
  
  $res = fp_db_execute($db,$query);

  mysqli_close($db);
  
  $numberArticle = count($res);

  var_dump($_POST);

  fp_print_beginPage('actus','L\'actu',1);
  cpl_print_button_pages($numberArticle);
  fp_print_endPage();

  // #########################################################

  function cpl_print_actus() {

  }

  /**
   * Printing article actus in section in the gazette website 
   * 
   * @param string $id 
   * @param string $titre 
   * @param string $resume 
   * @return void 
   */
  function cpl_print_section($article, $titre) {
    echo '<section class="type1">',
   "<h2>$titre</h2>",
   '</section>';
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
   * @param int $numArticle 
   * @return void 
   */
  function cpl_print_button_pages($numArticle){
    echo '<section> <form method="POST" action="actus.php">',
    '<p>Pages : </p>';
    for ($i=0; $i < $numArticle/4; $i++) { 
      echo '<input type="submit" value="',$i+1,'" name="page',$i+1,'" ',$i==0 ? 'disabled' : '','></input>';
    }
    echo '</form> </section>';
  }
?>