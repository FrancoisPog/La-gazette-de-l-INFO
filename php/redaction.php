<?php 
ob_start();
session_start();
require_once('bibli_gazette.php');

/**
 * Fetch the editors data in database
 * @return Array The editors data sorted by category id
 */
function cpl_fetch_editor_data(){
    $query = 'SELECT *
                FROM utilisateur, redacteur, categorie
                WHERE utilisateur.utPseudo = redacteur.rePseudo
                AND redacteur.reCategorie = categorie.catID
                AND utilisateur.utStatut >= 1
                AND utilisateur.utStatut <> 2
                ORDER BY catID';

    $db = cp_db_connecter();

    $result = cp_db_execute($db,$query,false);

    $sorted_result = array();

    if($result == null){
        return null;
    }

    foreach($result as $data){
        $sorted_result[$data['catID']][] = $data;
    }

    return $sorted_result;

}

/**
 * Get the category title with the category id
 * @param int       The category ID
 * @return String   The category title
 */
function cpl_get_category_title($catID){
    switch($catID){
        case 1 : 
            return 'Rédacteur en chef';
        case 2 : 
            return 'Nos premiers violons';
        case 3 : 
            return 'Nos sous-fifres';
        default : 
            return 'Employé lambda';
    }
}

/**
 * Print a section for a category
 * @param $categoryData The category data
 */
function cpl_print_category($categoryData){
    $catTitle = cpl_get_category_title($categoryData[0]['catID']);
    echo '<section>',
            '<h2>',$catTitle,'</h2>';
            foreach($categoryData as $editor){
                $fullName = mb_convert_case($editor['utPrenom'],MB_CASE_TITLE,ENCODE).' '.mb_convert_case($editor['utNom'],MB_CASE_TITLE,ENCODE);

                $fullName = cp_db_protect_outputs($fullName);
                $editor = cp_db_protect_outputs($editor);

                $function = (isset($editor['reFonction']))?$editor['reFonction']:null;
                $pseudo = $editor['utPseudo'];

                if(file_exists('../upload/'.$pseudo.'.jpg')){
                    $picture = '../upload/'.$pseudo.'.jpg';
                }else{
                    $picture = '../images/anonyme.jpg';
                }

                echo '<article id="',$pseudo,'" ',($function)?'class="haveFunction"':'',' >',
                        '<img src="',$picture,'" alt="',$pseudo,'" title="',$fullName,'">',
                        '<h3>',$fullName,'</h3>',
                        ($function)?('<h4>'.$function.'</h4>'):'',
                        '<div class="biography">',cp_html_parseBbCode(str_replace("\r\n"," ",$editor['reBio'])),'</div>',                     
                    '</article>';
            }
    echo '</section>';
}

/**
 * Print the redaction page
 * @param boolean $isLogged True is the user is logged in
 */
function cpl_print_page_redac($isLogged){

    cp_print_beginPage('redaction','La rédac',1,$isLogged);

    echo '<section>',
            '<h2>Le mot de la rédaction</h2>',
            '<p>',
                'Passionnés par le journalisme d\'investigation depuis notre plus jeune âge, nous avons créé en 2019 ce site ',
                'pour répondre à un réel besoin : celui de fournir une information fiable et précise sur la vie de la ',
                '<abbr title="Licence Informatique">L-INFO</abbr> ',
                'de <a href="https://www.univ-fcomte.fr/">l\'Université de Franche-Comté</a>.',
            '</p>',
            '<p>',
                'Découvrez les hommes et les femmes qui composent l\'équipe de choc de la Gazette de L-INFO.',
            '</p>',

        '</section>';

        $editorData = cpl_fetch_editor_data();
        if($editorData != null){
            foreach($editorData as $category){
                cpl_print_category($category);
            }
        }

    echo '<section class="type1">',
            '<h2>La Gazette de L-INFO recrute !</h2>',
            '<p>',
                'Si vous souhaitez vous aussi faire partie de notre team, rien de plus simple. Envoyez-nous un mail grâce au lien dans le menu de navigation, et rejoignez l\'équipe.',
            '</p>',
        '</section>';

    cp_print_endPage();

}



// main

$isLogged = cp_is_logged();
cpl_print_page_redac($isLogged);