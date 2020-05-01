<?php
ob_start();
session_start();
require_once('bibli_gazette.php');
 

function cpl_fetchArticleData($id){
    $db = cp_db_connecter();

    $query = "SELECT arTitre as title, arResume AS abstract, arAuteur AS author, arID AS id, arTexte AS content
                FROM article
                WHERE arID = '$id'";

    $articleData = cp_db_execute($db,$query)[0];

    mysqli_close($db);

    return $articleData;
}

function cpl_hackGuard(){
    $mandatoryKeys = ['title','abstract','content','btnEditArticle'];
    cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
}

function cpl_updateArticle(){
    $db = cp_db_connecter();
    $protected = array_slice($_POST,0,null,true);
    $protected = cp_db_protect_inputs($db,$protected);
    extract($protected);
    $id = $_SESSION['articleID'];
    unset($_SESSION['articleID']);

    date_default_timezone_set('Europe/Paris');
    $time = date('YmdHi');

    $query = "UPDATE article
                SET arTitre = '$title',
                    arResume = '$abstract',
                    arTexte = '$content',
                    arDateModification = '$time'
                WHERE arID = '$id'";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);


}

function cpl_editingProcess(){
    cpl_hackGuard();

    if($errors = cp_article_isValid($_POST)){
        return $errors;
    }

    cpl_updateArticle();

    return 0;

}









/// MAIN

cp_is_logged('../index.php');

cp_print_beginPage('nouveau','Modifier votre article',1,true);

if(isset($_POST['btnEditArticle'])){
    $errors = cpl_editingProcess();
    cp_print_editArticleSection('edition.php',cp_db_protect_outputs($_POST),($errors)?$errors:[],'<p class="success">Votre article a été mis à jour</p>');

}else{
    cp_check_param($_GET,['data']) or cp_session_exit('../index.php');

    $id = cp_decrypt_url($_GET['data'],1)[0];
    $_SESSION['articleID'] = $id;
    $articleData = cpl_fetchArticleData($id);

    if($articleData['author'] != $_SESSION['pseudo']){
        cp_session_exit('../index.php');
    }

    cp_print_editArticleSection('edition.php',$articleData);
}
cp_print_endPage();