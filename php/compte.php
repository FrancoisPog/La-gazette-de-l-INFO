<?php
ob_start();
session_start();
require_once('bibli_gazette.php');

define('EDIT_PERSONAL_DATA',1);
define('EDIT_PASS',2);
define('EDIT_EDITOR_DATA',3);
define('EDIT_PICTURE',4);


// --- local functions ---

/**
 * Fetch the connected user data in database
 * @return Array $userData
 */
function cpl_fetch_userData(){

    $db = cp_db_connecter();

    $query = '('.'SELECT utPseudo AS pseudo, utNom AS last_name, utPrenom AS first_name, utEmail AS email, utDateNaissance AS birthday, utCivilite AS civility, utMailsPourris AS spam, reBio AS bio, reCategorie AS category, reFonction AS function, 1 AS type 
                FROM utilisateur LEFT OUTER JOIN redacteur ON utPseudo = rePseudo
                WHERE utPseudo="'. cp_db_protect_inputs($db,$_SESSION['pseudo']).'" ) UNION (SELECT 0,0,0, utEmail,0,0,0,0,0,0, 2 AS type FROM utilisateur WHERE utPseudo <> "'. cp_db_protect_inputs($db,$_SESSION['pseudo']).'" ) ORDER BY type';

    $userData = cp_db_execute($db,$query,false);

    if(isset($_POST['btnEditData'])){
        // We keep all others user's email for check later if the user want to change
        $GLOBALS['emails'] = array_slice($userData,1);
    }else if(isset($_POST['btnEditBio']) && $userData[0]['bio'] == '' ){
        $GLOBALS['emptyBio'] = true;
    }

    $userData = $userData[0];
    $userData['bio'] = str_replace(["\r","\n"],"",$userData['bio']);
    $userData['birthday_d'] = substr($userData['birthday'],-2,2);
    $userData['birthday_m'] = substr($userData['birthday'],-4,2);
    $userData['birthday_y'] = substr($userData['birthday'],0,4);

    mysqli_close($db);

    return $userData;

}


// EDIT DATA


/**
 * Check if it's a hacking case
 * @param int $processType  The type of process
 * @return void|exit Exit the script if it's a hacking case
 */
function cpl_hackGuard($processType){
    switch($processType){
        
        case EDIT_PERSONAL_DATA : {
            /* Only the spam checkbox can be missing. So, if other keys are missing -> hacking.*/
            $mandatoryKeys = ['btnEditData','civility','last_name','first_name','birthday_y','birthday_m','birthday_d','email'];
            $optionalKeys = ['spam'];

            cp_check_param($_POST,$mandatoryKeys,$optionalKeys) or cp_session_exit('../index.php');

            // If the date fields values are invalid -> hacking
            cp_isValid_date($_POST['birthday_d'],$_POST['birthday_m'],$_POST['birthday_y']) or cp_session_exit('../index.php');

            // If the value of civility is different of 'h' and 'f' -> hacking
            cp_isValid_civility($_POST['civility']) or cp_session_exit('../index.php');
            return;
        }

       
        case EDIT_PASS : {
            $mandatoryKeys = ['pass2','pass1','btnEditPass'];
            cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
            return;
        }

        
        case EDIT_EDITOR_DATA : {
            $mandatoryKeys = ['btnEditBio','category','function','bio'];
            cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
            cp_intIsBetween($_POST['category'],1,3) or cp_session_exit('../index.php');
            return ;
        }
        case EDIT_PICTURE : {
            $mandatoryKeys = ['btnEditPicture'];
            cp_check_param($_POST,$mandatoryKeys) or cp_session_exit('../index.php');
            $mandatoryKeys = ['picture'];
            cp_check_param($_FILES,$mandatoryKeys) or cp_session_exit('../index.php');
            return;
        }
    }
}

/**
 * Check if the user made mistakes while editing his data
 * @param int $processType  The type of process
 * @return Array The errors list
 */
