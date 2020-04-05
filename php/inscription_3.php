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
 * Print the errors of registration 
 */
function fpl_print_Errors($errors){
    echo '<!DOCTYPE html >',
        '<html lang="fr">',
            '<head>',
                '<title>Inscription</title>',
                '<meta charset="utf-8">',
            '</head>',
            '<body>',
                '<h1>Réception du formulaire \'Inscription utilisateur\'</h1>',
                '<p>Les erreurs suivantes ont été relevées lors de votre inscription :</p>',
                '<ul>';

                foreach($errors as $error){
                    echo '<li>',$error,'</li>';
                }


    echo        '</ul>',
            '</body>',
        '</html>';
}

/**
 * Check if it's a hacking case
 * Exit the script if it's a hacking case
 * (this function will be improved in the final version of inscription.php)
 */
function fpl_hackGuard(){
    /*
        Only the 'spam check box' key may be missing, 
        indeed all others are required for registration and the "required" attribute is set.
        So, if they are absent, -> hacking.
    */
    $mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2', 'cbCGU','btnInscription','radSexe'];
    $optionalKeys = ['cbSpam'];

    $keysAreValid = fp_check_param($_POST,$mandatoryKeys,$optionalKeys);

    // If one of the text fields is empty (before trim) although the "required" attribute is positioned there -> hacking.
    $oneFieldIsEmpty = strlen($_POST['pseudo']) == 0 || strlen($_POST['nom']) == 0 || strlen($_POST['prenom']) == 0 || strlen($_POST['email']) == 0 || strlen($_POST['passe1']) == 0 || strlen($_POST['passe2']) == 0;

    // If the date fields are not integers or are invalid -> hacking
    $dateAreValid = fpl_intIsBetween($_POST['naissance_j'],1,31) && fpl_intIsBetween($_POST['naissance_m'],1,12) && fpl_intIsBetween($_POST['naissance_a'],1900,2020);
    
    // If the value of civility is different from 'h' and 'f' -> hacking
    $civiliteIsValid = preg_match('/^[hf]$/',$_POST['radSexe']);

    if($oneFieldIsEmpty || !$keysAreValid || !$dateAreValid || !$civiliteIsValid){
        header('Location: ../index.php');
        exit;
    }
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
        $maxLength = ($value == 'nom') ? 50 : 60;
        if(strlen($tmp)==0){
            $errors[] = "Le $value ne doit pas être vide";
            continue;
        }
        if($tmp != strip_tags($tmp)){
            $errors[] = "Le $value ne doit pas contenir de tags HTML";
        }
        if(strlen($tmp) > $maxLength){
            $errors[] = "Le $value doit contenir moins de $maxLength caractères";
        }
    }

    // Passe1 et Passe2
    if(strlen($_POST['passe1']) == 0){
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

    $res = fp_db_execute($db,$query,false,true);

    return $res;
}



// --- Arguments check 
fpl_hackGuard();

if(($errors = fpl_checkInputsError()) != 0){
    fpl_print_Errors($errors);
    exit(0);
}


// --- Check if the pseudo and email isn't already used

$db = fp_db_connecter();

$userData = fp_db_protect_inputs($db,$_POST);

if(($errors = fpl_checkAlreadyUsed($db,$userData['pseudo'],$userData['email'])) != 0){
    fpl_print_Errors($errors);
    mysqli_close($db);
    exit(0);
}


// Registering of new user

fpl_registerUser($db,$userData);

mysqli_close($db);

echo '<!DOCTYPE html >',
        '<html lang="fr">',
            '<head>',
                '<title>Inscription</title>',
                '<meta charset="utf-8">',
            '</head>',
            '<body>',
                '<h1>Réception du formulaire d\'inscription</h1> ',
                '<p>Un nouvel utilisateur a bien été enregistré</p>',
            '</body>',
        '</html>';
