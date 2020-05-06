<?php 

/*################################################################
 *
 *              Gazette functions librarie          
 * 
 *      > My libraries functions are prefixed by cp_
 *      > My local functions are prefixed by cpl_
 * 
 *      > I organize my functions in several domains :
 *         _print_  -> functions that display html code
 *         _html_   -> processing functions returning html code
 *         _db_     -> interactions functions with database
 *         _str_    -> string processing functions
 *
 ###############################################################*/

/* Constants and includes */

// define("BD_SERVER","localhost");
// define("BD_NAME","poguet_gazette");
// define("BD_USER","poguet_u");
// define("BD_PASS","poguet_p");

define("BD_SERVER","db38127-poguet-gazette.sql-pro.online.net");
define("BD_NAME","db38127_poguet_gazette");
define("BD_USER","db115427");
define("BD_PASS","Poguet_p");

define("ENCODE","UTF-8");
define("ENCRYPTION_KEY","lJ4sMUKYK2DvDXMFr5lyCw==");

require_once('bibli_generale.php');


// PRINT

/**
 * Printing the beginning of page in the gazette website 
 * @param String $id        The page's id (for css)
 * @param String $title     The page title
 * @param int $deepness     The deepness between the page file and the website root
 * @param int $status       The user status (-1 if unlogged in)
 * @param String $pseudo    The (optional) user pseudo (if logged in)
 */
