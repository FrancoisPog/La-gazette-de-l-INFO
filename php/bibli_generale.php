<?php 

require_once('bibli_gazette.php');

/*################################################################
 *
 *              Generic function librarie          
 * 
 *      > My libraries functions are prefixed by fp_
 *      > My local functions are prefixed by fpl_
 * 
 *      > I organize my functions in several domains :
 *         _print_  -> functions that display html code
 *         _html_   -> processing functions returning html code
 *         _db_     -> interactions functions with database
 *         _str_    -> string processing functions
 *
 ###############################################################*/


// BD

/** 
 *  Ouverture de la connexion à la base de données
 *  En cas d'erreur de connexion le script est arrêté.
 *
 *  @return objet 	connecteur à la base de données
 */
function fp_db_connecter() {
    $conn = mysqli_connect(BD_SERVER, BD_USER, BD_PASS, BD_NAME);
    if ($conn !== FALSE) {
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8') 
        or fp_db_error_exit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
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
    fp_db_error_exit($msg);
}

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
function fp_db_error_exit($msg) {
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

/**
 * Gestion d'une erreur de requête à la base de données.
 *
 * A appeler impérativement quand un appel de mysqli_query() échoue 
 * Appelle la fonction xx_db_erreurExit() qui affiche un message d'erreur puis termine le script
 *
 * @param objet		$bd		Connecteur sur la bd ouverte
 * @param string	$sql	requête SQL provoquant l'erreur
 */
function fp_db_error($bd, $sql) {
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

    fp_db_error_exit($msg);	// ==> ARRET DU SCRIPT
}

/**
 * Execution of database query. 
 * @param Object $db            The databasse connecter 
 * @param String $query         The query 
 * @param bool $protect_outputs If true, the outputs results will be protected (for 'select' query)
 * @param bool $insert          If true, is an insertion query
 * @return Array                The result in an array
 */
function fp_db_execute($db,$query,$protect_outputs = true,$insert = false){
    $query = mysqli_query($db,$query) or fp_db_error($db,$query);
    
    if($insert){
        return $query;
    }

    $array = null;
    while($data = mysqli_fetch_assoc($query)){
        $array[] = ($protect_outputs) ? fp_db_protect_outputs($data):$data;
    }

    mysqli_free_result($query);
    return $array;
}

/**
 * Protection of database outputs. (htmlentities())
 * @param mixed $content    The array or string to protect
 * @return mixed            The array or string protected
 */
function fp_db_protect_outputs($content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = fp_db_protect_outputs($value);   
        }
        unset ($value);
        return $content;
    }
    if (is_string($content)){
        $protected_content = htmlentities($content);
        return $protected_content;
    }
    return $content;
}

/**
 * Protection of database inputs.
 * @param Object $db        The database connecter
 * @param mixed $content    The array or string to protect
 * @return mixed            The array or string protected
 */
function fp_db_protect_inputs($db,$content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = fp_db_protect_inputs($db,$value);   
        }
        unset ($value);
        return $content;
    }
    if (is_string($content)){
        $protected_content = mysqli_real_escape_string($db,$content);
        return $protected_content;
    }
    return $content;
}

// HTML

/**
 * Parsing bbcode text to html code.
 * @param String $arg   The bbcode text to parse
 * @return String       The html text
 */
