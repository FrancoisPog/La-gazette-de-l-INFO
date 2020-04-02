<?php
require_once("bibli_generale.php");
session_start();
ob_start();

/**
 * Check if it's a hacking case
 * Exit the script if it's a hacking case
 */
function fpl_hackGuard(){
    fp_check_param($_POST,['pseudo','passe','btnConnexion']) or fp_session_exit('../index.php');

    (strlen($_POST['pseudo']) == 0 || strlen($_POST['passe']) == 0) and fp_session_exit('../index.php');
}

/**
 * Check if the user made a mistake during connection 
 * @return mixed 0 if there are no error, else it returning an array with the errors
 */
function fpl_check_inputs(){
    $_POST = array_map('trim',$_POST);
    $errors = array();

    if(strlen($_POST['pseudo']) == 0){
       $errors[] = 'Veuillez renseigner un pseudo non vide';
    }

    if(strlen($_POST['passe']) == 0){
        $errors[] = 'Veuillez renseigner un mot de passe non vide';
    }

    if(count($errors) != 0){
        return $errors;
    }
    return 0;
}

/**
 * Check if the pseudo and the password match
 * @return mixed exit if the connection is a success, else it returning an array with the errors
 */
function fpl_check_user_data(){
    $db = fp_db_connecter();

    $query = 'SELECT utPasse,utStatut
                FROM utilisateur
                WHERE utPseudo = "'.fp_db_protect_inputs($db,$_POST['pseudo']).'"';

    $pass = fp_db_execute($db,$query,false);

    if($pass == null){
        return 1;
    }
   
    if(password_verify($_POST['passe'],$pass)){
        return 1;
    }

    $_SESSION['pseudo'] = $_POST['pseudo'];
    $_SESSION['statut'] = $pass[0]['utStatut'];

    header('Location: ../index.php');
    exit(0);

    

}

/**
 * Print the connection page
 * @param $errors The potential errors
 */
function fpl_print_log_form($errors = false){
    fp_print_beginPage('connexion','Connexion',1,-1);

    echo '<section>',
            '<h2>Formuaire de connexion</h2>',
            '<p>Pour vous identifier, remplissez le formulaire ci-dessous :</p>',
            ($errors) ? '<p class="error">Echec d\'authentification. Utilisateur inconnu ou mot de passe incorrect.</p>':'',
            '<form method="POST" action="connexion.php">',
                '<table class="form">',
                    fp_print_inputLine('Pseudo :','text','pseudo',20),
                    fp_print_inputLine('Mot de passe :','password','passe',255),
                    '<tr>',
                        '<td><input type="submit" value="Se connecter" name="btnConnexion"></td>',
                        '<td><input type="reset" value="Annuler"></td>',
                    '</tr>',
                '</table>',
            '</form>',
            '<p>Pas encore inscrit ? N\'attendez pas, <a href="inscription.php">inscrivez-vous</a> !</p> ',
        '</section>';

    fp_print_endPage();
}

/**
 * Execute the logging process
 * @return mixed exit if success, else 1
 */
function fpl_logging_process(){
    fpl_hackGuard();
    
    $res = fpl_check_user_data();

    if($res != 0){
        return 1;
    }

}





// if the user comes to this page while already logged in -> compte.php
if(fp_is_logged()){
    header('Location: compte.php');
    exit(0);
}

if(isset($_POST['btnConnexion'])){
    $res = fpl_logging_process();
    
    if($res != 0){
        fpl_print_log_form(true);
    }
}else{
    fpl_print_log_form();
}