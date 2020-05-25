<?php
session_start();
ob_start();
require_once("bibli_gazette.php");

// --- Local functions ---

/**
 * Print article's comments
 * @param Array $articleData The article's data
 */
function cpl_print_comments($articleData,$isLogged){
    if($articleData[0]['coID'] == null){
        echo '<p>Il n\'y a pas encore de commentaire pour cette article !</p>';
        return;
    }

    echo '<ul>';

    foreach($articleData as $comment){
        echo    '<li>',
                    '<p>Commentaire de <strong>',$comment['coAuteur'],'</strong>, le ',cp_str_toDate($comment['coDate']),'</p>',
                    '<blockquote>',
                        cp_html_parseBbCode($comment['coTexte'],false),
                        ($isLogged && ($comment['coAuteur'] == $_SESSION['pseudo'] || $_SESSION['status'] == 1 || $_SESSION['status'] == 3))?cpl_print_deleteCommentBtn($comment['coID']):'',
                    '</blockquote>',
                '</li>';
    }

    echo '</ul>';
}

/**
 * Print the btn to delete a comment
 * @param $id   The comment id
 */
function cpl_print_deleteCommentBtn($id){
    echo '<div class="comment-delete">',
            '<form method="POST" action="article.php?data=',urlencode($_GET['data']),'">',
                '<input type="hidden" value="',urldecode(cp_encrypt_url([$id])),'" name="commentID">',
                cp_print_button('submit','Supprimer ce commentaire','btnDeleteComment'),
            '</form>',
        '</div>';
}

/**
 * Print the section to add a comments
 * @param Array $errors The optional errors to print
 */
function cpl_print_addCommentSection($errors){
    echo '<fieldset class="newComment" id="comment-form">',
            '<legend>Ajouter un commentaire</legend>',
            ($errors)?cp_print_errors($errors,'Veuillez corriger les erreurs suivantes avant de soumettre votre commentaire :'):'',
            '<form method="POST" action="article.php?data=',urlencode($_GET['data']),'">',
                
                '<textarea name="comment" id="comment" maxlength="255" cols="60" rows="6" required >',($errors)?cp_db_protect_outputs($_POST['comment']):'','</textarea>',
                cp_print_button('submit','Publier ce commentaire ','btnNewComment'),
            '</form>',
        '</fieldset>';
}


/**
 * Print an article
 * @param Array $data The article data (not yet protected)
 */
function cpl_print_article($data,$isLogged,$errors){
    // author name formatting
    $auteur =  mb_strtoupper(mb_substr($data[0]['utPrenom'],0,1,ENCODE),ENCODE).'.'.mb_convert_case($data[0]['utNom'],MB_CASE_TITLE,ENCODE);

    // data protection
    $auteur = cp_db_protect_outputs($auteur);
    $data = cp_db_protect_outputs($data);

    $titre = $data[0]['arTitre'];
    $texte = $data[0]['arTexte'];
    
    $dateP = cp_str_toDate($data[0]['arDatePublication']);
    $dateM = $data[0]['arDateModification'];
    $dateM = ($dateM == null)?false:cp_str_toDate($dateM);
    
    $status = $data[0]['utStatut'];
    $link = (($status == 3 || $status == 1) && isset($data[0]['reBio']) ) ? 'redaction.php#'.$data[0]['utPseudo']:false;

    $pictureExist = (file_exists('../upload/'.$data[0]['arID'].'.jpg'));

    $urlID = cp_encrypt_url([$data[0]['arID']]);
    
    echo    ($isLogged && $_SESSION['pseudo'] == $data[0]['arAuteur'])?"<aside class='link-banner'>Vous êtes l'auteur de cet article, <a href='edition.php?data=$urlID'>cliquer ici pour le modifier ou le supprimer</a></aside>":"",

            '<article ',($pictureExist)?'class="with-picture"':'',' >',
                '<h3>',$titre,'</h3>';

                    if($pictureExist){
                        echo '<img src="../upload/',$data[0]['arID'],'.jpg" width="250" height="187" alt="',$titre,'" >';
                    }
        
                echo cp_html_parseBbCode($texte,false),

                '<footer>',
                    '<p>';

                        if($link){
                            echo 'Par <a href="',$link,'">',$auteur,'</a>. Publié le ',$dateP;
                        }else{
                            echo 'Par ',$auteur,'. Publié le ',$dateP;
                        }
                        
                        if($dateM){
                            echo ', modifié le ',$dateM;
                        }

    echo            '</p>',
                '</footer>',
            '</article>',

            '<section id=comments>',
                '<h2>Réactions</h2>',
                cpl_print_comments($data,$isLogged),
                ($isLogged)?cpl_print_addCommentSection($errors):'<p><a href="connexion.php"> Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> pour pouvoir commenter cet article !</p>',
            '</section>';
                

           
}

