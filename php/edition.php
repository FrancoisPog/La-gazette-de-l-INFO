<?php
ob_start();
session_start();
require_once('bibli_gazette.php');

define('EDIT_ARTICLE',1);
define('DELETE_ARTICLE',2);
define('CHANGE_PICTURE',3);
 
/**
 * Fetch the article data in database
 * @param int $id   The article id
 * @return Array    The article data
 */
function cpl_fetchArticleData($id){
    $db = cp_db_connecter();

    $query = "SELECT arTitre as title, arResume AS abstract, arAuteur AS author, arID AS id, arTexte AS content
                FROM article
                WHERE arID = '$id'";

    $articleData = cp_db_execute($db,$query)[0];

    mysqli_close($db);

    return $articleData;
}

/**
 * Check if it's a hacking case
 * @param int $processType  The type of process
 * @return void|exit        Exit the script if it's a hacking case
 */
function cpl_hackGuard($processType){
    switch($processType){
        case EDIT_ARTICLE:
            $mandatoryKeys = ['title','abstract','content','btnEditArticle'];
            break;
        case DELETE_ARTICLE : 
            $mandatoryKeys = ['popup-conf','btnDeleteArticle'];
            break;
    }
   
    cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
}

/**
 * Update the article in database
 */
function cpl_updateArticle(){
    $db = cp_db_connecter();

    $protected = array_slice($_POST,0,null,true);
    $protected = cp_db_protect_inputs($db,$protected);
    extract($protected);

    $id = $_SESSION['articleID'];

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

/**
 * Execute the process to edit an article
 * @return Array|0  0 on success, otherwise an array of errors
 */
function cpl_editingProcess(){
    cpl_hackGuard(EDIT_ARTICLE);

    if($errors = cp_article_isValid($_POST)){
        return $errors;
    }

    $id = $_SESSION['articleID'];
    if($_FILES['picture']['name'] != ''){
        move_uploaded_file($_FILES['picture']['tmp_name'],realpath('..')."/upload/$id.jpg");
    }
    cpl_updateArticle();

    return 0;

}

/**
 * Delete an article from database
 */
function cpl_deleteArticle(){
    $db = cp_db_connecter();

    $id = cp_db_protect_inputs($db,$_SESSION['articleID']);

    $query = "DELETE FROM commentaire
                WHERE coArticle = $id;
                DELETE FROM article
                WHERE arID = $id ";

    $res = mysqli_multi_query($db,$query);
    
    if(!$res){
        cp_db_error($db,$query);
    }
    
    mysqli_close($db);


}

/**
 * Print the page to edit an article
 * @param $articleData  The article data from database
 * @param Array $errors The optional errors to display
 */
function cpl_print_editArticlePage($articleData,$errors = []){

    cp_print_beginPage('edition','Modifier votre article',1,true);

    echo '<aside class="link-banner">Vous pouvez consulter votre article <a href="article.php?data=',cp_encrypt_url([$_SESSION['articleID']]),'">ici</a></aside>';

    if(isset($_POST['btnEditArticle'])){
        cp_print_editArticleSection('edition.php',cp_db_protect_outputs($_POST),($errors)?$errors:[],'<p class="success">Votre article a été mis à jour</p>');
    }else{
        cp_print_editArticleSection('edition.php',$articleData);
    }

    echo '<section>',
            '<h2>Supprimer l\'article</h2>',
            '<form action="edition.php" method="POST">',
                cp_print_popUp('Supprimer l\'article','Confirmation de la suppression','La suppression d\'un article est irréversible, vous confirmez la suppression ?','submit','Supprimer','btnDeleteArticle'),
            '</form>', 
        '</section>',

    cp_print_endPage();
    
}




/// MAIN

cp_is_logged('../index.php');



if(isset($_POST['btnEditArticle'])){
    $errors = cpl_editingProcess();
    cpl_print_editArticlePage([],$errors);

}elseif(isset($_POST['btnDeleteArticle'])){

    cpl_hackGuard(DELETE_ARTICLE);
    cpl_deleteArticle();


    header('Location: ../index.php');
    exit(0);

}else{
    cp_check_param($_GET,['data']) or cp_session_exit('../index.php');

    $id = cp_decrypt_url($_GET['data'],1)[0];
    $_SESSION['articleID'] = $id;
    $articleData = cpl_fetchArticleData($id);

    if($articleData['author'] != $_SESSION['pseudo']){
        cp_session_exit('../index.php');
    }

    cpl_print_editArticlePage($articleData);
    
}
