<?php 

/*################################################################
 *
 *              Generic functions librarie          
 * 
 *      > My libraries functions are prefixed by cp_
 *      > My local functions are prefixed by cpl_
 * 
 *      > I organize my functions in several domains :
 *         _print_  -> functions that display html code
 *         _html_   -> processing functions returning html code
 *         _db_     -> interactions functions with database
 *         _str_    -> string processing functions
 *
 ###############################################################*/

// Includes
require_once('bibli_formulaire.php');
require_once('bibli_database.php');

// HTML

/**
 * Parsing bbcode text to html code.
 * @param String $arg   The bbcode text to parse
 * @return String       The html text
 */
function cp_html_parseBbCode($arg,$protect = false){
    $exp = array();
    $exp[] = '/\[(p|citation|gras|it|item|liste)\](.+?)\[\/\1\]/i';
    $exp[] = '/\[(br|youtube:\d+:\d+:https:\/\/www\.youtube\.com\/[^ \]]+( [^\]]*)?)\]/i';
    $exp[] = '/\[(a):([^\]]*)\][^[]+\[\/\1\]/i';
    $exp[] = '/\[#x?[0-9a-fA-F]+\]/';
    
    if(is_string($arg)){
        return preg_replace_callback($exp,'cp_html_parseBbCode',str_replace(array("\r","\n"),'',($protect)?htmlentities($arg):$arg));
    }
    
    // youtube with legende
    if(preg_match('/^\[youtube:\d+:\d+:https:\/\/www\.youtube\.com\/[^ \]]+ [^\]]+\]$/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):(https:\/\/www\.youtube\.com\/[^ \]]+) ([^\]]*)$/i','<figure><iframe width="\1" height="\2" src="\3" allowfullscreen></iframe><figcaption>\4</figcaption></figure>',$arg[1]);
        
    }

    // youtube without légende
    if(preg_match('/^\[youtube:\d+:\d+:https:\/\/www\.youtube\.com\/[^ \]]+\]/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):(https:\/\/www\.youtube\.com\/[^ \]]+)/i','<iframe width="\1" height="\2" src="\3" allowfullscreen></iframe>',$arg[1]);
        
    }

    // Link
    if(preg_match('/^\[a:([^\]])*\][^[]+\[\/a\]$/i',$arg[0])){
       $url_data = parse_url($arg[2]);

       $extern = isset($url_data['scheme']); 

        if($extern){
            $isValid = filter_var($arg[2],FILTER_VALIDATE_URL);
            if($isValid){
                return preg_replace_callback($exp,'cp_html_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a target="_blank" href="\1">\2</a>',$arg[0]));
            }
            return $arg[0];
            
        }else{
            if( isset($url_data['path']) && !file_exists($url_data['path']) ){
                return $arg[0];
            }
            return preg_replace_callback($exp,'cp_html_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a href="\1">\2</a>',$arg[0]));
        }
            
       
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
            return preg_replace_callback($exp,'cp_html_parseBbCode','<p>'.$arg[2].'</p>');
        case 'citation' : 
            return preg_replace_callback($exp,'cp_html_parseBbCode','<blockquote>'.$arg[2].'</blockquote>');
        case 'liste' : 
            return preg_replace_callback($exp,'cp_html_parseBbCode','<ul>'.$arg[2].'</ul>');
        case 'item' : 
            return preg_replace_callback($exp,'cp_html_parseBbCode','<li>'.$arg[2].'</li>');
        case 'it' : 
            return preg_replace_callback($exp,'cp_html_parseBbCode','<em>'.$arg[2].'</em>');
        case 'gras' : 
            return preg_replace_callback($exp,'cp_html_parseBbCode','<strong>'.$arg[2].'</strong>');
    }

    echo 'error';
    
}

/**
 * Return the html code of a tooltip
 * @param String $label    The tooltip message
 * @return String          The html tooltip 
 */
function cp_html_tooltip($label){
    return '<span class="tooltip-icon">&#128269;<span class="tooltip-content">'.$label.'</span></span>';
}


// STR

/**
 * Check if a string is an int
 * @param String $str The string to test
 * @return boolean    True if the string is an int, else false
 */
function cp_str_isInt($str){
    return preg_match('/^[[:digit:]]+$/',$str);
}

/**
 * Check if a string contains html tags
 * @param String $str   The string to test
 * @return boolean
 */
function cp_str_containsHTML($str){
    return ($str != str_replace(['>','<'],'',$str));
}



// PARAMETERS

/**
 * Checking parameters validity
 * @param Array array               The array containing the parameters
 * @param Array $mandatory_keys     The array containing the mandatory keys
 * @param Array $optional_keys      The array containing the optional keys
 * @return boolean                  True if the parameters are correct, else false
 */
function cp_check_param($array, $mandatory_keys, $optional_keys = array()){
    $array = array_keys($array);
    if (count(array_diff($mandatory_keys, $array)) > 0){
        return false;
    }
    if (count(array_diff($array, array_merge($mandatory_keys,$optional_keys))) > 0){
        return false;
    }
    
    return true;
}




// SESSION

/**
 * Correctly end a session and redirect to the given page.
 * @param String $page  The page for the redirection
 */
function cp_session_exit($page){
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

    setcookie('pseudo','',time()-3600*24,'/');
    setcookie('status','',time()-3600*24,'/');
    setcookie('key','',time()-3600*24,'/');
        
    header("Location: $page");
    exit(0);

}