function cpl_checkMistakes($processType){
    $_POST = array_map('trim',$_POST);
    $errors = array();

    switch($processType){
        case EDIT_PERSONAL_DATA : {
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


            foreach($GLOBALS['emails'] as $email){
                if($email['email'] == $_POST['email'] ){
                    $errors[] = 'L\'adresse mail est déjà utilisée';
                    break;
                }
            }
            unset($GLOBALS['emails']);
        break;
        }
        case EDIT_PASS : {
            // Passe1 et Passe2
            $passValid = cp_isValid_pass($_POST['pass1'],$_POST['pass2']);
            if($passValid == 1 ){
                $errors[] = 'Le mot de passe ne doit pas être vide';
            }else if($passValid == 2 ){
                $errors[] = 'Les mots de passes doivent être identiques';
            }else if($passValid == 3){
                $errors[] = 'Le mot de passe doit contenir moins de 256 caractères';
            }
        break;
        }
        case EDIT_EDITOR_DATA : {
            if($_POST['function'] != strip_tags($_POST['function'])){
                $errors[] = 'La fonction ne doit pas contenir de tags html';
            }
        
            if(strlen($_POST['function']) > 100){
                $errors[] = 'La fonction ne doit pas dépasser 100 caractères';
            }
        
            if(strlen($_POST['bio']) == 0){
                $errors[] = 'La biographie ne peut pas être vide';
            }
        
            if($_POST['bio'] != str_replace(['<','>'],'',$_POST['bio'])){
                $errors[] = 'La biographie ne doit pas contenir de tags HTML';
            }
        break;
        }
        case EDIT_PICTURE : {
            
            switch($_FILES['picture']['error']){
                case 1 : 
                case 2 :
                    $errors[] = 'Le fichier est trop volumineux';
                break 2;
                case 3 : 
                    $errors[] = 'Erreur de transfert';
                break 2;
                case 4 : 
                    $errors[] = 'Fichier introuvable';
                break 2;
            }

            if($_FILES['picture']['type'] != 'image/jpeg'){
                $errors[] = 'Le format de la photo doit être "jpeg"';
            }
            if(!is_uploaded_file($_FILES['picture']['tmp_name'])){
                $errors[] = 'Erreur interne';
            }
        break;
        }
        
    }
    return (count($errors) == 0)?0:$errors;

}

/**
 * Update data on database
 * @param int $processType  The type of process
 */
function cpl_updateDatabase($processType){
    $db = cp_db_connecter();
    $protected = array_slice($_POST,0,null,true); // clone $_POST to $protected (important because otherwise, the value displayed after the update will be escaped on the html form) -> *1
    $protected = cp_db_protect_inputs($db,$protected);
    $pseudo = cp_db_protect_inputs($db,$_SESSION['pseudo']);

    switch($processType){
       
        case EDIT_PERSONAL_DATA : {
            extract($protected);
            $birthDate = $birthday_y*10000+$birthday_m*100+$birthday_d;
            $spam = (isset($spam)) ? 1:0;

            $query = "UPDATE utilisateur
                        SET utCivilite = '$civility',
                            utNom = '$last_name',
                            utPrenom = '$first_name',
                            utEmail = '$email',
                            utDateNaissance = '$birthDate',
                            utMailsPourris = '$spam'
                        WHERE  utPseudo = '$pseudo'";
        break;
        }
        
        case EDIT_PASS : {
            $pass = $protected['pass1'];
            $pass =  password_hash($pass,PASSWORD_DEFAULT);
            
            $query = "UPDATE utilisateur
                        SET utPasse = '$pass'
                        WHERE  utPseudo = '$pseudo'";

        break;
        }
        
        case EDIT_EDITOR_DATA : {
            $bio = $protected['bio'];
            $function =$protected['function'];
            $category = $protected['category'];

            if(isset($GLOBALS['emptyBio'])){
                $query = "INSERT INTO redacteur SET rePseudo = '$pseudo', reBio = '$bio', reFonction = '$function', reCategorie = '$category'"; 
            }else{
                $query = "UPDATE redacteur SET reBio = '$bio', reFonction = '$function', reCategorie = '$category' WHERE rePseudo = '$pseudo' ";
            }

        break;
        }
    }
    cp_db_execute($db,$query,false,true);
    mysqli_close($db);
}


