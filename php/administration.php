<?php
  require_once("bibli_gazette.php");

  session_start();
  ob_start();

  $isLogged = cp_is_logged("../index.php");

  // --- Database interactions  --- 

  $db = cp_db_connecter();

  $query1 = 'SELECT utPseudo,utStatut,COUNT(coID) as nbCommentaire 
            FROM utilisateur LEFT OUTER JOIN commentaire ON utPseudo=coAuteur 
            GROUP BY utPseudo
            ORDER BY utPseudo, utStatut';
  $query2 = 'SELECT utPseudo,utStatut,COUNT(DISTINCT arID) as nbArticle, COUNT(coID) as nbCommentaireAr 
            FROM (utilisateur LEFT OUTER JOIN article ON utPseudo=arAuteur) LEFT OUTER JOIN commentaire ON arID=coArticle 
            GROUP BY utPseudo, utStatut, arID';
  
  $res1 = cp_db_execute($db,$query1);
  $res2 = cp_db_execute($db,$query2);
  mysqli_close($db);
  $res2 = cpl_organize_data($res2);

  cp_print_beginPage('administration', 'Administration',1,$isLogged);
  cpl_print_users_informations($res1, $res2);
  cp_print_endPage();
  ob_end_flush();

  // #####################################################################################################################

  /**
   * Format data to a readable array [pseudo=>[nbArticle,nbCommentaireMoy]...]
   * 
   * @param mixed $data 
   * @param mixed $value 
   * @return array 
   */
  function cpl_organize_data($data) {
    $result = [];
    foreach ($data as $value) {
      $result[$value['utPseudo']]['nbArticle'] = (int)$value['nbArticle'] + (isset($result[$value['utPseudo']]['nbArticle']) ? (int)$result[$value['utPseudo']]['nbArticle'] : 0);
      $result[$value['utPseudo']]['nbCommentaireAr'] = (int)$value['nbCommentaireAr'] + (isset($result[$value['utPseudo']]['nbCommentaireAr']) ? (int)$result[$value['utPseudo']]['nbCommentaireAr'] : 0);
    }
    foreach ($result as $value) {
      $value['nbCommentaireAr'] = $value['nbArticle'] == 0 ? 0 : $value['nbCommentaireAr']/$value['nbArticle'];
    }
    return $result;
  }

  /**
   * Print user administration panel
   * 
   * @param mixed $users 
   * @return void
   */
  function cpl_print_users_informations($users1, $users2) {
    echo '<section>',
    '<h2>Permissions</h2>',
    '<form method="POST" action="administration.php">',
    '<table>',
      '<thead>',
        '<tr>',
          '<th>pseudo</th>',
          '<th>statut</th>',
          '<th>commentaires publiés</th>',
          '<th>articles publiés</th>',
          '<th>nombre moyen de commentaires</th>',
        '</tr>',
      '</thead>',
    '<tbody>';
    foreach ($users1 as $value) {
      echo '<tr>',
      '<td>',$value['utPseudo'],'</td>',
      '<td>';
      echo $value['utStatut'] < $_SESSION['status'] ? cp_form_print_list($value['utPseudo'],cpl_create_array_number(0,(int)$_SESSION['status']),$value['utStatut']) : '<p>'.$value['utStatut'].'</p>';
      echo '</td>',
      '<td>',$value['nbCommentaire'],'</td>',
      '<td>',$users2[$value['utPseudo']]['nbArticle'],'</td>',
      '<td>',$users2[$value['utPseudo']]['nbCommentaireAr'],'</td>',
      '</tr>';
    }
    echo '</tbody>',
    '</table>',
    '</form>',
    '</section>';
  }

  /**
   * Permet de créer un tableau de nombre
   * 
   * @param int $min La valeur minimale du tableau
   * @param int $max La valeur maximale du tableau
   * @param int $step Le pas d'itération
   * @return array Le tableau de nombre
   */
  function cpl_create_array_number($min,$max,$step=1) {
    $result = [];
    for ($i=$min; $i <= $max; $i+=$step) {
      $result[] = "$i";
    }
    return $result; 
  }
?>