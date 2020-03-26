<?php
require_once("bibli_generale.php");

// Affichage de $_POST avec foreach
foreach($_POST as $key => $value){
    echo $key.' : '.$value.'<br/>';
}

// Affichage de $_POST avec var_dump()
var_dump($_POST);

// Affichage de $_POST avec print_r
echo '<pre>',print_r($_POST),'</pre>';


/* Vérification des arguments
    Seule la clé de la checkbox concernant les spams peut être absente, en effet toutes les autres
    sont obligatoire à l'inscription et l'attribut 'required' est positionné.
    Donc si elles sont absentes, --> piratage .
*/

$mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2', 'cbCGU','btnInscription','radSexe'];
$optionalKeys = ['cbSpam'];

if(!fp_check_param($_POST,$mandatoryKeys,$optionalKeys)){
    echo "Erreur parmis les clés";
}