function cpl_nothingChange($processType, $userData){

    

    switch($processType){
        case EDIT_PERSONAL_DATA : {
            $keys = ['civility','last_name','first_name','email','birthday_m','birthday_y','birthday_d'];
            if($userData['spam'] != isset($_POST['spam'])){
                return false;
            }
        break;
        }
        case EDIT_EDITOR_DATA : {
            $keys = ['function','bio','category'];
        break;
        }
        default : {
            return false;
        }
    }

    return cpl_arrayIsSame($_POST,$userData,$keys);

}


/**
 * Edit the user's data 
 * @param int $processType  The type of process
 * @return Array|0 The array of errors, 0 if there are no errors
 */
function cpl_editDataProcess($processType,$userData){
    // Avoid hacking case
    cpl_hackGuard($processType);

    // check user mistakes
    if(($errors = cpl_checkMistakes($processType))){
        return $errors;
    }

    // if nothing change, no database connection
    if(cpl_nothingChange($processType,$userData)){
        return 0;
    }

    if($processType < EDIT_PICTURE){
        // Update data on database
        cpl_updateDatabase($processType);
    }else{
        $pseudo = $_SESSION['pseudo'];
        move_uploaded_file($_FILES['picture']['tmp_name'],realpath('..')."/upload/$pseudo.jpg");
    }

    return 0;
}



function cpl_arrayIsSame($array1, $array2,$keys){

    foreach($keys as $key){
        if($array1[$key] != $array2[$key]){
            return false;
        }
    }

    return true;
}



// PRINT

/**
 * Print the errors of registration 
 * @param Array $errors The errors to print
 */
