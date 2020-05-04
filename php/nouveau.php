<?php

ob_start();
session_start();
require_once('bibli_gazette.php');

/**
 * Check if it's a hacking case
 * @return void|exit Exit the script if it's a hacking case
 */
function cpl_hackGuard(){
    $mandatoryKeys = ['title','abstract','content','btnEditArticle'];
    $optionalKeys = ['popup-conf','picture'];
    cp_check_param($_POST,$mandatoryKeys,$optionalKeys) or cp_session_exit('../index.php');
}

/**
 * Insert the new article in database 
 */
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

/**
 * Execute the process to test and insert the new article
 * @return Array|0 The array of errors, or 0 if success
 */
function cpl_newArticleProcess(){
    cpl_hackGuard();
    if($errors = cp_article_isValid($_POST)){
        return $errors;
    }

    cpl_insertInDatabase();

    return 0;
}


/**
 * Get the new article ID
 * @return int The article's id
 */
function cpl_getNewArticleId(){
    $db = cp_db_connecter();

    $query = "SELECT MAX(arID) AS id FROM article";

    $id = cp_db_execute($db,$query)[0]['id'];

    mysqli_close($db);

    return $id;
}

/**
 * Print the section of success
 * @param int $id   The new article's id
 */
function cpl_print_newArticeSuccess($id){
    echo '<section>',
            '<h2>Nouvel article</h2>',
            '<h3>Votre nouvel article vient d\'être publié !</h3>',
            '<p>Vous pouvez le consulter <a href="article.php?data=',cp_encrypt_url([$id]),'">ici</a>.   </p>',
            '<p>Vous pouvez le modifier <a href="edition.php?data=',cp_encrypt_url([$id]),'">ici</a>.  </p>',
        '</section>';
}




// --- Main --- 

// If the user isn't an editor -> index.php
cp_is_logged('../index.php');
($_SESSION['status'] != 1 && $_SESSION['status'] != 3) && cp_session_exit('../index.php');

cp_print_beginPage('nouveau','Rédiger un nouvel article',1,true);

if(isset($_POST['btnEditArticle'])){
    $errors = cpl_newArticleProcess();
    
    if(!$errors){
        $id = cpl_getNewArticleId();
        move_uploaded_file($_FILES['picture']['tmp_name'],realpath('..')."/upload/$id.jpg");
        cpl_print_newArticeSuccess($id);
        exit(0);
    }
    
    cp_print_editArticleSection('nouveau.php',cp_db_protect_outputs($_POST),$errors);
    
}else{
    cp_print_editArticleSection('nouveau.php',[]);
}
cp_print_endPage();