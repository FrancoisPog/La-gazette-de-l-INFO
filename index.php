<?php
session_start();
ob_start();
require_once("php/bibli_gazette.php");

// --- local functions ---

/**
 * Printing an article link
 * @param Array $articleData The article data (already protected)
 */
function cpl_print_articleLink($articleData){
    
    $abstarct = $articleData['arResume'];
    $cutAbstarct = mb_substr($abstarct,0,300,ENCODE);
    if($abstarct != $cutAbstarct){
        $cutAbstarct .= '...';
    }

    $articleData = cp_db_protect_outputs($articleData);
    $cutAbstarct = cp_db_protect_outputs($cutAbstarct);
    $titre = $articleData['arTitre'];

    if(file_exists('upload/'.$articleData['arID'].'.jpg')){
        $picture = 'upload/'.$articleData['arID'].'.jpg';
    }else{
        $picture = 'images/none.jpg';
    }

    echo    '<a href="php/article.php?id=',urlencode($articleData['arID']),'">',
                '<aside><p>',$cutAbstarct,'</p></aside>',
                '<figure>',
                    '<img src="',$picture,'" alt="',$titre,'">',
                    '<figcaption>',$titre,'</figcaption>',
                '</figure>',
            '</a>';

 
}



/**
 * Printing an articles link block
 * @param Array $articles   The articles to print
 * @param String $title     The section title
 */
function cpl_print_articleBlock($articles,$title){
    echo '<section>',
            '<h2>',$title,'</h2>';
                foreach($articles as $article){
                    cpl_print_articleLink($article);
                }
    echo '</section>';
}

/**
 * Print the horoscope
 */
function cpl_print_horoscope(){
    echo    '<section id="horoscope">',
                '<h2>L\'horoscope de la semaine</h2>',    
                '<p>',
                    'Vous l\'attendiez tous, voici l\'horoscope du semestre pair de l\'année 2019-2020. Sans surprise, il n\'est',
                '    pas terrible...',
                '</p>',
                '<table>',
                    '<thead>',
                        '<tr>',
                            '<th>Signe</th>',
                            '<th>Date</th>',
                            '<th>Votre horoscope</th>',
                        '</tr>',
                    '</thead>',
                    '<tbody>',
                        '<tr>',
                            '<td>&#9800; Bélier</td>',
                            '<td>du 21 mars au 19 avril</td>',
                            '<td rowspan="4">',
                                '<p>',
                                    'Après des vacances bien méritées, l\'année reprend sur les chapeaux de roues.',
                                    'Tous les signes sont concernés.',
                                '</p>',

                                '<p>',
                                    'Jupiter s\'aligne avec Saturne, péremptoirement à Venus, et nous promet un semestre qui ne',
                                    'sera pas de tout repos. Février sera le mois le plus tranquille puisqu\'il ne comporte',
                                    'que 29 jours.',
                                '</p>',

                                '<p>',
                                    'Les fins de mois seront douloureuses pour les natifs du 2e décan au moment où tomberont',
                                    'les tant-attendus résultats du module <em>d\'Algorithmique et Structures de Données</em> du',
                                    'semestre 3.',
                                '</p>',
                            '</td>',
                        '</tr>',
                        '<tr>',
                            '<td>&#9801; Taureau</td>',
                            '<td>du 20 avril au 20 mai</td>',
                        '</tr>',
                        '<tr>',
                            '<td>...</td>',
                            '<td>...</td>',
                        '</tr>',
                        '<tr>',
                            '<td>&#9811; Poisson</td>',
                            '<td>du 20 février au 20 mars</td>',

                        '</tr>',
                    '</tbody>',
                '</table>',
                '<p>',
                    'Malgré cela, notre équipe d\'astrologues de choc vous souhaite à tous un bon semestre, et bon courage pour',
                    'le module de <em>Système et Programmation Système</em>',
                '</p>',
            '</section>';
}

/**
 * Division of articles in three sections 
 * @param Array $articles The articles from database
 * @return Array Articles distributed in three array
 */
function cpl_select_articles($articles) {
    $result = array([],[],[]);
    $idAlreadyUsed = array();

    foreach($articles as $article){
      switch($article['type']) {
        case 1:
          $result[0][] = $article;
          $idAlreadyUsed[] = $article['arID'];
          break;
        case 2:
          $result[1][] = $article;
          $idAlreadyUsed[] = $article['arID'];
          break;
        case 3:
          if(!in_array($article['arID'], $idAlreadyUsed) && count($result[2]) < 3) {
            $result[2][] = $article;
          }
          break;
      }
    }
    return $result;
}



// --- Database interactions  --- 

$db = cp_db_connecter();

$query = '('.'SELECT arID, arTitre, arResume, 1 AS type
                FROM article
                ORDER BY arDatePublication DESC
                LIMIT 0, 3)
                UNION
                    (SELECT arID, arTitre, arResume, 2 AS type
                    FROM article
                    LEFT OUTER JOIN commentaire ON coArticle = arID
                    GROUP BY arID
                    ORDER BY COUNT(coArticle) DESC, rand()
                    LIMIT 0, 3)
                    UNION
                        (SELECT arID, arTitre, arResume, 3 AS type
                        FROM article
                        ORDER BY rand()
                        LIMIT 0,9)';

$res = cp_db_execute($db,$query,false);

mysqli_close($db);

$articles = cpl_select_articles($res);

            
// ---Page generation ---

$isLogged = cp_is_logged();

cp_print_beginPage('accueil',"Le site de désinformation n°1 des étudiants en Licence info",0,($isLogged)?$_SESSION['statut']:-1,($isLogged)?$_SESSION['pseudo']:false);
    
cpl_print_articleBlock($articles[0],"&Agrave; la une");
cpl_print_articleBlock($articles[1],"L'info brûlante");
cpl_print_articleBlock($articles[2],"Les incontournables");
cpl_print_horoscope();

cp_print_endPage();