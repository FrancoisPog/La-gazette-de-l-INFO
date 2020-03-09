<?php

ob_start();
require_once("php/bibli_generale.php");

/**
 * Affiche un article
 * @param Array $articleData : Les données de l'article (id+nom)
 */
function make_article($articleData){
    echo '<a href="php/article.php?id=',$articleData['arID'],'">',
            '<figure>',
                '<img src="upload/',$articleData['arID'],'.jpg','" alt="',$articleData['arTitre'],'">',
                '<figcaption>',$articleData['arTitre'],'</figcaption>',
            '</figure>',
        '</a>';
}

/**
 * Affiche un block d'articles
 * @param Array $articles : La liste des articles à afficher
 */
function make_article_block($articles){
    foreach($articles as $article){
        make_article($article);
    }
}

/**
 * Affiche l'horoscope
 */
function make_horoscope(){
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



$aLaUne[] = array('arTitre'=>'Un mouchard dans un corrigé de Langages du Web','arID'=>'10');
$aLaUne[] = array('arTitre'=>'Votez pour l\'hymne de la Licence','arID'=>'9');
$aLaUne[] = array('arTitre'=>'L\'amphi Sciences Naturelles bientôt renommé Amphi Mélenchon','arID'=>'8');

$infoBrulante[] = array('arTitre'=>'Sondage : allez-vous réussir votre année ?', 'arID'=>'1');
$infoBrulante[] = array('arTitre'=>'Une famille de pingouins s\'installe dans l\'amphi B', 'arID'=>'7');
$infoBrulante[] = array('arTitre'=>'Le Président Macron obtient sa Licence d\'Informatique en EAD', 'arID'=>'3');

$incontournables[] = array('arTitre'=>'Il leur avait annoncé "Je vais vous défoncer" l\'enseignant relaxés','arID'=>'2');
$incontournables[] = array('arTitre'=>'Donald Trump veut importer les CMI aux Etats-Unis','arID'=>'4');
$incontournables[] = array('arTitre'=>'Le calendier des Dieux de la Licence bientôt disponible','arID'=>'5');



fp_begin_gaz_page("Accueil","Le site de désinformation n°1 des étudiants en Licence info",0,"styles/gazette.css",2);

    fp_begin_tag('main',['id'=>'accueil']);

        fp_begin_gaz_section("A la une");
            make_article_block($aLaUne);
        fp_end_gaz_section();

        fp_begin_gaz_section("L'info brûlante");
            make_article_block($infoBrulante);
        fp_end_gaz_section();

        fp_begin_gaz_section("Les incontournables");
            make_article_block($incontournables);
        fp_end_gaz_section();

        fp_begin_gaz_section("Horoscope de la semaine",['id'=>'horoscope']);
            make_horoscope();
        fp_end_gaz_section();

    fp_end_tag('main');

fp_end_gaz_page();