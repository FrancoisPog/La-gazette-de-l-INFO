<?php 

/*################################################################
 *
 *              Gazette function librarie          
 * 
 *      > My libraries functions are prefixed by fp_
 *      > My local functions are prefixed by fpl_
 * 
 *      > I organize my functions in several domains :
 *         _print_  -> functions that display html code
 *         _html_   -> processing functions returning html code
 *         _db_     -> interactions functions with database
 *         _str_    -> string processing functions
 *
 ###############################################################*/

/* Constants and includes */

define("BD_SERVER","localhost");
define("BD_NAME","poguet_gazette");
define("BD_USER","poguet_u");
define("BD_PASS","poguet_p");
define("ENCODE","UTF-8");

require_once('bibli_generale.php');

/**
 * Printing the beginning of page in the gazette website 
 * @param String $id    The page's id (for css)
 * @param String $title The page title
 * @param int $deepness The deepness between the page file and the website root
 * @param int $status   The user status (-1 if unlogged)
 */
function fp_print_beginPage($id,$title,$deepness,$status,$pseudo = false){
    $path="";
    for($i = 0 ; $i < $deepness ; $i++){
        $path.="../";
    }
    
    if($status == 3){
        $statusClass = "all";
    }else if($status == 2 || $status == 1){
        $statusClass = "redacOrAdmin";
    }else{
        $statusClass = "simpleUser";
    }       

    echo    '<!DOCTYPE html>',
                '<html lang=\'fr\'>',
                    '<head>',
                        '<meta charset="utf-8">',
                        '<title>',$title,'</title>',
                        '<link rel="stylesheet" href="',$path,'styles/gazette.css">',
                    '</head>',
                    '<body>',
                        '<nav>',
                            '<ul>',
                                '<li><a href="',$path,'index.php">Accueil</a></li>',
                                '<li><a href="',$path,'php/actus.php">Toute l\'actu</a></li>',
                                '<li><a href="',$path,'php/recherche.php">Recherche</a></li>',
                                '<li><a href="',$path,'php/redaction.php">La rédac\'</a></li>';

    if($status == -1){
        echo                    '<li><a href="',$path,'php/connexion.php">Se connecter</a></li>';
    }else{
        echo                       '<li><a href="',$path,'php/compte.php">',($pseudo)?$pseudo:'Se connecter','</a>',
                                        '<ul class="'.$statusClass.'">',
                                            '<li><a href="',$path,'php/compte.php">Mon profil</a></li>',
            ($status > 0 && $status != 2) ? "<li><a href=\"$path"."php/nouveau.php\">Nouvel article</a></li>":'',
                            ($status > 1) ? "<li><a href=\"$path"."php/administration.php\">Administration</a></li>":'',
                                            '<li><a href="',$path,'php/deconnexion.php">Se deconnecter</a></li>',
                                        '</ul>',
                                    '</li>';
    }
                            
            echo            '</ul>',
                        '</nav>',
                        '<header>',
                            '<img src="',$path,'images/titre.png" alt="Titre : La gazette de l\'info">',
                            '<h1>',$title,'</h1>',
                        '</header>',
                        '<main id="',$id,'">';  

}

/**
 * Printing the ending of page in the gazette website 
 */
function fp_print_endPage(){
    echo        '</main>',
                '<footer>',
                    '<p> &copy; Licence Informatique - Janvier 2020 - Tous droits réservés</p>',
                '</footer>',
            '</body>',
        '</html>';    
}

/**
 * Printing an error section 
 * @param $msg The error message 
 */
function fp_make_error($msg){  
    echo    '<section>',
                '<h2>Oups, il y a une erreur...</h2>',
                '<p>La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :</p>',
                '<blockquote>',
                    $msg,
                '</blockquote>',
            '</section>';
}