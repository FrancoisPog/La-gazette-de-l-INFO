<?php
session_start();
ob_start();
require_once("bibli_gazette.php");

// --- Local functions ---

/**
 * Print article's comments
 * @param Array $articleData The article's data
 */
function cpl_print_comments($articleData){
    if($articleData[0]['coID'] == null){
        echo '<p>Il n\'y a pas encore de commentaire pour cette article !</p>';
        return;
    }

    echo '<ul>';

    foreach($articleData as $comment){
        echo    '<li>',
                    '<p>Commentaire de <strong>',$comment['coAuteur'],'</strong>, le ',cp_str_toDate($comment['coDate']),'</p>',
                    '<blockquote>',cp_html_parseBbCode($comment['coTexte']),'</blockquote>',
                '</li>';
    }

    echo '</ul>';
}

/**
 * Print an article
 * @param Array $data The article data (not yet protected)
 */
function cpl_print_article($data,$isLogged){
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
    $link = ($status == 3 || $status == 1) ? 'redaction.php#'.$data[0]['utPseudo']:false;

    $pictureExist = (file_exists('../upload/'.$data[0]['arID'].'.jpg'));

    $urlID = cp_encrypt_url([$data[0]['arID']]);
    
    echo    ($isLogged && $_SESSION['pseudo'] == $data[0]['arAuteur'])?"<aside class='link-banner'>Vous êtes l'auteur de cet article, <a href='edition.php?data=$urlID'>cliquer ici pour le modifier ou le supprimer</a></aside>":"",

            '<article ',($pictureExist)?'class="with-picture"':'',' >',
                '<h3>',$titre,'</h3>';

                    if($pictureExist){
                        echo '<img src="../upload/',$data[0]['arID'],'.jpg" width="250" height="187" alt="',$titre,'" >';
                    }
        
                echo cp_html_parseBbCode($texte),

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
                cpl_print_comments($data),
                '<p><a href="connexion.php"> Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> pour pouvoir commenter cet article !</p>',
            '</section>';
                

           
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
    $query = 'SELECT * 
            FROM article INNER JOIN utilisateur
            ON utPseudo = arAuteur LEFT JOIN commentaire ON coArticle = arID
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
        cpl_print_article($data,$isLogged);
    }else{ // print error page
        $errorMsg = ($codeErr == 1) ? "Identifiant d'article invalide"  :"Aucun n'article ne correspond à cet identifiant";
        cp_print_errorSection($errorMsg);
    }

cp_print_endPage();