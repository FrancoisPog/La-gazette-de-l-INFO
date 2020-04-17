<?php
ob_start();
session_start();
require_once('bibli_gazette.php');

/**
 * Fetch the connected user data in database
 * @return Array $userData
 */
function cpl_fetch_userData(){

    $db = cp_db_connecter();

    $query = 'SELECT *
                FROM utilisateur
                WHERE utPseudo="'. cp_db_protect_inputs($db,$_SESSION['pseudo']).'"';

    $userData = cp_db_execute($db,$query)[0];

    mysqli_close($db);

    return $userData;

}



/**
 * Print the personal user page
 * @param Array $userData The connected user's data
 */
function cpl_print_page_compte($userData){
    $required = true;
    extract($userData);

    $naissance_j = substr($utDateNaissance,-2,2);
    $naissance_m = substr($utDateNaissance,-4,2);
    $naissance_a = substr($utDateNaissance,0,4);

    cp_print_beginPage('compte',"Mon compte",1,$_SESSION['statut'],$_SESSION['pseudo']);

    echo '<section>',
            '<h2>Informations personnelles</h2>',
            '<p>Vous pouvez modifier les informations suivantes.</p>',
            '<form method="POST" action="compte.php">',
                '<table class="form">',
                cp_form_print_radiosLine('Votre civilité :','radSexe',['Monsieur' => 'h','Madame'=> 'f'],$required,$utCivilite,'',true),
                cp_form_print_inputLine('Votre nom :','text','nom','50',$required,'',$utNom,'',true),
                cp_form_print_inputLine('Votre prénom :','text','prenom','60',$required,'',$utPrenom,'',true),
                cp_form_print_DatesLine('Votre date de naissance','naissance',1920,0,$naissance_j,$naissance_m,$naissance_a,1,'Vous devez avoir 18 ans pour vous inscrire',true),
                cp_form_print_inputLine('Votre email :','email','email',255,$required,'',$utEmail,'',true),
                cp_form_print_checkboxLine('cbSpam','J\'accepte de recevoir des tonnes de mails pourris',$required,$utMailsPourris,'Vos données personnelles seront bien évidemment utilisées à des fins commerciales',true),
                cp_form_print_buttonsLine(['Enregistrer','btnModifInfo'],'Réinitialiser',true),
                '</table>',
            '</form>',
            '</section>',
            '<section>',
                '<h2>Authentification</h2>',
                '<p>Vous pouvez modifier votre mot de passe ci-dessous</p>',
                '<form method="POST" action="compte.php">',
                    '<table class="form">',
                        cp_form_print_inputLine('Choisissez un mot de passe :','password','passe1',255,$required),
                        cp_form_print_inputLine('Répétez le mot de passe :','password','passe2',255,$required),
                        cp_form_print_buttonsLine(['Enregistrer','btnModifPasse']),
                    '</table>',
                '</form>',
            '</section>',


    cp_print_endPage();


}




cp_is_logged('../index.php');

$userData = cpl_fetch_userData();

cpl_print_page_compte($userData);