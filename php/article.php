<?php
session_start();
ob_start();
require_once("bibli_generale.php");

// --- Local functions ---

/**
 * Print article's comments
 * @param Array $articleData The article's data
 */
function fpl_print_comments($articleData){
    if($articleData[0]['coID'] == null){
        echo '<p>Il n\'y a pas encore de commentaire pour cette article !</p>';
        return;
    }

    echo '<ul>';

    foreach($articleData as $comment){
        echo    '<li>',
                    '<p>Commentaire de <strong>',$comment['coAuteur'],'</strong>, le ',fp_str_toDate($comment['coDate']),'</p>',
                    '<blockquote>',fp_html_parseBbCode($comment['coTexte']),'</blockquote>',
                '</li>';
    }

    echo '</ul>';
}

/**
 * Print an article
 * @param Array $data The article data (not yet protected)
 */
function fpl_print_article($data){
    // author name formatting
    $auteur =  mb_strtoupper(mb_substr($data[0]['utPrenom'],0,1,ENCODE),ENCODE).'.'.mb_convert_case($data[0]['utNom'],MB_CASE_TITLE,ENCODE);

    // data protection
    $auteur = fp_db_protect_outputs($auteur);
    $data = fp_db_protect_outputs($data);

    $titre = $data[0]['arTitre'];
    $texte = $data[0]['arTexte'];
    
    $dateP = fp_str_toDate($data[0]['arDatePublication']);
    $dateM = $data[0]['arDateModification'];
    $dateM = ($dateM == null)?false:fp_str_toDate($dateM);
    
    $status = $data[0]['utStatut'];
    $link = ($status == 3 || $status == 1) ? 'redaction.php#'.$data[0]['utPseudo']:false;


    echo    '<article>',
                '<h3>',$titre,'</h3>';

                    if(file_exists('../upload/'.$data[0]['arID'].'.jpg')){
                        echo '<img src="../upload/',$data[0]['arID'],'.jpg" alt="',$titre,'" >';
                    }
        
                echo fp_html_parseBbCode(str_replace("\r\n"," ",$texte)),

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
            '<section>',
                '<h2>Réactions</h2>',
                fpl_print_comments($data),
                '<p><a href="connexion.php"> Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> pour pouvoir commenter cet article !</p>',
            '</section>';
                

           
}


// --- ID verification and database interactions ---

// if invalid keys -> index
if(!fp_check_param($_GET,['id'])){
    header('Location: ../index.php');
    exit(); 
}

$codeErr = 0;

$id = $_GET['id'];

if(!fp_str_isInt($id)){
    $codeErr = 1; // The id key isn't an integer
}else{
    $id = (int)$id;
    $db = fp_db_connecter();
    $query = 'SELECT * 
            FROM article INNER JOIN utilisateur
            ON utPseudo = arAuteur LEFT JOIN commentaire ON coArticle = arID
            WHERE arID = '.mysqli_escape_string($db,$id).
            ' ORDER BY coDate DESC';
    $data = fp_db_execute($db,$query,false);
    if($data == null){
        $codeErr = 2; // No article for this id
    }
    mysqli_close($db);
}



// --- Page generation ---

$isLogged = fp_is_logged();

fp_print_beginPage('article','L\'actu',1,($isLogged)?$_SESSION['statut']:-1,($isLogged)?$_SESSION['pseudo']:false);

    if($codeErr == 0){ // print article
        fpl_print_article($data);
    }else{ // print error page
        $errorMsg = ($codeErr == 1) ? "Identifiant d'article invalide"  :"Aucun n'article ne correspond à cet identifiant";
        fp_make_error($errorMsg);
    }

fp_print_endPage();