<?php
session_start();
ob_start();
require_once("bibli_gazette.php");


// --- Local functions




/**
 * Check if it's a hacking case
 * @return void|exit Exit the script if it's a hacking case
 */
function cpl_hackGuard(){
    /*
        Only checkbox and radio buttons can be missing
        So, if other keys are missing -> hacking.
    */
    $mandatoryKeys = ['pseudo','last_name','first_name','birthday_d','birthday_m', 'birthday_y','email','pass1','pass2','inscriptionBtn'];
    $optionalKeys = ['spam','GCU','civility'];
    
    cp_check_param($_POST,$mandatoryKeys,$optionalKeys) or cp_session_exit('../index.php');

    // If the date fields values are invalid -> hacking
    cp_isValid_date($_POST['birthday_d'],$_POST['birthday_m'],$_POST['birthday_y']) or cp_session_exit('../index.php');
    
    // If the value of civility is different of 'h' and 'f' (if it was entered) -> hacking
    isset($_POST['radSexe']) and (cp_isValid_civility($_POST['radSexe']) or cp_session_exit('../index.php'));

    
}

/**
 * Check if the user made a mistake during registration 
 * @return mixed 0 if there are no error, else it returning an array with the errors
 */
function cpl_checkInputsError(){
    $_POST = array_map('trim',$_POST);
    $errors = array();

    // Pseudo
    if(!cp_isValid_pseudo($_POST['pseudo'])){
        $errors[] = 'Le pseudo doit contenir entre 4 et 20 chiffres ou lettres minuscule';
    }

    if(!isset($_POST['civility'])){
        $errors[] = 'Vous devez choisir une civilité';
    }

    // Last/First name

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



    // Pass1 et Pass2
    $passesValid = cp_isValid_pass($_POST['pass1'],$_POST['pass2']);
    if($passesValid == 1 ){
        $errors[] = 'Le mot de passe ne doit pas être vide';
    }else if($passesValid == 2 ){
        $errors[] = 'Les mots de passes doivent être identiques';
    }else if($passesValid == 3){
        $errors[] = 'Le mot de passe doit contenir moins de 256 caractères';
    }

    

    
    if(!isset($_POST['GCU'])){
        $errors[] = 'Vous devez accepter les conditions générales d\'utilisation';
    }
    
    if(count($errors) != 0){
        return $errors;
    }else{
        return 0;
    }
}

/**
 * Check if the email or pseudo specified is already used 
 * @param Object $db        The database connecter
 * @param String $pseudo    The specified pseudo
 * @param String $email     The specified email
 * @return mixed            0 if there are no error, else it returning an array with the errors
 */
function cpl_checkAlreadyUsed($db,$pseudo,$email){

    $query = '('."SELECT utPseudo, 1 AS type
                    FROM utilisateur
                    WHERE utPseudo = '$pseudo') 
                    UNION 
                    (SELECT utPseudo, 2 AS type 
                        FROM utilisateur
                        WHERE utEmail = '$email' )";

    $res = cp_db_execute($db,$query);

    // if the pseudo of email is already used
    if($res != null){
        foreach($res as $value){
            if($value['type'] == 1){
                $errors[] = 'Le pseudo est déjà utilisé';
            }else{
                $errors[] = 'L\'adresse mail est déjà utilisée';
            }
        }
        return $errors;
    }
    return 0;
}

/**
 * Register a user in the database
 * @param Object $db        The database connecter
 * @param Array $userData   The user's data (must be protected before calling this function !)
 * @return Boolean          True is the registration is a success  
 */
function cpl_registerUser($db,$userData){
    extract($userData);
    $birthDate = $birthday_y*10000+$birthday_m*100+$birthday_d;
    $spam = (isset($spam)) ? 1:0;
    $pass = password_hash($pass1,PASSWORD_DEFAULT);


    $query = "INSERT INTO utilisateur SET 
                utPseudo = '$pseudo', 
                utNom = '$last_name', 
                utPrenom = '$first_name', 
                utEmail = '$email', 
                utPasse = '$pass', 
                utDateNaissance = '$birthDate', 
                utCivilite = '$civility',
                utStatut = '0', 
                utMailsPourris = '$spam' ";

    cp_db_execute($db,$query,false,true);

    $_SESSION['pseudo'] = $pseudo;
    $_SESSION['status'] = 0;

}


