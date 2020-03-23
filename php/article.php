<?php

ob_start();
require_once("bibli_generale.php");

// --- Fonctions locales ---

/**
 * Affiche la liste des commentaires d'un article
 * @param Array $articleData Les données de l'article
 */
function fpl_make_comments($articleData){
    if($articleData[0]['coID'] == null){
        fp_begin_tag('p');
            echo 'Il n\'y a pas encore de commentaire pour cette article ! ';
        fp_end_tag('p');
        return;
    }

    fp_begin_tag('ul');

    foreach($articleData as $comment){
        fp_begin_tag('li');
            fp_begin_tag('p');
                echo 'Commentaire de <strong>',$comment['coAuteur'],'</strong>, le ',fp_date_format($comment['coDate']);
            fp_end_tag('p');

            fp_begin_tag('blockquote');
                echo fp_parseBbCode($comment['coTexte']);
            fp_end_tag('blockquote');
        fp_end_tag('li');
    }

    fp_end_tag('ul');
}


// --- Vérification de l'id et intéractions base de données ---

if(!fp_check_param('get',['id'])){ // Si d'autres clés sont présentes ou que la clé 'id' est absente -> piratage
    header('Location: ../index.php');
    exit(); // --> EXIT : Redirection vers index.php
}

$codeErr = 0;

$id = $_GET['id'];

if(!fp_str_isInt($id)){
    $codeErr = 1;
}else{
    $id = (int)$id;
    $db = fp_bd_connecter();
    $query = 'SELECT * 
            FROM article INNER JOIN utilisateur
            ON utPseudo = arAuteur LEFT JOIN commentaire ON coArticle = arID
            WHERE arID = '.mysqli_escape_string($db,$id).
            ' ORDER BY coDate DESC';
    $data = fp_queryToArray($db,$query);
    if($data == null){
        $codeErr = 2;
    }
    mysqli_close($db);
}



// --- Génération de la page ---

fp_begin_gaz_page('L\'actu','L\'actu',1,'../styles/gazette.css',0);

    fp_begin_tag('main',['id'=>'article']);

        if($codeErr == 0){ // Affichage de l'article

            $titre = $data[0]['arTitre'];
            $texte = $data[0]['arTexte'];
            $initPrenom = mb_strtoupper(mb_substr($data[0]['utPrenom'],0,1,ENCODE),ENCODE);
            $nom = mb_convert_case($data[0]['utNom'],MB_CASE_TITLE,ENCODE);

            $dateP = fp_date_format($data[0]['arDatePublication']);
            $dateM = $data[0]['arDateModification'];
            $dateM = ($dateM == null)?false:fp_date_format($dateM);
            
            $status = $data[0]['utStatut'];
            $link = ($status == 3 || $status == 1) ? 'redaction.php#'.$data[0]['utPseudo']:false;
            
           
            fp_begin_tag('article');

                fp_begin_tag('h2');
                    echo $titre;
                fp_end_tag('h2');

                if(file_exists('../upload/'.$id.'.jpg')){
                    fp_begin_tag('img',['src'=>'../upload/'.$id.'.jpg' , 'alt'=>$titre]);
                }
                
                
                echo fp_parseBbCode(str_replace("\r\n"," ",$texte));

                fp_begin_tag('footer');

                    fp_begin_tag('p');
                        if($link){
                            echo 'Par <a href="',$link,'">',$initPrenom,'.',$nom,'</a>. Publié le ',$dateP;
                        }else{
                            echo 'Par ',$initPrenom,'.',$nom,'. Publié le ',$dateP;
                        }
                        
                        if($dateM){
                            echo ', modifié le ',$dateM;
                        }
                    fp_end_tag('p');

                fp_end_tag('footer');

            fp_end_tag('article');

            fp_begin_gaz_section('Réactions');

                fpl_make_comments($data);

                fp_begin_tag('p');
                    echo '<a href="connexion.php"> Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> pour pouvoir commenter cet article !';
                fp_end_tag('p');

            fp_end_gaz_section();

        }else{ // Affichage de la page d'erreur
            
           $errorMsg = ($codeErr == 1) ? "Identifiant d'article invalide"  :"Aucun n'article ne correspond à cet identifiant";
            
           fp_make_error($errorMsg);
        }

    fp_end_tag('main');

fp_end_gaz_page();