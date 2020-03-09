<?php

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
                echo 'Commentaire de <strong>',htmlspecialchars($comment['coAuteur']),'</strong>, le ',htmlspecialchars($comment['coDate']);
            fp_end_tag('p');

            fp_begin_tag('blockquote');
                echo htmlspecialchars($comment['coTexte']);
            fp_end_tag('blockquote');
        fp_end_tag('li');
    }

    fp_end_tag('ul');
}

// --- Vérification de l'id et intéractions base de données


if(count($_GET) != 1 || !isset($_GET['id'])){ // Si d'autres clés sont présentes ou que la clé 'id' est absente -> piratage
    header('Location: ../index.php');
    exit(); // --> EXIT : Redirection vers index.php
}

$codeErr = 0;

$id = $_GET['id'];
if(!fp_str_isInt($id)){
    $codeErr = 1;
}else{
    $db = fp_bd_connecter();
    $query = 'SELECT * 
            FROM article INNER JOIN utilisateur
            ON utPseudo = arAuteur LEFT JOIN commentaire ON coArticle = arID
            WHERE arID = '.mysqli_escape_string($db,$id);
    $data = fp_queryToArray($db,$query );
    if($data == null){
        $codeErr = 2;
    }
}



// --- Génération de la page ---

fp_begin_gaz_page("L'actu",'L\'actu',1,'../styles/gazette.css',1);

    fp_begin_tag('main',['id'=>'article']);

        if($codeErr == 0){ // Affichage de l'article

            $titre = htmlspecialchars($data[0]['arTitre']);
            $texte = htmlspecialchars($data[0]['arTexte']);
            $initPrenom = strtoupper(substr(htmlspecialchars($data[0]['utPrenom']),0,1));
            $nom = htmlspecialchars($data[0]['utNom']);
            $dateP = htmlspecialchars($data[0]['arDatePublication']);
           
            fp_begin_tag('article');

                fp_begin_tag('h2');
                    echo $titre;
                fp_end_tag('h2');

                if(file_exists('../upload/'.$id.'.jpg')){
                    fp_begin_tag('img',['src'=>'../upload/'.$id.'.jpg' , 'alt'=>$titre]);
                }

                echo $texte;

                fp_begin_tag('footer');
                    fp_begin_tag('p');
                        echo 'Par <a href="redaction.php">',$initPrenom,'.',$nom,'</a>. Publié le ',$dateP;
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
            
            fp_begin_gaz_section('Oups, il y a une erreur...');

                fp_begin_tag('p');
                    echo 'La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :';
                fp_end_tag('p');

                fp_begin_tag('blockquote');
                    if($codeErr == 1){
                        echo 'Identifiant d\'article non reconnu';
                    }elseif($codeErr == 2){
                        echo 'Aucun article ne correspond à l\'identifiant';
                    }
                fp_end_tag('blockquote');

            fp_end_gaz_section();
        }

    fp_end_tag('main');

fp_end_gaz_page();