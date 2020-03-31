<?php
require_once("bibli_generale.php");

// Print $_POST with foreach
foreach($_POST as $key => $value){
    echo $key.' : '.$value.'<br/>';
}

// Print $_POST with var_dump()
var_dump($_POST);

// Print $_POST with print_r
echo '<pre>',print_r($_POST),'</pre>';


/* 
    Only the 'spam check box' key may be missing, 
    indeed all others are required for registration and the "required" attribute is set.
    So, if they are absent, -> hacking.
*/

$mandatoryKeys = ['pseudo','nom','prenom','naissance_j','naissance_m', 'naissance_a','email','passe1','passe2', 'cbCGU','btnInscription','radSexe'];
$optionalKeys = ['cbSpam'];

if(!fp_check_param($_POST,$mandatoryKeys,$optionalKeys)){
    echo "Erreur parmis les clés";
}

