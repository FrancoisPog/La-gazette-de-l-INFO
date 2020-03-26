<?php
require_once("bibli_generale.php");
ob_start();


// --- Fonctions locales

/**
 * Fonction redirigeant vers la page d'accueil 'index.php'
 */
function fpl_goIndex(){
    header('Location: ../index.php');
    exit;
}

/**
 * Fonction vérifiant si un entier est compris entre deux autres
 * @param $number L'entier à tester
 * @param $min La borne minimum incluse
 * @param $max La borne maximum incluse
 */
function fpl_intIsBetween($number,$min,$max){
    if(!fp_str_isInt($number)){
        return false;
    }

    return $number >= $min && $number <= $max;
}

/**
 * Fonction affichant les erreurs de saisies du formulaire 
 */
function fpl_displayErrors($errors){
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




// --- Vérification des arguments

/*
    Seule la clé de la checkbox concernant les spams peut être absente, en effet toutes les autres
    sont obligatoire à l'inscription et l'attribut 'required' est positionné.
    Donc si elles sont absentes, --> piratage .

    De plus, si un des champs de type texte est vide (avant le trim) alors que l'attribut 'required' est positionné dessus --> piratage.
*/
$mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2', 'cbCGU','btnInscription','radSexe'];
$optionalKeys = ['cbSpam'];

if(!fp_check_param('post',$mandatoryKeys,$optionalKeys) || strlen($_POST['pseudo']) == 0 || strlen($_POST['nom']) == 0 || strlen($_POST['prenom']) == 0 || strlen($_POST['email']) == 0 || strlen($_POST['passe1']) == 0 || strlen($_POST['passe2']) == 0){ 
    fpl_goIndex();
}

// Si les champs de dates ne sont pas des entiers ou sont invalides -> piratage
if(!fpl_intIsBetween($_POST['naissance_j'],1,31) || !fpl_intIsBetween($_POST['naissance_m'],1,12) || !fpl_intIsBetween($_POST['naissance_a'],1900,2020)){
    fpl_goIndex();
}

// Si la valeur de la civilité est différente de 'h' et 'f' --> piratage
if(!preg_match('/^[hf]$/',$_POST['radSexe'])){
    fpl_goIndex();
}

$_POST = array_map('trim',$_POST);
$errors = array();

// Pseudo
if(!preg_match("/^[0-9a-z]{4,20}$/",$_POST['pseudo'])){
    $errors[] = 'Le pseudo doit contenir entre 4 et 20 chiffres ou lettres minuscule';
}

// Email
if(!preg_match("/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$/",$_POST['email'])){
    $errors[] = "L'adresse email n'est pas valide";
}

// Nom - Prénom
foreach(['nom','prénom'] as $value){
    $tmp = $_POST[str_replace('é','e',$value)];
    if(strlen($tmp)==0){
        $errors[] = "Le $value ne doit pas être vide";
    }else if($tmp != strip_tags($tmp)){
        $errors[] = "Le $value ne doit pas contenir de tags HTML";
    }
}

// Passe1 et Passe2
if(strlen($_POST['passe1']) == 0){
    $errors[] = 'Le mot de passe ne doit pas être vide';
}else if($_POST['passe1'] != $_POST['passe2'] ){
    $errors[] = 'Les mots de passes doivent être identiques';
}






// Affichage des erreurs
if(count($errors)!= 0){
    fpl_displayErrors($errors);
    exit(0);
}




// Enregistrement du nouvel utilisateur dans la base de données

$db = fp_db_connecter();

$userData = fp_db_protect_entries($db,$_POST);

extract($userData);

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
    mysqli_close($db);
    foreach($res as $value){
        if($value['type'] == 1){
            $errors[] = 'Le pseudo est déjà utilisé';
        }else{
            $errors[] = 'L\'adresse mail est déjà utilisée';
        }
    }
    fpl_displayErrors($errors);
    exit(1);
}

// Enregistrement de l'utilisateur

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

