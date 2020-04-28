<?php

ob_start();
session_start();
require_once('bibli_gazette.php');


function cpl_hackGuard(){
    
    $mandatoryKeys = ['title','abstract','content','btnNewArticle'];
    cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
    
}

function cpl_checkMistakes(){
    $errors = array();
    $_POST = array_map('trim',$_POST);
    $translate = ['title'=>'titre','abstract'=>'résumé','content'=>'contenu'];

    foreach(['title','abstract','content'] as $element){
        $french = $translate[$element];
        
        if($err = cp_isValid_articleElement($_POST[$element])){
            if($err == 1){
                $errors[] = "Le $french ne doit pas être vide";
            }else{
                $errors[] = "Le $french ne doit pas contenir de tags html";
            }
        }
    }

    return ($errors)?$errors:0;
}

function cpl_insertInDatabase(){
    $db = cp_db_connecter();

    $_POST = cp_db_protect_inputs($db,$_POST);
    extract($_POST);
    $pseudo = $_SESSION['pseudo'];
    date_default_timezone_set('Europe/Paris');
    $time = date('YmdHi');

    $query = "INSERT INTO article SET 
                arTitre = '$title',
                arResume = '$abstract',
                arTexte = '$content',
                arDatePublication = '$time',
                arAuteur = '$pseudo' ";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);
}

function cpl_newArticleProcess(){
    cpl_hackGuard();
    if($errors = cpl_checkMistakes()){
        return $errors;
    }

    cpl_insertInDatabase();

    return 0;
}


function cpl_print_page($errors = []){

    cp_print_beginPage('nouveau','Rédiger un nouvel article',1,true);

    $_POST = cp_db_protect_outputs($_POST);
    $title = (isset($_POST['title']))?$_POST['title']:'';
    $abstract = (isset($_POST['abstract']))?$_POST['abstract']:'';
    $content = (isset($_POST['content']))?$_POST['content']:'';

    echo '<section>',
            '<h2>Nouvel article</h2>',
            '<p>Rédiger un nouvel article ci dessous : </p>',
            ($errors)?cp_print_errors($errors):'',
                '<form action="nouveau.php" method="POST">',
                    '<table class="form">',
                        cp_form_print_inputLine('Titre de l\'article : ','text','title',250,true,'',$title),
                        cp_form_print_textAreaLine('Résumé de l\'article : ','abstract',$abstract,80,7,true,'La page d\'accueil afffiche les 300 premiers caractéres du résumé'),
                        cp_form_print_textAreaLine('Contenu de l\'article :','content',$content,80,25,true),
                        cp_form_print_buttonsLine(['Enregistrer','btnNewArticle'],'Réinitialiser',false,true,'','Aucune sauvegarde n\'est encore effectuée, êtes-vous certain de vouloir réinitialiser l`\'article ?'),
                    '</table>',
                '</form>',


          '</section>',  

    cp_print_endPage();

}







// --- Main --- 

cp_is_logged('../index.php');
($_SESSION['status'] != 1 && $_SESSION['status'] != 3) && cp_session_exit('../index.php');


if(isset($_POST['btnNewArticle'])){
    $errors = cpl_newArticleProcess();
    cpl_print_page(($errors)?$errors:0);
    // Si pas d'erreur, afficher page success et liens
}else{
    cpl_print_page();
}
