<?php

ob_start();
require_once("bibli_generale.php");

// --- Fonctions locales ---

/**
 * Affiche la liste des commentaires d'un article
 * @param Array $articleData Les données de l'article
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

function fpl_print_article($data){
    $auteur =  mb_strtoupper(mb_substr($data[0]['utPrenom'],0,1,ENCODE),ENCODE).'.'.mb_convert_case($data[0]['utNom'],MB_CASE_TITLE,ENCODE);

    $auteur = fp_db_protect_exits($auteur);
    $data = fp_db_protect_exits($data);

    $titre = $data[0]['arTitre'];
    $texte = $data[0]['arTexte'];
    
    $dateP = fp_str_toDate($data[0]['arDatePublication']);
    $dateM = $data[0]['arDateModification'];
    $dateM = ($dateM == null)?false:fp_str_toDate($dateM);
    
    $status = $data[0]['utStatut'];
    $link = ($status == 3 || $status == 1) ? 'redaction.php#'.$data[0]['utPseudo']:false;


    echo    '<article>',
                '<h2>',$titre,'</h2>';

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


// --- Vérification de l'id et intéractions base de données ---

if(!fp_check_param($_GET,['id'])){ // Si d'autres clés sont présentes ou que la clé 'id' est absente -> piratage
    header('Location: ../index.php');
    exit(); // --> EXIT : Redirection vers index.php
}

$codeErr = 0;

$id = $_GET['id'];

if(!fp_str_isInt($id)){
    $codeErr = 1;
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
        $codeErr = 2;
    }
    mysqli_close($db);
}



// --- Génération de la page ---

fp_print_beginPage('article','L\'actu',1,0);

    if($codeErr == 0){ // Affichage de l'article
        fpl_print_article($data);
    }else{ // Affichage de la page d'erreur
        $errorMsg = ($codeErr == 1) ? "Identifiant d'article invalide"  :"Aucun n'article ne correspond à cet identifiant";
        fp_make_error($errorMsg);
    }

fp_print_endPage();