function fp_html_parseBbCode($arg){
    $exp = array();
    $exp[] = '/\[(p|citation|gras|it|item|liste)\](.+?)\[\/\1\]/i';
    $exp[] = '/\[(br|youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+( [^\]]*)?)\]/i';
    $exp[] = '/\[(a):([^\]]*)\][^\[]+\[\/\1\]/i';
    $exp[] = '/\[#x?[0-9a-fA-F]+\]/';
    
    if(is_string($arg)){
        return preg_replace_callback($exp,'fp_html_parseBbCode',$arg);
    }
    
    // youtube with legende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+ [^\]]+\]$/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+) ([^\]]*)$/i','<figure><iframe width="\1" height="\2" src="\3" allowfullscreen></iframe><figcaption>\6</figcaption></figure>',$arg[1]);
        
    }

    // youtube without légende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+\]/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+)/i','<iframe width="\1" height="\2" src="\3" allowfullscreen></iframe>',$arg[1]);
        
    }

    // Link
    if(preg_match('/^\[a:([^\]])*\][^\[]+\[\/a\]$/i',$arg[0])){
        return preg_replace_callback($exp,'fp_html_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a href="\1">\2</a>',$arg[0]));
       
    }

    // Unicode
    if(preg_match('/^\[#x?[0-9a-fA-F]+\]$/',$arg[0])){
        return preg_replace('/\[#(x)?([0-9a-fA-F]+)\]/','&#\1\2;',$arg[0]);
    }

    
    // Basic tags
    switch ($arg[1]) {
        case "br":
            return "<br>";
        case 'p' :
            return preg_replace_callback($exp,'fp_html_parseBbCode','<p>'.$arg[2].'</p>');
        case 'citation' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<blockquote>'.$arg[2].'</blockquote>');
        case 'liste' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<ul>'.$arg[2].'</ul>');
        case 'item' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<li>'.$arg[2].'</li>');
        case 'it' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<em>'.$arg[2].'</em>');
        case 'gras' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<strong>'.$arg[2].'</strong>');
    }

    echo 'error';
    
}


// FORMS


/**
 * Print a number list in select form field
 * @param String $name  The field's name
 * @param int $min      The minimum value (include)
 * @param int $max      The maximum value (include) 
 * @param int $step     The list iteration step
 * @param int $default  The value selected by defalult
 */
function fp_print_numbersList($name,$min,$max,$step,$default){  
    if($min > $max ){
        throw new Exception('[fp_print_numbersList] : The min value can\'t be greater than the max.');
    }
    if($step == 0){
        throw new Exception('[fp_print_numbersList] : The step value can\'t be 0.');
    }
    echo '<select name="',$name,'">';

    $i = ($step > 0)?$min:$max;
    while(($step > 0) ? ($i <= $max) : ($i >= $min)){
        echo '<option value="',$i,'" ',($default == $i)?'selected':'','>',$i,'</option>';
        $i = $i + $step;
    }
    
    echo '</select>';
}

/**
 * Print a list in select form field
 * @param String name       The field's name
 * @param Array $values     The value list in the 'label'=>'value' format
 * @param String $default   The value selected by defalult
 */
function fp_print_list($name,$values,$default){
    echo '<select name="',$name,'">';
    foreach($values as $key => $value){
        echo '<option value="',$value,'" ',($default == $value)?'selected':'','>',$key,'</option>';
    }
    echo '</select>';
}

/**
 * Print a month list in select form field
 * @param String $name        The field's name
 * @param String $default     The month selected by defalult
 */
function fp_print_monthsList($name,$default){
    fp_print_list($name,['Janvier' => 1, 'Février' => 2,'Mars' => 3, 'Avril' => 4,'Mai' => 5, 'Juin' => 6,'Juillet' => 7, 'Août' => 8,'Septembre' => 9, 'Octobre' => 10,'Novembre' => 11, 'Décembre' => 12,],$default);
}

/**
 * Print a date list in a select form field
 * @param String $name         The field's name
 * @param int $minYear         The minimum year (include)
 * @param int $maxYear         The maximum year (include), if 0, it's the current years
 * @param String $defaultDay   The day selected by default, if 0, it's the current day
 * @param String $defaultMonth The month selected by default, if 0, it's the current month
 * @param String $defaultYear  The year selected by default, if 0, it's the current year
 * @param int $yearsStep       The iteration step for years value
 */
function fp_print_datesList($name,$minYear,$maxYear,$defaultDay = 0, $defaultMonth = 0,$defaultYear = 0,$yearsStep = 1){
    $today=  explode('-',date('d-m-Y'));

    fp_print_numbersList($name.'_j',1,31,1,($defaultDay==0)?$today[0]:$defaultDay);
    fp_print_monthsList($name.'_m',($defaultMonth==0)?$today[1]:$defaultMonth);
    fp_print_numbersList($name.'_a',$minYear,($maxYear == 0)?$today[2]:$maxYear,$yearsStep,($defaultYear==0)?$today[2]:$defaultYear);
}