/**
 * Execute the process to add a now comment
 * @param int $id The article id
 * @return Array|void   Void on success, an array of errors on failure
 */
function cpl_newCommentProcess($id){
    cp_check_param($_POST,['btnNewComment','comment']) or cp_session_exit('../index.php');

    $_POST = array_map('trim',$_POST);
    $errors = array();
    if(strlen($_POST['comment']) == 0){
        $errors[] = 'Le commentaire ne peut pas être vide';
    }elseif(strlen($_POST['comment']) > 255){
        $errors[] = 'Le commentaire doit contenir moins de 256 caractères';
    }

    if(cp_str_containsHTML($_POST['comment'])){
        $errors[] = 'Le commentaire ne doit pas contenir de tags HTML';
    }

    if($errors){
        return $errors;
    }

    $db = cp_db_connecter();

    $author = cp_db_protect_inputs($db,$_SESSION['pseudo']);
    $content = cp_db_protect_inputs($db,$_POST['comment']);
    date_default_timezone_set('Europe/Paris');
    $date = date('YmdHi');


    $query = "INSERT INTO commentaire 
                SET coAuteur = '$author',
                    coTexte = '$content',
                    coDate = '$date',
                    coArticle = '$id'";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);


}

/**
 * Execute the process to delete a comment
 */
function cpl_deleteCommentProcess(){
    cp_check_param($_POST,['btnDeleteComment'],['commentID']) or cp_session_exit('../index.php');
    
    $id = cp_decrypt_url($_POST['commentID'],1)[0];

    $id or cp_session_exit('../index.php'); 

    $db = cp_db_connecter();

    $id = cp_db_protect_inputs($db,$id);

    $query = "DELETE FROM commentaire
                WHERE coID = '$id'";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);
    
    
}

// --- ID verification and database interactions ---

// Only data can be here
cp_check_param($_GET,[],['data']) or cp_session_exit('../index.php');

// If data is not set, -> actus.php
if(!isset($_GET['data'])){
    header('Location: actus.php');
    exit(0);
}




$codeErr = 0;
$id = cp_decrypt_url($_GET['data'],1)[0];

if(!$id){
    $codeErr = 1; // The id key isn't an integer
}else{
    $id = (int)$id;
    $db = cp_db_connecter();
    $errorsComment = 0;

    if(isset($_POST['btnDeleteComment'])){
        cpl_deleteCommentProcess();
    }

    if(isset($_POST['btnNewComment'])){
        $errorsComment = cpl_newCommentProcess($id);
    }

    $query = 'SELECT * 
            FROM (article INNER JOIN utilisateur
            ON utPseudo = arAuteur LEFT JOIN commentaire ON coArticle = arID) 
            LEFT JOIN redacteur ON utPseudo = rePseudo
            WHERE arID = '.mysqli_escape_string($db,$id).
            ' ORDER BY coDate DESC';
    $data = cp_db_execute($db,$query,false);
    if($data == null){
        $codeErr = 2; // No article for this id
    }
    mysqli_close($db);
}

// --- Page generation ---

$isLogged = cp_is_logged();

cp_print_beginPage('article','L\'actu',1,$isLogged);

    if($codeErr == 0){ // print article
        cpl_print_article($data,$isLogged,$errorsComment);
        if(isset($_POST['btnNewComment']) || isset($_POST['btnDeleteComment'])){
            echo '<script>window.location.replace("#',($errorsComment)?'comment-form':'comments','");</script>';
        }
    }else{ // print error page
        $errorMsg = ($codeErr == 1) ? "Identifiant d'article invalide"  :"Aucun n'article ne correspond à cet identifiant";
        cp_print_errorSection($errorMsg);
    }

cp_print_endPage();