<?php

session_start();
ob_start();
require_once("bibli_gazette.php");

// if the user came in this page without be logged -> go index.php
cp_is_logged('../index.php');

$pseudo = $_SESSION['pseudo'];

$db = cp_db_connecter();

// fetching user data
$query = 'SELECT * FROM utilisateur WHERE utPseudo = "'.mysqli_real_escape_string($db,$pseudo).'"';
$userData = cp_db_execute($db,$query)[0];
// N.B. $userData is protected in cp_db_execute()

mysqli_close($db);


// --- Page Generation

cp_print_beginPage('protegee','Page accessible uniquement aux utilisateurs authentifiés',1,true);

echo '<section>',
        '<h2>Utilisateur : ',$userData['utPseudo'],'</h2>',
        '<p>SID : ',session_id(),'</p>',
        '<h3>Données mémorisées dans la table utilisateur</h3>',
        '<ul>';
        foreach($userData as $key => $value){
            echo '<li>',$key,' : ',$value,'</li>';
        }
echo    '</ul>', 
    '</section>';

cp_print_endPage();