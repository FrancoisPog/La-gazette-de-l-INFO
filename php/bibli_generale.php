<?php 

require_once("bibli_gazette.php");

// **************************************************************************
//                      FONCTIONS BASE DE DONNEES 
// **************************************************************************
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
 * @param Object $db L'identifiant de la base de données
 * @param String $query La requête à executer
 * @return Array Un tableau contenant les résultats, null si aucun résultat
 */
function fp_queryToArray($db,$query){
    $query = mysqli_query($db,$query) or fp_bd_erreur($db,$query);
    $array = null;
    while($data = mysqli_fetch_assoc($query)){
        $array[] = fp_protect_exits($data);
    }
    mysqli_free_result($query);
    return $array;
}








// **************************************************************************
//                  FONCTION DE GENERATION CODE HTML
// **************************************************************************
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
 * @param AssociativeArray $attributs : La liste des attributs et leur valeurs de la balise
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








// **************************************************************************
//                               AUTRES FONCTIONS
// **************************************************************************
/**
 * Check is a string is an int
 * @param String $str The string to test
 * @return True if the string is an int, else false
 */
function fp_str_isInt($str){
    return preg_match('/^[[:digit:]]+$/',$str);
}

/**
 * Parse une date du format AAAAMMJJHHmm au format "14 mai 2000 à 19h15"
 * @param int $date La date à parser
 * @return String 
 */
function fp_date_format($date){
    setlocale(LC_TIME, "fr_FR");
    return utf8_encode(strftime("%e %B %G &agrave; %Hh%M",strtotime($date)));
}


/**
 * Fonction d'analyse syntaxique d'un texte BBCode pour le parser en html (IN PROGRESS)
 * @param String $arg Le texte en BBCode.
 * N.B. Cette fonction est récursive et lors d'un appel interne, $arg est un tableau de String.
 * @return String Le texte en html
 */
function fp_parseBbCode($arg){
    $exp = array();
    $exp[] = '/\[(p|citation|gras|it|item|liste)\](.+?)\[\/\1\]/i';
    $exp[] = '/\[(br|youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+( [^\]]*)?)\]/i';
    $exp[] = '/\[(a):([^\]]*)\][^\[]+\[\/\1\]/i';
    $exp[] = '/\[#x?[0-9a-fA-F]+\]/';
    
    if(is_string($arg)){
        return preg_replace_callback($exp,'fp_parseBbCode',$arg);
    }
    
    // youtube avec legende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+ [^\]]+\]$/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+) ([^\]]*)$/i','<figure><iframe width="\1" height="\2" src="\3" allowfullscreen></iframe><figcaption>\6</figcaption></figure>',$arg[1]);
        
    }

    // youtube sans légende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+\]/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+)/i','<iframe width="\1" height="\2" src="\3" allowfullscreen></iframe>',$arg[1]);
        
    }

    // Lien
    if(preg_match('/^\[a:([^\]])*\][^\[]+\[\/a\]$/i',$arg[0])){
        return preg_replace_callback($exp,'fp_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a href="\1">\2</a>',$arg[0]));
       
    }

    // Code unicode 
    if(preg_match('/^\[#x?[0-9a-fA-F]+\]$/',$arg[0])){
        return preg_replace('/\[#(x)?([0-9a-fA-F]+)\]/','&#\1\2;',$arg[0]);
    }

    
    // Balises simples
    switch ($arg[1]) {
        case "br":
            return "<br>";
        case 'p' :
            return preg_replace_callback($exp,'fp_parseBbCode','<p>'.$arg[2].'</p>');
        case 'citation' : 
            return preg_replace_callback($exp,'fp_parseBbCode','<blockquote>'.$arg[2].'</blockquote>');
        case 'liste' : 
            return preg_replace_callback($exp,'fp_parseBbCode','<ul>'.$arg[2].'</ul>');
        case 'item' : 
            return preg_replace_callback($exp,'fp_parseBbCode','<li>'.$arg[2].'</li>');
        case 'it' : 
            return preg_replace_callback($exp,'fp_parseBbCode','<em>'.$arg[2].'</em>');
        case 'gras' : 
            return preg_replace_callback($exp,'fp_parseBbCode','<strong>'.$arg[2].'</strong>');
    }

    echo 'erreur';
    
}

function fp_protect_exits($content, $i=0) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = fp_protect_exits($value, ++$i);   
        }
        unset ($value); // à ne pas oublier (de façon générale)
        return $content;
    }
    if (is_string($content)){
        $protected_content = htmlspecialchars($content);
        return $protected_content;
    }
    return $content;
}

function fp_check_param($tab_global, $cles_obligatoires, $cles_facultatives = array()){
    $x = strtolower($tab_global) == 'post' ? $_POST : $_GET;

    $x = array_keys($x);
    // $cles_obligatoires doit être inclus dans $x
    if (count(array_diff($cles_obligatoires, $x)) > 0){
        return false;
    }
    // $x doit être inclus dans $cles_obligatoires Union $cles_facultatives
    if (count(array_diff($x, array_merge($cles_obligatoires,$cles_facultatives))) > 0){
        return false;
    }
    
    return true;
}