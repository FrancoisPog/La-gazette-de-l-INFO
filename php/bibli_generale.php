<?php 

require_once("bibli_gazette.php");

// FONCTIONS BASE DE DONNEES 

//____________________________________________________________________________
/** 
 *  Ouverture de la connexion à la base de données
 *  En cas d'erreur de connexion le script est arrêté.
 *
 *  @return objet 	connecteur à la base de données
 */
function fp_bd_connecter() {
    $conn = mysqli_connect(BD_SERVER, BD_USER, BD_PASS, BD_NAME);
    if ($conn !== FALSE) {
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8') 
        or fp_bd_erreur_exit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
        return $conn;     // ===> Sortie connexion OK
    }
    // Erreur de connexion
    // Collecte des informations facilitant le debugage
    $msg = '<h4>Erreur de connexion base MySQL</h4>'
            .'<div style="margin: 20px auto; width: 350px;">'
            .'BD_SERVER : '. BD_SERVER
            .'<br>BD_USER : '. BD_USER
            .'<br>BD_PASS : '. BD_PASS
            .'<br>BD_NAME : '. BD_NAME
            .'<p>Erreur MySQL numéro : '.mysqli_connect_errno()
            //appel de htmlentities() pour que les éventuels accents s'affiche correctement
            .'<br>'.htmlentities(mysqli_connect_error(), ENT_QUOTES, 'ISO-8859-1')  
            .'</div>';
    fp_bd_erreur_exit($msg);
}

//____________________________________________________________________________
/**
 * Arrêt du script si erreur base de données 
 *
 * Affichage d'un message d'erreur, puis arrêt du script
 * Fonction appelée quand une erreur 'base de données' se produit :
 * 		- lors de la phase de connexion au serveur MySQL
 *		- ou indirectement lorsque l'envoi d'une requête échoue
 *
 * @param string	$msg	Message d'erreur à afficher
 */
function fp_bd_erreur_exit($msg) {
    ob_end_clean();	// Suppression de tout ce qui a pu être déja généré

    echo    '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">',
            '<title>Erreur base de données</title>',
            '<style>',
                'table{border-collapse: collapse;}td{border: 1px solid black;padding: 4px 10px;}',
            '</style>',
            '</head><body>',
            $msg,
            '</body></html>';
    exit(1);		// ==> ARRET DU SCRIPT
}

//____________________________________________________________________________
/**
 * Gestion d'une erreur de requête à la base de données.
 *
 * A appeler impérativement quand un appel de mysqli_query() échoue 
 * Appelle la fonction xx_bd_erreurExit() qui affiche un message d'erreur puis termine le script
 *
 * @param objet		$bd		Connecteur sur la bd ouverte
 * @param string	$sql	requête SQL provoquant l'erreur
 */
function fp_bd_erreur($bd, $sql) {
    $errNum = mysqli_errno($bd);
    $errTxt = mysqli_error($bd);

    // Collecte des informations facilitant le debugage
    $msg =  '<h4>Erreur de requête</h4>'
            ."<pre><b>Erreur mysql :</b> $errNum"
            ."<br> $errTxt"
            ."<br><br><b>Requête :</b><br> $sql"
            .'<br><br><b>Pile des appels de fonction</b></pre>';

    // Récupération de la pile des appels de fonction
    $msg .= '<table>'
            .'<tr><td>Fonction</td><td>Appelée ligne</td>'
            .'<td>Fichier</td></tr>';

    $appels = debug_backtrace();
    for ($i = 0, $iMax = count($appels); $i < $iMax; $i++) {
        $msg .= '<tr style="text-align: center;"><td>'
                .$appels[$i]['function'].'</td><td>'
                .$appels[$i]['line'].'</td><td>'
                .$appels[$i]['file'].'</td></tr>';
    }

    $msg .= '</table>';

    fp_bd_erreur_exit($msg);	// ==> ARRET DU SCRIPT
}

/**
 * Execute une requete 'SELECT' et retourne le résultat dans un tableau de tableau associatif
 * @param Object $db : L'identifiant de la base de données
 * @param String $query : La requête à executer
 * @return Array Un tableau contenant les résultats, null si aucun résultat
 */
function fp_queryToArray($db,$query){
    $query = mysqli_query($db,$query) or fp_bd_erreur($db,$query);
    $array = null;
    while($data = mysqli_fetch_assoc($query)){
        $array[] = $data;
    }
    mysqli_free_result($query);
    return $array;
}

// FONCTION DE GENERATION CODE HTML

/**
 * Affiche le head d'un site
 * @param String $title : Le titre de la page
 * @param String $stylesheet : Le lien vers la feuille de style
 */
function fp_make_head($title,$stylesheet){
    echo    '<head>',
                '<meta charset="utf-8">',
                '<title>',$title,'</title>',
                '<link rel="stylesheet" href="',$stylesheet,'">',
            '</head>';
}

/**
 * Affiche la balise ouvrante d'un tag donné
 * @param String $tagName : Le nom de la balise
 * @param Array $attributs : La liste des attributs et leur valeurs de la balise
 */
function fp_begin_tag($tagName,$attributs=[]){
    echo '<',$tagName,' ';
    foreach($attributs as $key => $value){
        echo $key,'="'.$value.'" ';
    }
    echo '>';
}

/**
 * Affiche la balise fermante d'un tag donné
 * @param String $tagName Le nom du tag 
 */
function fp_end_tag($tagName){
    echo '</',$tagName,'>';
}

// AUTRES FONCTIONS

function fp_str_isInt($str){
    return preg_match('/^[[:digit:]]+$/',$str);
}