/**
 * Execute the registerion 
 * @return int|Array 0 if there are no error, else it returning an array with the errors
 */
function cpl_registeringProcess(){
    
    // Avoid hacking case
    cpl_hackGuard();

    // Check input errors
    if(($errors = cpl_checkInputsError()) != 0){
        return $errors;
    }


    // Check if the pseudo and email are already used

    $db = cp_db_connecter();

    $userData = cp_db_protect_inputs($db,$_POST);

    if(($errors = cpl_checkAlreadyUsed($db,$userData['pseudo'],$userData['email'])) != 0){
        mysqli_close($db);
        return $errors;
    }

    // Registering of new user
    cpl_registerUser($db,$userData);

    mysqli_close($db);

    header('Location: protegee.php');
    exit(0);
}



/**
 * Print the registration forms in the page
 * @param Array $errors The potential errors 
 */
function cpl_print_register_forms($errors = []){
    $required = true;
    cp_print_beginPage('inscription','Inscription',1,false);
    echo '<section>',
            '<h2>Formulaire d\'inscription</h2>',
            '<p>Pour vous inscrire, remplissez le formulaire ci-dessous.</p>',
            (count($errors)!=0) ? cp_print_errors($errors):'',
            '<form method="POST" action="inscription.php">',
                '<table class="form">',
                    cp_form_print_inputLine('Choisissez un pseudo :',"text",'pseudo',20,$required,'4 caractères minimum',($errors)?htmlentities($_POST['pseudo']):'',"Le pseudo doit contenir entre 4 et 20 chiffres ou lettres minuscules non-accentuées."),
                    
                    cp_form_print_radiosLine('Votre civilité :','civility',['Monsieur'=>'h','Madame'=>'f'],$required,($errors && isset($_POST['civility']))?htmlentities($_POST['civility']):''),
                    
                    cp_form_print_inputLine('Votre nom :',"text",'last_name',50,$required,'',($errors)?htmlentities($_POST['last_name']):''),
                    
                    cp_form_print_inputLine('Votre prénom :',"text",'first_name',60,$required,'',($errors)?htmlentities($_POST['first_name']):''),
                    
                    cp_form_print_DatesLine('Votre date de naissance :','birthday',1920,0,($errors)?htmlentities($_POST['birthday_d']):0,($errors)?htmlentities($_POST['birthday_m']):0,($errors)?htmlentities($_POST['birthday_y']):0,-1,"Vous devez avoir 18 ans pour vous inscrire."),
                    
                    cp_form_print_inputLine('Votre email :',"email",'email',255,$required,'',($errors)?htmlentities($_POST['email']):''),
                    
                    cp_form_print_inputLine('Choisissez un mot de passe :',"password",'pass1',255,$required,'',($errors)?htmlentities($_POST['pass1']):''),
                    
                    cp_form_print_inputLine('Répétez le mot de passe :',"password",'pass2',255,$required,'',($errors)?htmlentities($_POST['pass2']):''),
                    
                    cp_form_print_checkboxLine('GCU',"J'ai lu et accepte les conditions générales d'utilisation",$required,isset($_POST['GCU']),'Vous les trouverez <a href="#">ici</a>.'),
                    
                    cp_form_print_checkboxLine('spam',"J'accepte de recevoir des tonnes de mails pourris",false,isset($_POST['spam']),'Vos données personnelles seront bien évidemment utilisées à des fins commerciales.'),
                    
                    cp_form_print_buttonsLine(2,['S\'inscrire','inscriptionBtn'],'Réinitialiser'),
                '</table>',
            '</form>',
        '</section>';
    cp_print_endPage();
}





// MAIN

// if the user comes to this page while already logged in -> compte.php
if(cp_is_logged()){
    header('Location: compte.php');
    exit(0);
}

if(isset($_POST['inscriptionBtn'])){
    $errors = cpl_registeringProcess(); // no return if success
    cpl_print_register_forms($errors);
    
}else{
    cpl_print_register_forms();
}
    