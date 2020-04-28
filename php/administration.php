<?php
  require_once("bibli_gazette.php");

  session_start();
  ob_start();

  cp_is_logged("../index.php");

  // --- Database interactions  --- 

  $db = cp_db_connecter();

  $query = 'SELECT utPseudo,utStatut FROM utilisateur ORDER BY utPseudo';
  
  $res = cp_db_execute($db,$query);

  mysqli_close($db);

  cp_print_beginPage('administration', 'Administration',1,$_SESSION['status'],$_SESSION['pseudo']);
  cpl_print_users_informations($res);
  cp_print_endPage();
  ob_end_flush();

  // #####################################################################################################################

  /**
   * Print user administration panel
   * 
   * @param mixed $users 
   * @return void
   */
  function cpl_print_users_informations($users) {
    echo '<section>',
    '<h2>Permissions</h2>',
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
    foreach ($users as $value) {
      echo '<tr>',
      cp_form_print_listLine($value['utPseudo'],"statut",['0','1','2','3'],$value['utStatut']);
      echo '</tr>';
    }
    echo '</tbody>',
    '</table>',
    '</section>';
  }
?>