/**
 * Check if the user is logged in.
 * @param mixed $page_to_go_if_not  If you want to redirect the user if he's not logged in, you must specify a page
 * @return boolean                  True if he's logged in, else false (only if he's not redirected)
 */
function cp_is_logged($page_to_go_if_not = false){
    $isLogged = (isset($_SESSION['pseudo']) && isset($_SESSION['status']));

    if(!$isLogged){
        if(isset($_COOKIE['pseudo']) && isset($_COOKIE['status']) && isset($_COOKIE['key'])){

           
            // mysqli_close($db);
            if(!hash_equals($_COOKIE['key'],hash('sha256',$_COOKIE['pseudo']).hash('sha256',$_COOKIE['status']))){
                cp_session_exit('.');
            }


            $_SESSION['pseudo'] = $_COOKIE['pseudo'];
            $_SESSION['status'] = $_COOKIE['status'];
            return true;
        }
    }
    if($isLogged){
        return true;
    }
    
    if(!$page_to_go_if_not){
        return false;
    }
    
    cp_session_exit($page_to_go_if_not);

}





// URL

/**
 * Crypt and sign url.
 * @param Array $data       All data to crypt in an array
 * @return String|false     The encrypted and signed url is success, false if failure
 */
function cp_encrypt_url($data){
    if(!defined('ENCRYPTION_KEY')){
        throw new Exception('[cp_encrypt_url] : The constant \'ENCRYPTION_KEY\' must be defined');
    }
    $data = implode('§',$data);

    $method = 'aes-128-gcm';
    $initVectorLen = openssl_cipher_iv_length($method);
    $initVector = openssl_random_pseudo_bytes($initVectorLen);
    $data = openssl_encrypt($data,$method,base64_decode(ENCRYPTION_KEY),OPENSSL_RAW_DATA,$initVector,$tag);
    if($data == false){
        return false;
    }
    $url = $initVector.$tag.$data;
    $url = base64_encode($url);
    return urlencode($url);

}

/**
 * Decrypts and authenticates the url. 
 * @param String $url   The url to decrypt
 * @param int $field    The number of field expected
 * @return Array|false  Decrypted and authenticated data if success, false if failure
 */
function cp_decrypt_url($url,$field){
    if(!defined('ENCRYPTION_KEY')){
        throw new Exception('[cp_decrypt_url] : The constant \'ENCRYPTION_KEY\' must be defined');
    }
    if($url == ""){
        return false;
    }
    $method = 'aes-128-gcm';
    $url = base64_decode($url);
    $initVectorLen = openssl_cipher_iv_length($method);
    $initVector = substr($url,0,$initVectorLen);
    $tagLen = 16;
    $tag = substr($url,$initVectorLen,$tagLen);
    $data = substr($url,$tagLen+$initVectorLen);
    
    $data = openssl_decrypt($data,$method,base64_decode(ENCRYPTION_KEY),OPENSSL_RAW_DATA,$initVector,$tag);
    
    if(!$data){
        return false;
    }
    $data = explode('§',$data);
    return (count($data) == $field)?$data:false ;

}


// INT
/**
 * Check is an integer is include between two others
 * @param $number The integer to test
 * @param $min The min limit
 * @param $max The max limit
 */
function cp_intIsBetween($number,$min,$max){
    if(!cp_str_isInt($number)){
        return false;
    }

    return $number >= $min && $number <= $max;
}





// PRINT

/**
 * Print a single button
 * @param String $type  The button's type (submit,reset,button)
 * @param String $value The button's value
 * @param String $name  The button's name
 */
function cp_print_button($type,$value,$name){
    return '<span class="btn-wrapper"><input type="'.$type.'" class="btn" value="'.$value.'" name="'.$name.'"></span>';
}

/**
 * Print a confirmation popup
 * @param String $firstbtnValue     The first button's value
 * @param String $title             The popup title
 * @param String $content           The popup content
 * @param String $btnType  The button's type (submit,reset,button)
 * @param String $btnValue The button's value
 * @param String $btnName  The button's name
 */
function cp_print_popUp($firstBtnValue,$title,$content,$btnType,$btnValue,$btnName){
    echo    "<span class='btn-wrapper'><label for='popup-first-btn' class='popup-btn btn'>$firstBtnValue</label></span>",
            '<input id="popup-first-btn" type="radio" name="popup-conf" class="popup-first-btn btn" value="none">',
            '<div class="popup-night">',
                '<div class="popup-box" >',
                    "<h4>$title</h4>",
                    "<p>$content</p>",
                    '<input id="popup-exit" type="radio" name="popup-conf">',
                    '<label for="popup-exit">&times;</label>',
                    "<span class='btn-wrapper'><input class='popup-final-btn btn' name='$btnName' value='$btnValue' type='$btnType'></span>",
                '</div>',
            '</div> ';
}

/**
 * Check if two array is equals for given keys
 * @param Array $array1 The first array
 * @param Array $array2 The second array
 * @param Array $keys   The keys to check
 * @return boolean
 */
function cp_arrayIsSame($array1, $array2,$keys){

    foreach($keys as $key){
        if($array1[$key] != $array2[$key]){
            return false;
        }
    }

    return true;
}