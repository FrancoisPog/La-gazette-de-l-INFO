<?php

ob_start();
require_once("php/bibli_generale.php");

// --- Fonction locales ---

/**
 * Affiche un article
 * @param Array $articleData : Les données de l'article (id+nom)
 */
function fpl_make_article($articleData){
    $titre = $articleData['arTitre'];
    if(file_exists('upload/'.$articleData['arID']).'.jpg'){
        $picture = 'upload/'.$articleData['arID'].'.jpg';
    }else{
        $picture = 'images/none.jpg';
    }

    fp_begin_tag('a',['href'=>'php/article.php?id='.urlencode($articleData['arID']).'']);

        fp_begin_tag('figure');

            fp_begin_tag('img',['src'=>$picture,'alt'=>$titre]);

            fp_begin_tag('figcaption');
                echo $titre;
            fp_end_tag('figcaption');

        fp_end_tag('figure');

    fp_end_tag('a');



 
}

/**
 * Affiche un block d'articles
 * @param Array $articles : La liste des articles à afficher
 */
function fpl_make_article_block($articles){
    foreach($articles as $article){
        fpl_make_article($article);
    }
}

/**
 * Affiche l'horoscope
 */
function fpl_make_horoscope(){
    echo    '<p>',
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
            '</p>';
}

/**
 * Fonction retournant un tableau de 3 articles différents de ceux des deux premieres sections
 * @param Array $articles La liste d'articles
 * @param Array $aLaUne Les articles présents dans la première section
 * @param Array $infoBrulante Les articles présents dans la deuxième section
 * @return Array Les 3 articles choisis
 */
function fp_select_articles_incontournable($articles,$aLaUne,$infoBrulante){
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


// --- Interactions base de données --- 

$db = fp_bd_connecter();

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

$res = fp_queryToArray($db,$query);
mysqli_close($db);

$aLaUne = array_slice($res,0,3);
$infoBrulante = array_slice($res,3,3);
$incontournables = fp_select_articles_incontournable(array_slice($res,6,9),$aLaUne,$infoBrulante);


            




// --- Génération de la page ---

fp_begin_gaz_page("Accueil","Le site de désinformation n°1 des étudiants en Licence info",0,"styles/gazette.css",1);
    
    fp_begin_tag('main',['id'=>'accueil']);

        fp_begin_gaz_section("&Agrave; la une");
            fpl_make_article_block($aLaUne);
        fp_end_gaz_section();

        fp_begin_gaz_section("L'info brûlante");
            fpl_make_article_block($infoBrulante);
        fp_end_gaz_section();

        fp_begin_gaz_section("Les incontournables");
            fpl_make_article_block($incontournables);
        fp_end_gaz_section();

        fp_begin_gaz_section("Horoscope de la semaine",['id'=>'horoscope']);
            fpl_make_horoscope();
        fp_end_gaz_section();

    fp_end_tag('main');

fp_end_gaz_page();