function cp_print_beginPage($id,$title,$deepness,$isLogged = false){
    $path="";
    for($i = 0 ; $i < $deepness ; $i++){
        $path.="../";
    }

    if($isLogged){
        $pseudo = $_SESSION['pseudo'];
        $status = $_SESSION['status'];
    }else{
        $status = -1;
    }
    
    // useful for transition
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
                        '<title>La Gazette de L-INFO | ',$title,'</title>',
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
        echo                       '<li><a>',$pseudo,'</a>',
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
function cp_print_endPage(){
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
function cp_print_errorSection($msg){  
    echo    '<section>',
                '<h2>Oups, il y a une erreur...</h2>',
                '<p>La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :</p>',
                '<blockquote>',
                    $msg,
                '</blockquote>',
            '</section>';
}

/**
 * Print the errors of registration 
 * @param Array $errors The errors to print
 */
function cp_print_errors($errors){
    echo '<div class="error">',
            '<p>Les erreurs suivantes ont été relevées lors de votre inscription :</p>',
            '<ul>';
                foreach($errors as $error){
                    echo '<li>',$error,'</li>';
                }
    echo    '</ul>',
        '</div>';
}


// GAZETTE DATA VALIDITY

/**
 * Test if a date is valid
 * @param int $day      The date's day
 * @param int $month    The date's month 
 * @param int $year     The date's year
 * @return boolean
 */
function cp_isValid_date($day,$month,$year) {
    return (cp_intIsBetween($day,1,31) && 
            cp_intIsBetween($month,1,12) && 
            cp_intIsBetween($year,1900,2020));
}

/**
 * Test if a civility is valid
 * @param char $civility    The civility
 * @return boolean
 */
function cp_isValid_civility($civility){
    return preg_match('/^[hf]$/',$civility);
}

/**
 * Test if a pseudo is valid
 * @param char $pseudo   The pseudo
 * @return boolean
 */
function cp_isValid_pseudo($pseudo){
    return preg_match("/^[0-9a-z]{4,20}$/",$pseudo);
}

/**
 * Test if a name is valid
 * @param char $name        The name
 * @param int $maxlength    The name's max length     
 * @return int 0:valid, 1:empty, 2:tags_html, 3:too_long
 */
function cp_isValid_name($name,$maxLength){
    if(strlen($name)==0){
        return 1;
    }
    if(cp_str_containsHTML($name)){
        return 2;
    }

    if(strlen($name) > $maxLength){
        return 3;
    }
}

/**
 * Test if an age is valid
 * @param int $birthday      The birth day
 * @param int $birthmonth    The birth month
 * @param int $birthyear     The birth year
 * @return boolean  
 */
function cp_isValid_age($birthDay,$birthMonth,$birthYear){
    return date('Ymd') - ($birthYear*10000+$birthMonth*100+$birthDay) >= 180000;
}


/**
 * Test if an email is valid
 * @param String $email The email
 * @return int 0:valid, 1:invalid, 2:too_long
 */
function cp_isValid_email($email){
    if(!preg_match("/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$/",$email)){
        return 1;
    }
    if(strlen($email) > 255){
        return 2;
    }

    return 0;
}

/**
 * Test if the pass is valid
 * @param String $passe1 The first pass
 * @param String $passe2 The second pass
 * @return int 0:valid, 1:empty, 2:different, 2:too_long
 */
function cp_isValid_pass($passe1, $passe2 = true){
    if(strlen($passe1) == 0 ){
        return 1;
    }else if($passe1 != $passe2 ){
        return 2;
    }else if(strlen($passe1) > 255){
        return 3;
    }
    return 0;
}


function cp_isValid_articleElement($element){
    if(strlen($element) == 0){
        return 1;
    }
    if(cp_str_containsHTML($element)){
        return 2;
    }

    return 0;
}


// ARTICLE

/**
 * Check if an article is valid
 * @param Array $data   The article data (title, abstract, content)
 * @return Array|0        The errors list, 0 if no error
 */
function cp_article_isValid($data){
    $errors = array();
    $data = array_map('trim',$data);
    $translate = ['title'=>'titre','abstract'=>'résumé','content'=>'contenu'];

    foreach(['title','abstract','content'] as $element){
        $french = $translate[$element];
        
        if($err = cp_isValid_articleElement($data[$element])){
            if($err == 1){
                $errors[] = "Le $french ne doit pas être vide";
            }else{
                $errors[] = "Le $french ne doit pas contenir de tags html";
            }
        }
    }

    if($_FILES['picture']['name'] != ''){
        $errors = cp_picture_isValid($_FILES['picture']);
    }

   

    

    return ($errors)?$errors:0;
}

/**
 * Check if a picture is valid
 * @param FILE $picture The uploaded picture
 * @return Array        The array of errors
 */
function cp_picture_isValid($picture){
    $errors = array();
    switch($picture['error']){
        case 1 : 
        case 2 :
            $errors[] = 'Le fichier est trop volumineux';
            return $errors;
        case 3 : 
            $errors[] = 'Erreur de transfert';
            return $errors;
        case 4 : 
            $errors[] = 'Fichier introuvable';
            return $errors;
    }

    if($picture['type'] != 'image/jpeg'){
        $errors[] = 'Le format de la photo doit être "jpeg"';
    }
    if(!is_uploaded_file($picture['tmp_name'])){
        $errors[] = 'Erreur interne';
    }

    return $errors;
}


/**
 * Print a form to edit an article
 * @param String $page      The form's page
 * @param Array $data       An array with the optional default values
 * @param Array $errors      An optional array with errors to display
 * @param String $onSuccess Text to display in case of success
 */
function cp_print_editArticleSection($page,$data,$errors = [],$onSuccess = ''){
    $title = (isset($data['title']))?$data['title']:'';
    $abstract = (isset($data['abstract']))?$data['abstract']:'';
    $content = (isset($data['content']))?$data['content']:'';
    $id = ($page != 'nouveau.php' && isset($_SESSION['articleID']))?$_SESSION['articleID']:'';
    $pictures = ($id != '' && file_exists("../upload/$id.jpg")) ? "../upload/$id.jpg" : '';

    echo '<section>',
            '<h2>Votre article</h2>',
            '<p>Editer votre article ci dessous : </p>',
            ($errors)?cp_print_errors($errors):$onSuccess,
                '<form action="',$page,'" method="POST" enctype="multipart/form-data">',
                    '<table class="form row">',
                        cp_form_print_inputLine('Titre de l\'article : ','text','title',250,true,'',$title),
                        cp_form_print_textAreaLine('Résumé de l\'article : ','abstract',$abstract,80,7,true,'La page d\'accueil afffiche les 300 premiers caractéres du résumé'),
                        cp_form_print_textAreaLine('Contenu de l\'article :','content',$content,80,25,true),
                        cp_form_print_file('picture','Image d\'illustration : ',false,'Pour ne pas être déformé l\'image doit-être au format 4/3'),
                        ($pictures != '')?'<tr><td colspan="2"><img title="Image d\'illustration actuelle" width="250" height="187" src="'.$pictures.'"></td></tr>':'',
                        cp_form_print_buttonsLine(2,['Enregistrer','btnEditArticle'],'Réinitialiser',false,($page == 'nouveau.php'),'','Aucune sauvegarde n\'est encore effectuée, êtes-vous certain de vouloir réinitialiser l`\'article ?'),
                        
                    '</table>',
                '</form>',


          '</section>';
}




// STR
/**
 * Parsing date from database to string 
 * @param int $date The date to parse
 * @return String   The date in correct format
 */
function cp_str_toDate($date){
    setlocale(LC_TIME, "fr_FR");
    return utf8_encode(strftime("%e %B %G &agrave; %Hh%M",strtotime($date)));
}