function cpl_print_Errors($errors,$label){
    echo '<div class="error">',
            "<p>$label</p>",
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

    extract($userData);
    

    
    $_POST = cp_db_protect_outputs($_POST); // *1
    extract($_POST,EXTR_OVERWRITE);
    if(isset($_POST['btnEditData'])){
        $spam = isset($_POST['spam']);
    }
    

    cp_print_beginPage('compte',"Mon compte",1,$_SESSION['status'],$_SESSION['pseudo']);
    
    echo '<section id="personal_data">',
            '<h2>Informations personnelles</h2>',
            '<p>Vous pouvez modifier les informations suivantes :</p>',
            ($errors && isset($_POST['btnEditData'])) ? cpl_print_Errors($errors,'Les erreurs suivantes ont été relevées lors de la mise à jours de vos données :') : '',
            (!$errors && isset($_POST['btnEditData'])) ? '<p class="success">Vos informations ont été mise à jour avec succès.</p>':'',
            '<form method="POST" action="compte.php">',
                '<table class="form">',
                    cp_form_print_radiosLine('Votre civilité :','civility',['Monsieur' => 'h','Madame'=> 'f'],$required,$civility),
                    cp_form_print_inputLine('Votre nom :','text','last_name','50',$required,'',$last_name),
                    cp_form_print_inputLine('Votre prénom :','text','first_name','60',$required,'',$first_name),
                    cp_form_print_DatesLine('Votre date de naissance :','birthday',1920,0,$birthday_d,$birthday_m,$birthday_y,1,'Vous devez avoir 18 ans pour être inscrire'),
                    cp_form_print_inputLine('Votre email :','email','email',255,$required,'',$email),
                    cp_form_print_checkboxLine('spam','J\'accepte de recevoir des tonnes de mails pourris',false,$spam,'Vos données personnelles seront bien évidemment utilisées à des fins commerciales'),
                    cp_form_print_buttonsLine(['Enregistrer','btnEditData'],'Réinitialiser'),
                '</table>',
            '</form>',
            '</section>',
            '<section id="pass">',
                '<h2>Authentification</h2>',
                '<p>Vous pouvez modifier votre mot de passe ci-dessous :</p>',
                ($errors && isset($_POST['btnEditPass'])) ? cpl_print_Errors($errors,'Les erreurs suivantes ont été relevées lors de la mise à jours de votre mot de passe :') : '',
                (!$errors && isset($_POST['btnEditPass'])) ? '<p class="success">Votre mot de passe à été mis à jour avec succès.</p>':'',
                '<form method="POST" action="compte.php">',
                    '<table class="form">',
                        cp_form_print_inputLine('Choisissez un mot de passe :','password','pass1',255,$required),
                        cp_form_print_inputLine('Répétez le mot de passe :','password','pass2',255,$required),
                        cp_form_print_buttonsLine(['Enregistrer','btnEditPass']),
                    '</table>',
                '</form>',
            '</section>';

    if(isset($_POST['btnEditPass'])){
        echo '<script>window.location.replace("#pass"); </script>';
    }

    if($_SESSION['status'] == 0 || $_SESSION['status'] == 2){
        cp_print_endPage();
        return ;
    }

    echo '<section id="editor_data">',
            '<h2>Information rédacteur</h2>',
            '<p>Vous pouvez modifier les informations suivantes :</p>',
            ($errors && isset($_POST['btnEditBio'])) ? cpl_print_Errors($errors,'Les erreurs suivantes ont été relevées lors de la mise à jours de vos données :') : '',
            (!$errors && isset($_POST['btnEditBio'])) ? '<p class="success">Vos informations de rédacteur ont été mis à jour avec succès.</p>':'',
            '<form method="POST" action="compte.php">',
                '<table class="form" >',
                    cp_form_print_inputLine('Votre fonction :','text','function',100,false,'',$function),
                    cp_form_print_listLine('Votre catégorie :','category',['Rédacteur en chef' => '1','Premier violon' => '2',  'Sous-fifre' => '3'],$category),
                    cp_form_print_textAreaLine('Votre biographie :','bio',$bio,60,6,true,'La page d\'accueil affiche les 300 premiers caractères du résumé'),
                    cp_form_print_buttonsLine(['Enregistrer','btnEditBio'],'Réinitialiser'),
                '</table>',
            '</form>',

        '</section>',
        '<section id="profil_picture">',
            '<h2>Votre photo de rédacteur</h2>',
            '<p>Vous pouvez modifier votre photo ci-dessous :</p>',
            (file_exists('../upload/'.$_SESSION['pseudo'].'.jpg'))?'<img title="Votre photo de rédacteur actuelle" alt="Photo actuelle" width="150" height="200" src="../upload/'.$_SESSION['pseudo'].'.jpg" >':'',
            ($errors && isset($_POST['btnEditPicture'])) ? cpl_print_Errors($errors,'Les erreurs suivantes ont été relevées lors de la mise à jours de votre photo :') : '',
            (!$errors && isset($_POST['btnEditPicture'])) ? '<p class="success">Votre photo de rédacteur a été mis à jour avec succès.</p>':'',
            
            '<form method="POST" action="compte.php" enctype="multipart/form-data">',
                '<table class="form" >',
                    cp_form_print_file('picture','',true,'Pour ne pas être déformée, la photo doit faire 150x200 pixels.'),
                    cp_form_print_buttonsLine(['Enregistrer','btnEditPicture']),
                '</table>',
            '</form>',

        '</section>';

        if(isset($_POST['btnEditBio'])){
            echo '<script>window.location.replace("#pass"); </script>';
        }else if(isset($_POST['btnEditPicture'])){
            echo '<script>window.location.replace("#profil_picture"); </script>';
        }


    cp_print_endPage();
    
}


// --- Page generation


cp_is_logged('../index.php');

$userData = cpl_fetch_userData();

if(isset($_POST['btnEditData'])){ 
    $errors = cpl_editDataProcess(EDIT_PERSONAL_DATA,$userData);
    cpl_print_page_compte($userData,($errors)?$errors:[]);  

}else if(isset($_POST['btnEditPass'])){
    $errors = cpl_editDataProcess(EDIT_PASS,$userData);
    cpl_print_page_compte($userData,($errors)?$errors:[]);

}else if(isset($_POST['btnEditBio'])){
    $errors = cpl_editDataProcess(EDIT_EDITOR_DATA,$userData);
    cpl_print_page_compte($userData,($errors)?$errors:[]);
}else if(isset($_POST['btnEditPicture'])){
    $errors = cpl_editDataProcess(EDIT_PICTURE,$userData);
    cpl_print_page_compte($userData,$errors);
}else{
    cpl_print_page_compte($userData);   
}

