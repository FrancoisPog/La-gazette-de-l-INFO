<?php
  require_once("bibli_gazette.php");

  session_start();
  ob_start();

  cp_is_logged("../index.php");
  if($_SESSION['status'] < 2){
    cp_session_exit('../index.php');
  }

  // --- Database interactions  --- 

  $db = cp_db_connecter();

  $query = 'SELECT utPseudo,utStatut,COUNT(coID) as nbCommentaire 
            FROM utilisateur LEFT OUTER JOIN commentaire ON utPseudo=coAuteur 
            GROUP BY utPseudo
            ORDER BY utPseudo, utStatut;
            SELECT utPseudo,utStatut,COUNT(DISTINCT arID) as nbArticle, COUNT(coID) as nbCommentaireAr 
            FROM (utilisateur LEFT OUTER JOIN article ON utPseudo=arAuteur) LEFT OUTER JOIN commentaire ON arID=coArticle 
            GROUP BY utPseudo, utStatut, arID';
  
  $res = cp_db_execute($db,$query,true,false,true);
  $res1 = $res[0];
  $res2 = $res[1];

  mysqli_close($db);

  $res2 = cpl_organize_data($res2);
  if(isset($_POST['submit'])) {
    $res1 = cpl_verification_statut($res1);
  }

  cp_print_beginPage('administration', 'Administration',1,true);
  cpl_print_users_informations($res1, $res2);
  cpl_print_statut_description();
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
    foreach ($result as $pseudo => $value) {
      if($result[$pseudo]['nbArticle'] == 0) {
        $result[$pseudo]['nbCommentaireAr'] = 0;
      } else {
        $result[$pseudo]['nbCommentaireAr'] = round($result[$pseudo]['nbCommentaireAr']/$result[$pseudo]['nbArticle'],0,PHP_ROUND_HALF_UP);
      }
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
    $i = 0;
    foreach ($users1 as $value) {
      echo '<tr>',
      '<td>',$value['utPseudo'],'</td>',
      '<td>';
      echo $value['utStatut'] < $_SESSION['status'] ? cp_form_print_list($i,cpl_create_array_number(0,(int)$_SESSION['status']),$value['utStatut']) : '<p>'.$value['utStatut'].'</p>';
      echo '</td>',
      '<td>',$value['nbCommentaire'],'</td>',
      '<td>',$users2[$value['utPseudo']]['nbArticle'],'</td>',
      '<td>',$users2[$value['utPseudo']]['nbCommentaireAr'],'</td>',
      '</tr>';
      $i++;
    }
    cp_form_print_buttonsLine(5,['Envoyer','submit'],'Réinitialiser',false,false);
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

  /**
   * Print the description of each statut
   * 
   * @return void 
   */
  function cpl_print_statut_description() {
    echo '<section>',
    '<h2>Statut déscription</h2>',
    '<p><strong>Statut 0</strong> : Un simple utilisateur inscrit (valeur par défaut)</p>',
    '<p><strong>Statut 1</strong> : Un utilisateur inscrit qui est uniquement rédacteur</p>',
    '<p><strong>Statut 2</strong> : Un utilisateur inscrit qui est uniquement administrateur</p>',
    '<p><strong>Statut 3</strong> : Un utilisateur inscrit qui est rédacteur et administrateur</p>',
    '</section>';
  }

  /**
   * Check and change the user status
   * @param Array $tab  All users status
   * @return Array The users status updated
   */
  function cpl_verification_statut($tab) {
      
      $query = '';
      foreach ($_POST as $index => $statut) {
        if($index === 'submit') {
          break;
        }
        if(!cp_str_isInt($index) || !cp_str_isInt($statut) || $statut < "0" || $statut > "3" || $statut > $_SESSION['status'] || $tab[$index]['utStatut'] >= $_SESSION['status']) {
          cp_session_exit('../index.php');
        }
        if ($statut != $tab[$index]['utStatut']) {
          $query .= "UPDATE utilisateur SET utStatut = $statut WHERE utPseudo = '".$tab[$index]['utPseudo']."';";
          $tab[$index]['utStatut'] = $statut;
        }
      }
      if($query == ''){
        return $tab;
      }

      $db = cp_db_connecter();

      cp_db_execute($db,$query,true,false,true);
      
      mysqli_close($db);
      return $tab;
  }