/**
 * Print a form array line for date choice
 * @param String $label        The line label 
 * @param String $name         The field's name
 * @param int $minYear         The minimum year (include)
 * @param int $maxYear         The maximum year (include), if 0, it's the current years
 * @param String $defaultDay   The day selected by default, if 0, it's the current day
 * @param String $defaultMonth The month selected by default, if 0, it's the current month
 * @param String $defaultYear  The year selected by default, if 0, it's the current year
 * @param int $yearsStep       The iteration step for years value
 * @param String $tooltip      The (optional) information displayed in a tooltip
 * @param bool $tooltipInForm  True if there is at least one tooltip in the form, else false
 */
function fp_print_DatesLine($label,$name,$minYear,$maxYear,$defaultDay = 0, $defaultMonth = 0,$defaultYear = 0,$yearsStep = 1,$tooltip = false,$tooltipInForm=false){
    echo '<tr>',
            '<td class="label"><label>',$label,'</label></td>',
            '<td class="input" ',(!$tooltip && $tooltipInForm)?'colspan="2"':'','>',
                fp_print_datesList($name,$minYear,$maxYear,$defaultDay,$defaultMonth,$defaultYear,$yearsStep),
            '</td>',
            ($tooltip)?'<td><span class="info">&#9432;<span class="infobulle">'.$tooltip.'</span></span></td>':'',
        '</tr>';


}

/**
 * Print a form array input line 
 * @param String $label         The line label 
 * @param String $type          The input type (must be 'text','password' or 'email')
 * @param String $name          The field's name
 * @param int $maxLength        The (optional) maximum length, false if not
 * @param bool $required        True is the input field must be required, true by default
 * @param String $placeholder   The (optional) placeholder, false if not
 * @param String $value         The (optional) default value, false if not
 * @param String $tooltip       The (optional) information displayed in a tooltip
 * @param bool $tooltipInForm   True if there is at least one tooltip in the form, else false
 */
function fp_print_inputLine($label,$type,$name,$maxLength = false,$required =true,$placeholder = false,$value = false,$tooltip=false,$tooltipInForm = false){
    if($type != 'text' && $type != 'password' && $type != 'email'){
        throw new Exception('[fp_print_inputLine] : The input type must be "text", "password" or "email".');
    }
    echo '<tr>',
            '<td class="label"><label for="',$name,'">',$label,'</label></td>',
            '<td class="input" ',(!$tooltip && $tooltipInForm)?'colspan="2"':'','><input id="',$name,'" type="',$type,'" name="',$name,'" ',($required)?'required':'',' ',($placeholder)?('placeholder="'.$placeholder.'"'):(''),' value="',($value)?$value:'','" ',($maxLength)?('maxlength="'.$maxLength.'"'):'','></td>',
            ($tooltip)?'<td><span class="info">&#9432;<span class="infobulle">'.$tooltip.'</span></span></td>':'',
        '</tr>';
}

/**
 * Print a group of radio buttons
 * @param String $name          The field's name
 * @param Array $values         The value list in the 'label'=>'value' format
 * @param bool $required        True is the radio field must be required, true by default
 * @param String $default       The (optional) default value selected, false if not
 */
function fp_print_inputRadio($name,$values,$required = true,$default = false){
    foreach($values as $label => $value){
        echo    '<label for="',$value,'"><input type="radio" name="',$name,'" id="',$value,'" value="',$value,'" ',($required)?'required':'',' ',($value == $default)?'checked':'','>',$label,'</label>';        
    }
}

/**
 * Print a form array radio buttons group line
 * @param String $label         The line label
 * @param String $name          The field's name
 * @param Array $values         The value list in the 'label'=>'value' format
 * @param bool $required        True is the radio field must be required, true by default
 * @param String $default       The (optional) default value selected, false if not 
 * @param String $tooltip       The (optional) information displayed in a tooltip
 * @param bool $tooltipInForm   True if there is at least one tooltip in the form, else false
 */
function fp_print_inputRadioLine($label,$name,$values,$required = true,$default = false,$tooltip = false,$tooltipInForm = false){
    echo    '<tr>',
                '<td class="label"><label>',$label,'</label></td>',
                '<td class="input" ',(!$tooltip && $tooltipInForm)?'colspan="2"':'','>',
                    fp_print_inputRadio($name,$values,$required,$default),
                '</td>',
                ($tooltip)?'<td><span class="info">&#9432;<span class="infobulle">'.$tooltip.'</span></span></td>':'',
            '</tr>';
}

/**
 * Print a checkbox input
 * @param String $name          The field's name
 * @param String $label         The checkbox label
 * @param bool $required        True is the radio field must be required, true by default
 * @param bool $checked         True if the box is checked, false by defauly
 */
