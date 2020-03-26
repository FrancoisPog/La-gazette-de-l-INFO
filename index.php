<?php

ob_start();
require_once("php/bibli_generale.php");

// --- local functions ---

/**
 * Printing an article
 * @param Array $articleData The article data
 */
function fpl_print_article($articleData){
    $titre = $articleData['arTitre'];
    if(file_exists('upload/'.$articleData['arID']).'.jpg'){
        $picture = 'upload/'.$articleData['arID'].'.jpg';
    }else{
        $picture = 'images/none.jpg';
    }

    echo    '<a href="php/article.php?id=',urlencode($articleData['arID']),'">',
                '<figure>',
                    '<img src="',$picture,'" alt="',$titre,'">',
                    '<figcaption>',$titre,'</figcaption>',
                '</figure>',
            '</a>';

 
}

/**
 * Printing an articles block
 * @param Array $articles The articles to print
 */
function fpl_print_articleBlock($articles,$title){
    echo '<section>',
            '<h2>',$title,'</h2>';
                foreach($articles as $article){
                    fpl_print_article($article);
                }
    echo '</section>';
}

/**
 * Print the horoscope
 */
function fpl_print_horoscope(){
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
 * Function returning an array of 3different articles from those of the first two sections
 * @param Array $articles       The articles list
 * @param Array $aLaUne         The first section articles
 * @param Array $infoBrulante   The second section articles
 * @return Array                The three selected articles
 */
function fpl_select_articles_incontournable($articles,$aLaUne,$infoBrulante){
    foreach($articles as $value){
        foreach($aLaUne as $article){
            if($article['arID'] == $value['arID']){
                continue 2;
            }
        }
        foreach($infoBrulante as $article){
            if($article['arID'] == $value['arID']){
                continue 2;
            }
        }
        $incontournables[] = $value;
        if(count($incontournables)==3){
            break;
        }
    }
    return $incontournables;
}


// --- Database interactions  --- 

$db = fp_db_connecter();

$query = '('.'SELECT arID, arTitre, 1 AS type
                FROM article
                ORDER BY arDatePublication DESC
                LIMIT 0, 3)
                UNION
                    (SELECT arID, arTitre, 2 AS type
                    FROM article
                    LEFT OUTER JOIN commentaire ON coArticle = arID
                    GROUP BY arID
                    ORDER BY COUNT(coArticle) DESC, rand()
                    LIMIT 0, 3)
                    UNION
                        (SELECT arID, arTitre, 3 AS type
                        FROM article
                        ORDER BY rand()
                        LIMIT 0,9) 
                ORDER BY type ';

$res = fp_db_execute($db,$query);
mysqli_close($db);

$aLaUne = array_slice($res,0,3);
$infoBrulante = array_slice($res,3,3);
$incontournables = fpl_select_articles_incontournable(array_slice($res,6,9),$aLaUne,$infoBrulante);


            
// ---Page generation ---

fp_print_beginPage('accueil',"Le site de désinformation n°1 des étudiants en Licence info",0,1);
    
fpl_print_articleBlock($aLaUne,"&Agrave; la une");
fpl_print_articleBlock($infoBrulante,"L'info brûlante");
fpl_print_articleBlock($incontournables,"Les incontournables");
fpl_print_horoscope();

fp_print_endPage();