<?php
require_once("bibli_gazette.php");
ob_start();

// This script is a beta version, many functions will be improved in the final version of inscription.php

// --- Local functions

/**
 * Check is an integer is include between two others
 * @param $number The integer to test
 * @param $min The min limit
 * @param $max The max limit
 */
function fpl_intIsBetween($number,$min,$max){
    if(!fp_str_isInt($number)){
        return false;
    }

    return $number >= $min && $number <= $max;
}

/**
 * Redirects to the index pages
 */
function fpl_go_index(){
    header('Location: ../index.php');
    exit(0);
}

/**
 * Check if it's a hacking case
 * Exit the script if it's a hacking case
 */
function fpl_hackGuard(){
    /*
        Only the 'spam check box' key may be missing, 
        indeed all others are required for registration and the "required" attribute is set.
        So, if they are absent, -> hacking.
    */
    $mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2', 'cbCGU','btnInscription','radSexe'];
    $optionalKeys = ['cbSpam'];

    fp_check_param($_POST,$mandatoryKeys,$optionalKeys) or fpl_go_index();

    // If one of the text fields is empty (before trim) although the "required" attribute is positioned there -> hacking.
    ( strlen($_POST['pseudo']) == 0 || 
        strlen($_POST['nom']) == 0 || 
        strlen($_POST['prenom']) == 0 || 
        strlen($_POST['email']) == 0 || 
        strlen($_POST['passe1']) == 0 || 
        strlen($_POST['passe2']) == 0 ) and fpl_go_index();

    // If the date fields are not integers or are invalid -> hacking
    (fpl_intIsBetween($_POST['naissance_j'],1,31) && 
        fpl_intIsBetween($_POST['naissance_m'],1,12) && 
        fpl_intIsBetween($_POST['naissance_a'],1900,2020)) or fpl_go_index();
    
    // If the value of civility is different from 'h' and 'f' -> hacking
    preg_match('/^[hf]$/',$_POST['radSexe']) or fpl_go_index();

    
}

/**
 * Check if the user made a mistake during registration 
 * @return 0 if there are no error, else it returning an array with the errors
 */