function fp_print_inputCheckbox($name,$label,$required = true,$checked = false){
    echo '<input type="checkbox" name="',$name,'" id="',$name,'" ',($required)?'required':'',' ',($checked)?'checked':'','>',
            '<label for="',$name,'">',$label,'</label>';
}

/**
 * Print a form array line for a chackbox
 * @param String $name          The field's name
 * @param String $label         The checkbox label
 * @param bool $required        True is the radio field must be required, true by default
 * @param bool $checked         True if the box is checked, false by defauly
 * @param String $tooltip       The (optional) information displayed in a tooltip
 * @param bool $tooltipInForm   True if there is at least one tooltip in the form, else false
 */
function fp_print_checkboxLine($name,$label,$required = true, $checked = false,$tooltip = false, $tooltipInForm = false){
    echo '<tr>',
            '<td class="checkbox" colspan="',(!$tooltip && $tooltipInForm)?'3':'2','">',
                fp_print_inputCheckbox($name,$label,$required,$checked),
            '</td>',
            ($tooltip)?'<td><span class="info">&#9432;<span class="infobulle">'.$tooltip.'</span></span></td>':'',
        '</tr>';
}

/**
 * Print a form array line with submit and (optional) reset buttons
 * @param Array $submit         Value and name of submit button (0:value, 1:name)
 * @param String $resetValue    Value of reset button
 * @param bool $tooltipInForm   True if there is at least one tooltip in the form, else false
 */ 
function fp_print_buttonsLine($submit,$resetValue = false,$tooltipInForm = false){
    if(!is_array($submit)){
        throw new Exception('[fp_print_buttonsLine] : $submit must be an array ');
    }
    echo '<tr>',
            '<td class="buttons" colspan="',($tooltipInForm)?'3':'2','">',
                '<input type="submit" value="',$submit[0],'" name="',$submit[1],'">',
                ($resetValue)?'<input type="reset" value="'.$resetValue.'">':'',
             '</td>',
        '</tr>';
}

// STR

/**
 * Check if a string is an int
 * @param String $str The string to test
 * @return boolean    True if the string is an int, else false
 */
function fp_str_isInt($str){
    return preg_match('/^[[:digit:]]+$/',$str);
}

/**
 * Parsing date from database to string 
 * @param int $date The date to parse
 * @return String   The date in correct format
 */
function fp_str_toDate($date){
    setlocale(LC_TIME, "fr_FR");
    return utf8_encode(strftime("%e %B %G &agrave; %Hh%M",strtotime($date)));
}

// OTHER

/**
 * Checking parameters validity
 * @param Array array               The array containing the parameters
 * @param Array $mandatory_keys     The array containing the mandatory keys
 * @param Array $optional_keys      The array containing the optional keys
 * @return boolean                  True if the parameters are correct, else false
 */
function fp_check_param($array, $mandatory_keys, $optional_keys = array()){
    $array = array_keys($array);
    if (count(array_diff($mandatory_keys, $array)) > 0){
        return false;
    }
    if (count(array_diff($array, array_merge($mandatory_keys,$optional_keys))) > 0){
        return false;
    }
    
    return true;
}


/**
 * Correctly end a session and redirect to the given page.
 * @param String $page  The page for the redirection
 */
function fp_session_exit($page){
    session_destroy();
    session_unset();

    // deleting session cookie
    $cookie_session_data = session_get_cookie_params();
    setcookie(session_name(), 
                '', 
                time() - 86400,
                $cookie_session_data['path'], 
                $cookie_session_data['domain'],
                $cookie_session_data['secure'],
                $cookie_session_data['httponly']
            );
        
    header("Location: $page");
    exit(0);

}

/**
 * Check if the user is logged in.
 * @param mixed $page_to_go_if_not  If you want to redirect the user if he's not logged in, you must specify a page
 * @return boolean True if he's logged in, else false (only if he's not redirected)
 */
function fp_is_logged($page_to_go_if_not = false){
    $isLogged = (isset($_SESSION['pseudo']) && isset($_SESSION['statut']));
    if($isLogged){
        return true;
    }
    
    if(!$page_to_go_if_not){
        return false;
    }
    
    fp_session_exit($page_to_go_if_not);

}