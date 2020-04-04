<?php

session_start();
ob_start();
require_once("bibli_generale.php");

// if the user came in this page without be logged -> go index.php
fp_is_logged('../index.php');

$pseudo = $_SESSION['pseudo'];

$db = fp_db_connecter();

// fetching user data
$query = 'SELECT * FROM utilisateur WHERE utPseudo = "'.mysqli_real_escape_string($db,$pseudo).'"';
$userData = fp_db_execute($db,$query)[0];
// N.B. $userData is protected in fp_db_execute()

// --- Page Generation

fp_print_beginPage('protegee','Page accessible uniquement aux utilisateurs authentifiés',1,$userData['utStatut'],$userData['utPseudo']);

echo '<section>',
        '<h2>Utilisateur : ',$userData['utPseudo'],'</h2>',
        '<p>SID : ',session_id(),'</p>',
        '<h3>Données mémorisées dans le table utilisateur</h3>',
        '<ul>';
        foreach($userData as $key => $value){
            echo '<li>',$key,' : ',$value,'</li>';
        }
echo    '</ul>', 
    '</section>';


fp_print_endPage();