function fpl_checkInputsError(){
    $_POST = array_map('trim',$_POST);
    $errors = array();

    // Pseudo
    if(!preg_match("/^[0-9a-z]{4,20}$/",$_POST['pseudo'])){
        $errors[] = 'Le pseudo doit contenir entre 4 et 20 chiffres ou lettres minuscule';
    }

    // Email
    if(!preg_match("/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$/",$_POST['email'])){
        $errors[] = 'L\'adresse email n\'est pas valide';
    }else if(strlen($_POST['email']) > 255){
        $errors[] = 'L\'adresse mail doit contenir moins de 256 caractères';
    }

    // Last/First name
    foreach(['nom','prénom'] as $value){
        $tmp = $_POST[str_replace('é','e',$value)];
        
        if(strlen($tmp)==0){
            $errors[] = "Le $value ne doit pas être vide";
            continue;
        }
        if($tmp != strip_tags($tmp)){
            $errors[] = "Le $value ne doit pas contenir de tags HTML";
        }
        
        $maxLength = ($value == 'nom') ? 50 : 60;
        if(strlen($tmp) > $maxLength){
            $errors[] = "Le $value doit contenir moins de $maxLength caractères";
        }
    }

    // Passe1 et Passe2
    if(strlen($_POST['passe1']) == 0 ){
        $errors[] = 'Le mot de passe ne doit pas être vide';
    }else if($_POST['passe1'] != $_POST['passe2'] ){
        $errors[] = 'Les mots de passes doivent être identiques';
    }else if(strlen($_POST['passe1']) > 255){
        $errors[] = 'Le mot de passe doit contenir moins de 256 caractères';
    }

    if(date('Ymd') - ($_POST['naissance_a']*10000+$_POST['naissance_m']*100+$_POST['naissance_j']) < 180000){
        $errors[] = 'Vous devez avoir plus de 18 ans pour vous inscrire';
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
 * @return 0 if there are no error, else it returning an array with the errors
 */
function fpl_checkAlreadyUsed($db,$pseudo,$email){
    

    $query = '('."SELECT utPseudo, 1 AS type
                    FROM utilisateur
                    WHERE utPseudo = '$pseudo') 
                    UNION 
                    (SELECT utPseudo, 2 AS type 
                        FROM utilisateur
                        WHERE utEmail = '$email' )";

    $res = fp_db_execute($db,$query);


    // Si le pseudo ou le mail est déjà utilisé
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
function fpl_registerUser($db,$userData){
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

    fp_db_execute($db,$query,false,true);


}


/**
 * Execute the registerion 
 * @return Boolean 0 if there are no error, else it returning an array with the errors
 */
function fpl_registeringProcess(){
    
    fpl_hackGuard();

    if(($errors = fpl_checkInputsError()) != 0){
        return $errors;
    }


    // --- Check if the pseudo and email isn't already used

    $db = fp_db_connecter();

    $userData = fp_db_protect_inputs($db,$_POST);

    if(($errors = fpl_checkAlreadyUsed($db,$userData['pseudo'],$userData['email'])) != 0){
        mysqli_close($db);
        return $errors;
    }

    // Registering of new user

    fpl_registerUser($db,$userData);

    mysqli_close($db);

    header('Location: protegee.php');
    return 0;
}

/**
 * Print the errors of registration 
 */
function fpl_print_Errors($errors){
    echo '<aside class="error">',
            '<p>Les erreurs suivantes ont été relevées lors de votre inscription :</p>',
            '<ul>';
                foreach($errors as $error){
                    echo '<li>',$error,'</li>';
                }
    echo    '</ul>',
        '</aside>';
}

/**
 * Print the registration forms in the page
 */
function fpl_print_register_forms($errors = []){
    
    //var_dump($_POST);
    fp_print_beginPage('inscription','Inscription',1,-1);
    echo '<section>',
            '<h2>Formulaire d\'inscription</h2>',
            '<p>Pour vous inscrire, remplissez le formulaire ci-dessous.</p>',
            (count($errors)!=0) ? fpl_print_Errors($errors):'',
            '<form method="POST" action="inscription_4.php">',
                '<table class="form">',
                    fp_print_inputLine('Choisissez un pseudo :',"text",'pseudo',20,true,'4 caractères minimum',($errors)?htmlentities($_POST['pseudo']):false),
                    fp_print_inputRadioLine('Votre civilité :','radSexe',['Monsieur'=>'h','Madame'=>'f'],true,($errors)?htmlentities($_POST['radSexe']):false),
                    fp_print_inputLine('Votre nom :',"text",'nom',50,true,false,($errors)?htmlentities($_POST['nom']):false),
                    fp_print_inputLine('Votre prénom :',"text",'prenom',60,true,false,($errors)?htmlentities($_POST['prenom']):false),
                    fp_print_DatesLine('Votre date de naissance :','naissance',1920,0,($errors)?htmlentities($_POST['naissance_j']):0,($errors)?htmlentities($_POST['naissance_m']):0,($errors)?htmlentities($_POST['naissance_a']):0,-1),
                    fp_print_inputLine('Votre email :',"email",'email',255,true,false,($errors)?htmlentities($_POST['email']):false),
                    fp_print_inputLine('Choisissez un mot de passe :',"password",'passe1',255,true,false,($errors)?htmlentities($_POST['passe1']):false),
                    fp_print_inputLine('Répétez le mot de passe :',"password",'passe2',255,true,false,($errors)?htmlentities($_POST['passe2']):false),
                    '<tr>',
                        '<td colspan="2">',
                            fp_print_inputCheckbox('cbCGU',"J'ai lu et accepte les conditions générales d'utilisation",true,isset($_POST['cbCGU'])),
                        '</td>',
                    '</tr>',
                    '<tr>',
                        '<td colspan="2">',
                            fp_print_inputCheckbox('cbSpam',"J'accepte de recevoir des tonnes de mails pourris",false,isset($_POST['cbSpam'])),
                    '</td>',
                    '</tr>',
                    '<tr>',
                        '<td><input type="submit" value="S\'inscrire" name="btnInscription"></td>',
                        '<td><input type="reset" value="Réinitialiser"></td>',
                    '</tr>',
                '</table>',
            '</form>',
        '</section>';
    fp_print_endPage();
}





// MAIN


if(isset($_POST['btnInscription'])){
    $res = fpl_registeringProcess();
    
    if($res != 0){
        fpl_print_register_forms($res);
    }
}else{
    fpl_print_register_forms();
}
    