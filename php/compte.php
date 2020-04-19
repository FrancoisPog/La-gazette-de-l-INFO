<?php
ob_start();
session_start();
require_once('bibli_gazette.php');

// --- local functions ---

/**
 * Fetch the connected user data in database
 * @return Array $userData
 */
function cpl_fetch_userData(){

    $db = cp_db_connecter();

    $query = '('.'SELECT utPseudo AS pseudo, utNom AS last_name, utPrenom AS first_name, utEmail AS email, utDateNaissance AS birthday, utCivilite AS civility, utMailsPourris AS spam, 1 AS type 
                FROM utilisateur
                WHERE utPseudo="'. cp_db_protect_inputs($db,$_SESSION['pseudo']).'" ) UNION (SELECT 0,0,0, utEmail,0,0,0, 2 AS type FROM utilisateur WHERE utPseudo <> "'. cp_db_protect_inputs($db,$_SESSION['pseudo']).'" )';

    $userData = cp_db_execute($db,$query);

    // We keep all others user's email for check later if the user want to change
    $_SESSION['emails'] = array_slice($userData,1);

    $userData = $userData[0];

    mysqli_close($db);

    return $userData;

}


// EDIT DATA


/**
 * Check if it's a hacking case
 * @return void|exit Exit the script if it's a hacking case
 */
function cpl_hackGuardEditData(){
     /*
        Only the spam checkbox can be missing
        So, if other keys are missing -> hacking.
    */
    $mandatoryKeys = ['btnEditData','civility','last_name','first_name','birthday_y','birthday_m','birthday_d','email'];
    $optionalKeys = ['spam'];

    cp_check_param($_POST,$mandatoryKeys,$optionalKeys) or cp_session_exit('../index.php');

    // If the date fields values are invalid -> hacking
    cp_isValid_date($_POST['birthday_d'],$_POST['birthday_m'],$_POST['birthday_y']) or cp_session_exit('../index.php');

     // If the value of civility is different of 'h' and 'f' -> hacking
    cp_isValid_civility($_POST['civility']) or cp_session_exit('../index.php');

}

/**
 * Check if the user made mistakes while editing his data
 * @return Array The errors list
 */
function cpl_checkEditingMistakes(){
    $_POST = array_map('trim',$_POST);
    $errors = array();

    // Last/First name
    foreach(['last_name','first_name'] as $value){
        $french = ($value == 'last_name') ? 'nom' : 'prénom';

        $maxLength = ($value == 'last_name') ? 50 : 60;
        $res = cp_isValid_name($_POST[$value],$maxLength);
        
        if($res == 1){
            $errors[] = "Le $french ne doit pas être vide";
            continue;
        }
        if($res == 2){
            $errors[] = "Le $french ne doit pas contenir de tags HTML";
            continue;
        }
        
        if($res == 3){
            $errors[] = "Le $french doit contenir moins de $maxLength caractères";
            continue;
        }
    }

    // Age
    if(!cp_isValid_age($_POST['birthday_d'],$_POST['birthday_m'],$_POST['birthday_y'])){
        $errors[] = 'Vous devez avoir plus de 18 ans pour vous inscrire';
    }

    // Email
    $emailValid = cp_isValid_email($_POST['email']);
    if($emailValid == 1){
        $errors[] = 'L\'adresse email n\'est pas valide';
    }else if($emailValid == 2){
        $errors[] = 'L\'adresse mail doit contenir moins de 256 caractères';
    }

    // var_dump($_SESSION['emails']);

    foreach($_SESSION['emails'] as $email){
        if($email['email'] == $_POST['email'] ){
            $errors[] = 'L\'adresse mail est déjà utilisée';
            break;
        }
    }

    return (count($errors) == 0)?0:$errors;

}

/**
 * Update the user data in database
 */
function cpl_updateData(){
    $db = cp_db_connecter();

    $_POST = cp_db_protect_inputs($db,$_POST);
    extract($_POST);
    $birthDate = $birthday_y*10000+$birthday_m*100+$birthday_d;
    $spam = (isset($spam)) ? 1:0;

    $query = "UPDATE utilisateur
                SET utCivilite = '$civility',
                    utNom = '$last_name',
                    utPrenom = '$first_name',
                    utEmail = '$email',
                    utDateNaissance = '$birthDate',
                    utMailsPourris = '$spam'
                WHERE  utPseudo = '". cp_db_protect_inputs($db,$_SESSION['pseudo'])."'";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);
}

/**
 * Edit the user's data 
 * @return Array|0 The array of errors, 0 if there are no errors
 */
function cpl_editDataProcess(){
    // Avoid hacking case
    cpl_hackGuardEditData();

    // check user mistakes
    if(($errors = cpl_checkEditingMistakes()) != 0){
        return $errors;
    }

    cpl_updateData();

    return 0;
    
}


// EDIT PASS


/**
 * Update the user's pass on database
 */
function cpl_updatePass(){
    $db = cp_db_connecter();
    $pass = $_POST['pass1'];
    $pass =  password_hash($pass,PASSWORD_DEFAULT);

    $query = "UPDATE utilisateur
                SET utPasse = '$pass'
                WHERE  utPseudo = '". cp_db_protect_inputs($db,$_SESSION['pseudo'])."'";

    cp_db_execute($db,$query,false,true);

    mysqli_close($db);

}

