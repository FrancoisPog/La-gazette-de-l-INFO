<?php
require_once("bibli_gazette.php");
session_start();
ob_start();

/**
 * Check if it's a hacking case
 * Exit the script if it's a hacking case
 */
function fpl_hackGuard(){
    fp_check_param($_POST,['pseudo','passe','btnConnexion']) or fp_session_exit('../index.php');
}

/**
 * Check if the pass is empty, and if the pattern of pseudo is correct (so if not, we can avoid a database connection)
 * @return boolean True if there are no error, else false
 */
function fpl_check_inputs(){
    $_POST = array_map('trim',$_POST);
    

    if(!preg_match("/^[0-9a-z]{4,20}$/",$_POST['pseudo'])){
       return false;
    }

    if(strlen($_POST['passe']) == 0){
        return false;
    }

    return true;
}

/**
 * Check if the pseudo and the password match
 * @return mixed if the pseudo and pass match,it returning the user data in an array, else false
 */
function fpl_check_user_data(){
    $db = fp_db_connecter();

    $query = 'SELECT utPasse,utStatut
                FROM utilisateur
                WHERE utPseudo = "'.fp_db_protect_inputs($db,$_POST['pseudo']).'"';

    $pass = fp_db_execute($db,$query,false)[0];

    mysqli_close($db);

    if($pass == null){
        return false;
    }
   
    if(!password_verify($_POST['passe'],$pass['utPasse'])){
        return false;
    }

    return [$_POST['pseudo'],$pass['utStatut']];

}

/**
 * Connect the user and redirects to the origin page
 * @param $statut The user statut
 */
function fpl_connection($userData){
    
    $page = $_SESSION['origin_page'];
    unset($_SESSION['origin_page']);
   
    $_SESSION['pseudo'] = $userData[0];
    $_SESSION['statut'] = $userData[1];

    header('Location: '.$page);
    exit(0);
}

/**
 * Print the connection page
 * @param $errors The potential errors
 */
function fpl_print_connection_form($errors = false){
    fp_print_beginPage('connexion','Connexion',1,-1);
    $required = false;
    echo '<section>',
            '<h2>Formuaire de connexion</h2>',
            '<p>Pour vous identifier, remplissez le formulaire ci-dessous :</p>',
            ($errors) ? '<p class="error">Echec d\'authentification. Utilisateur inconnu ou mot de passe incorrect.</p>':'',
            '<form method="POST" action="connexion.php">',
                '<table class="form">',
                    fp_print_inputLine('Pseudo :','text','pseudo',20,$required),
                    fp_print_inputLine('Mot de passe :','password','passe',255,$required),
                    fp_print_buttonsLine(['Se connecter','btnConnexion'],'Annuler'),
                '</table>',
            '</form>',
            '<p>Pas encore inscrit ? N\'attendez pas, <a href="inscription.php">inscrivez-vous</a> !</p> ',
        '</section>';

    fp_print_endPage();
}

/**
 * Execute the logging process
 * @return mixed exit if success, else -1
 */
function fpl_logging_process(){
    fpl_hackGuard();

    if(!fpl_check_inputs()){
        return -1;
    }

    $statut = fpl_check_user_data();
    if($statut == false){
        return -1;
    }

    fpl_connection($statut);

}



// if the user comes to this page while already logged in -> compte.php
if(fp_is_logged()){
    header('Location: compte.php');
    exit(0);
}


if(isset($_POST['btnConnexion'])){
    $res = fpl_logging_process(); // no return if success
    fpl_print_connection_form(true);
    
}else{
    // Keep the origin page 
    $_SESSION['origin_page'] = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '../index.php' ;
    fpl_print_connection_form();
}