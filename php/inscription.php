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
    $mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2','btnInscription'];
    $optionalKeys = ['cbSpam','cbCGU','radSexe'];
    
    cp_check_param($_POST,$mandatoryKeys,$optionalKeys) or cp_session_exit('../index.php');

    // If the date fields values are invalid -> hacking
    cp_isValid_date($_POST['naissance_j'],$_POST['naissance_m'],$_POST['naissance_a']) or cp_session_exit('../index.php');
    
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

    if(!isset($_POST['radSexe'])){
        $errors[] = 'Vous devez choisir une civilité';
    }

    // Last/First name

    foreach(['nom','prénom'] as $value){
        $tmp = $_POST[str_replace('é','e',$value)];

        $maxLength = ($value == 'nom') ? 50 : 60;
        $res = cp_isValid_name($tmp,$maxLength);
        
        if($res == 1){
            $errors[] = "Le $value ne doit pas être vide";
            continue;
        }
        if($res == 2){
            $errors[] = "Le $value ne doit pas contenir de tags HTML";
        }
        
        if($res == 3){
            $errors[] = "Le $value doit contenir moins de $maxLength caractères";
        }
    }

    if(!cp_isValid_age($_POST['naissance_j'],$_POST['naissance_m'],$_POST['naissance_a'])){
        $errors[] = 'Vous devez avoir plus de 18 ans pour vous inscrire';
    }

    // Email
    $emailValid = cp_isValid_email($_POST['email']);
    if($emailValid == 1){
        $errors[] = 'L\'adresse email n\'est pas valide';
    }else if($emailValid == 2){
        $errors[] = 'L\'adresse mail doit contenir moins de 256 caractères';
    }



    // Passe1 et Passe2
    $passesValid = cp_isValid_passe($_POST['passe1'],$_POST['passe2']);
    if($passesValid == 1 ){
        $errors[] = 'Le mot de passe ne doit pas être vide';
    }else if($passesValid == 2 ){
        $errors[] = 'Les mots de passes doivent être identiques';
    }else if($passesValid == 3){
        $errors[] = 'Le mot de passe doit contenir moins de 256 caractères';
    }

    

    
    if(!isset($_POST['cbCGU'])){
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
    $dateNaissance = $naissance_a*10000+$naissance_m*100+$naissance_j;
    $spam = (isset($cbSpam)) ? 1:0;
    $passe = password_hash($passe1,PASSWORD_DEFAULT);


    $query = "INSERT INTO utilisateur SET 
                utPseudo = '$pseudo', 
                utNom = '$nom', 
                utPrenom = '$prenom', 
                utEmail = '$email', 
                utPasse = '$passe', 
                utDateNaissance = '$dateNaissance', 
                utCivilite = '$radSexe',
                utStatut = '0', 
                utMailsPourris = '$spam' ";

    cp_db_execute($db,$query,false,true);

    $_SESSION['pseudo'] = $pseudo;
    $_SESSION['statut'] = 0;

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
 * Print the registration forms in the page
 * @param Array $errors The potential errors 
 */
function cpl_print_register_forms($errors = []){
    $required = true;
    cp_print_beginPage('inscription','Inscription',1,-1);
    echo '<section>',
            '<h2>Formulaire d\'inscription</h2>',
            '<p>Pour vous inscrire, remplissez le formulaire ci-dessous.</p>',
            (count($errors)!=0) ? cpl_print_Errors($errors):'',
            '<form method="POST" action="inscription.php">',
                '<table class="form">',
                    cp_form_print_inputLine('Choisissez un pseudo :',"text",'pseudo',20,$required,'4 caractères minimum',($errors)?htmlentities($_POST['pseudo']):'',"Le pseudo doit contenir entre 4 et 20 chiffres ou lettres minuscules non-accentuées.",true),
                    
                    cp_form_print_radiosLine('Votre civilité :','radSexe',['Monsieur'=>'h','Madame'=>'f'],$required,($errors && isset($_POST['radSexe']))?htmlentities($_POST['radSexe']):'','',true),
                    
                    cp_form_print_inputLine('Votre nom :',"text",'nom',50,$required,'',($errors)?htmlentities($_POST['nom']):'','',true),
                    
                    cp_form_print_inputLine('Votre prénom :',"text",'prenom',60,$required,'',($errors)?htmlentities($_POST['prenom']):'','',true),
                    
                    cp_form_print_DatesLine('Votre date de naissance :','naissance',1920,0,($errors)?htmlentities($_POST['naissance_j']):0,($errors)?htmlentities($_POST['naissance_m']):0,($errors)?htmlentities($_POST['naissance_a']):0,-1,"Vous devez avoir 18 ans pour vous inscrire.",true),
                    
                    cp_form_print_inputLine('Votre email :',"email",'email',255,$required,'',($errors)?htmlentities($_POST['email']):'','',true),
                    
                    cp_form_print_inputLine('Choisissez un mot de passe :',"password",'passe1',255,$required,'',($errors)?htmlentities($_POST['passe1']):'','',true),
                    
                    cp_form_print_inputLine('Répétez le mot de passe :',"password",'passe2',255,$required,'',($errors)?htmlentities($_POST['passe2']):'','',true),
                    
                    cp_form_print_checkboxLine('cbCGU',"J'ai lu et accepte les conditions générales d'utilisation",$required,isset($_POST['cbCGU']),'Vous les trouverez <a href="#">ici</a>.',true),
                    
                    cp_form_print_checkboxLine('cbSpam',"J'accepte de recevoir des tonnes de mails pourris",false,isset($_POST['cbSpam']),'Vos données personnelles seront bien évidemment utilisées à des fins commerciales.',true),
                    
                    cp_form_print_buttonsLine(['S\'inscrire','btnInscription'],'Réinitialiser',true),
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

if(isset($_POST['btnInscription'])){
    $errors = cpl_registeringProcess(); // no return if success
    cpl_print_register_forms($errors);
    
}else{
    cpl_print_register_forms();
}
    