/**
 * Edit the user's password
 * @return Array|0 The array of errors, 0 if there are no errors
 */
function cpl_editPassProcess(){
    $mandatoryKeys = ['pass2','pass1','btnEditPass'];
    cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');

    $errors = array();
    $_POST = array_map('trim',$_POST);

    // Passe1 et Passe2
    $passValid = cp_isValid_pass($_POST['pass1'],$_POST['pass2']);
    if($passValid == 1 ){
        $errors[] = 'Le mot de passe ne doit pas être vide';
    }else if($passValid == 2 ){
        $errors[] = 'Les mots de passes doivent être identiques';
    }else if($passValid == 3){
        $errors[] = 'Le mot de passe doit contenir moins de 256 caractères';
    }

    if($errors){
        return $errors;
    }
    
    cpl_updatePass();

    return 0;

}

// PRINT

/**
 * Print the errors of registration 
 * @param Array $errors The errors to print
 */
function cpl_print_Errors($errors){
    echo '<div class="error">',
            '<p>Les erreurs suivantes ont été relevées lors de votre inscription :</p>',
            '<ul>';
                foreach($errors as $error){
                    echo '<li>',$error,'</li>';
                }
    echo    '</ul>',
        '</div>';
}


/**
 * Print the personal user page
 * @param Array $userData The connected user's data
 */
function cpl_print_page_compte($userData = [], $errors = []){
    $required = true;


    if(isset($_POST['btnEditData'])){
        $_POST = cp_db_protect_outputs($_POST);
        extract($_POST);
        $spam = isset($spam);
    }else{
        extract($userData);
        $birthday_d = substr($birthday,-2,2);
        $birthday_m = substr($birthday,-4,2);
        $birthday_y = substr($birthday,0,4);
    }
    

    cp_print_beginPage('compte',"Mon compte",1,$_SESSION['status'],$_SESSION['pseudo']);

    echo '<section>',
            '<h2>Informations personnelles</h2>',
            '<p>Vous pouvez modifier les informations suivantes :</p>',
            (count($errors) != 0 && isset($_POST['btnEditData'])) ? cpl_print_Errors($errors) : '',
            (count($errors) == 0 && isset($_POST['btnEditData'])) ? '<p class="success">Vos informations ont été mise à jour avec succès.</p>':'',
            '<form method="POST" action="compte.php">',
                '<table class="form">',
                    cp_form_print_radiosLine('Votre civilité :','civility',['Monsieur' => 'h','Madame'=> 'f'],$required,$civility,'',true),
                    cp_form_print_inputLine('Votre nom :','text','last_name','50',$required,'',$last_name,'',true),
                    cp_form_print_inputLine('Votre prénom :','text','first_name','60',$required,'',$first_name,'',true),
                    cp_form_print_DatesLine('Votre date de naissance :','birthday',1920,0,$birthday_d,$birthday_m,$birthday_y,1,'Vous devez avoir 18 ans pour être inscrire',true),
                    cp_form_print_inputLine('Votre email :','email','email',255,$required,'',$email,'',true),
                    cp_form_print_checkboxLine('spam','J\'accepte de recevoir des tonnes de mails pourris',false,$spam,'Vos données personnelles seront bien évidemment utilisées à des fins commerciales',true),
                    cp_form_print_buttonsLine(['Enregistrer','btnEditData'],'Réinitialiser',true),
                '</table>',
            '</form>',
            '</section>',
            '<section>',
                '<h2>Authentification</h2>',
                '<p>Vous pouvez modifier votre mot de passe ci-dessous :</p>',
                (count($errors) != 0 && isset($_POST['btnEditPass'])) ? cpl_print_Errors($errors) : '',
                (count($errors) == 0 && isset($_POST['btnEditPass'])) ? '<p class="success">Votre mot de passe à été mis à jour avec succès.</p>':'',
                '<form method="POST" action="compte.php">',
                    '<table class="form">',
                        cp_form_print_inputLine('Choisissez un mot de passe :','password','pass1',255,$required),
                        cp_form_print_inputLine('Répétez le mot de passe :','password','pass2',255,$required),
                        cp_form_print_buttonsLine(['Enregistrer','btnEditPass']),
                    '</table>',
                '</form>',
            '</section>',


    cp_print_endPage();
}






// --- Page generation


cp_is_logged('../index.php');

if(isset($_POST['btnEditData'])){  
    $errors = cpl_editDataProcess();
    cpl_print_page_compte([],($errors == 0)?[]:$errors);  

}else if(isset($_POST['btnEditPass'])){
    $errors = cpl_editPassProcess();
    $userData = cpl_fetch_userData();
    cpl_print_page_compte($userData,($errors == 0)?[]:$errors);

}else{
    $userData = cpl_fetch_userData();
    cpl_print_page_compte($userData);   
}


/** TODO
 * mettre en forme le message vert
 * optimiser : si pas de mofif, pas de co à la db
 */
