<?php 

/*################################################################
 *
 *              Forms functions librarie          
 *
 ###############################################################*/



require_once('bibli_generale.php');



/**
 * Print a number list in select form field
 * @param String $name  The field's name
 * @param int $min      The minimum value (include)
 * @param int $max      The maximum value (include) 
 * @param int $step     The list iteration step
 * @param int $default  The value selected by defalult
 */
function cp_form_print_numbersList($name,$min,$max,$step,$default){  
    if($min > $max ){
        throw new Exception('[cp_form_print_numbersList] : The min value can\'t be greater than the max.');
    }
    if($step == 0){
        throw new Exception('[cp_form_print_numbersList] : The step value can\'t be 0.');
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
function cp_form_print_list($name,$values,$default){
    echo '<select name="',$name,'">';
    foreach($values as $key => $value){
        echo '<option value="',$value,'" ',($default == $value)?'selected':'','>',$key,'</option>';
    }
    echo '</select>';
}

/**
 * Print a form array line for a list 
 * @param String $label     The line label
 * @param String name       The field's name
 * @param Array $values     The value list in the 'label'=>'value' format
 * @param String $default   The value selected by defalult
 */
function cp_form_print_listLine($label,$name,$value,$default,$tooltip = ''){
    echo '<tr>',
            '<td class="label"><label>',$label,'</label></td>',
            '<td class="input" >',
                cp_form_print_list($name,$value,$default),
                ($tooltip != '')?cp_html_tooltip($tooltip):'',
            '</td>',
           
        '</tr>';
}

/**
 * Print a month list in select form field
 * @param String $name        The field's name
 * @param String $default     The month selected by defalult
 */
function cp_form_print_monthsList($name,$default){
    cp_form_print_list($name,['Janvier' => 1, 'Février' => 2,'Mars' => 3, 'Avril' => 4,'Mai' => 5, 'Juin' => 6,'Juillet' => 7, 'Août' => 8,'Septembre' => 9, 'Octobre' => 10,'Novembre' => 11, 'Décembre' => 12,],$default);
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
function cp_form_print_datesList($name,$minYear,$maxYear,$defaultDay = 0, $defaultMonth = 0,$defaultYear = 0,$yearsStep = 1){
    $today=  explode('-',date('d-m-Y'));

    cp_form_print_numbersList($name.'_d',1,31,1,($defaultDay==0)?$today[0]:$defaultDay);
    cp_form_print_monthsList($name.'_m',($defaultMonth==0)?$today[1]:$defaultMonth);
    cp_form_print_numbersList($name.'_y',$minYear,($maxYear == 0)?$today[2]:$maxYear,$yearsStep,($defaultYear==0)?$today[2]:$defaultYear);
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
 */
function cp_form_print_DatesLine($label,$name,$minYear,$maxYear,$defaultDay = 0, $defaultMonth = 0,$defaultYear = 0,$yearsStep = 1,$tooltip = ''){
    echo '<tr>',
            '<td class="label"><label>',$label,'</label></td>',
            '<td class="input" >',
                cp_form_print_datesList($name,$minYear,$maxYear,$defaultDay,$defaultMonth,$defaultYear,$yearsStep),
                ($tooltip != '')?cp_html_tooltip($tooltip):'',
            '</td>',
            
        '</tr>';


}

/**
 * Print a form array input line 
 * @param String $label         The line label 
 * @param String $type          The input type (must be 'text','password' or 'email')
 * @param String $name          The field's name
 * @param int $maxLength        The (optional) maximum length
 * @param bool $required        True is the input field must be required, true by default
 * @param String $placeholder   The (optional) placeholder
 * @param String $value         The (optional) default value
 * @param String $tooltip       The (optional) information displayed in a tooltip
 */
function cp_form_print_inputLine($label,$type,$name,$maxLength = '',$required =true,$placeholder = '',$value = '',$tooltip = ''){
    if($type != 'text' && $type != 'password' && $type != 'email' && $type != 'file'){
        throw new Exception('[cp_form_print_inputLine] : The input type must be "text", "password" or "email".');
    }
    echo '<tr>',
            '<td class="label"><label for="',$name,'">',$label,'</label></td>',
            '<td class="input" >',
                '<input id="',$name,'" type="',$type,'" name="',$name,'" ',($required)?'required ':'','placeholder="',$placeholder,'" value="',$value,'" ','maxlength="',$maxLength,'">',
                ($tooltip != '')?cp_html_tooltip($tooltip):'',
            '</td>',
            
        '</tr>';
}

/**
 * Print a group of radio buttons
 * @param String $name          The field's name
 * @param Array $values         The value list in the 'label'=>'value' format
 * @param bool $required        True is the radio field must be required, true by default
 * @param String $default       The (optional) default value selected
 */
function cp_form_print_radios($name,$values,$required = true,$default = ''){
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
 * @param String $default       The (optional) default value selected
 * @param String $tooltip       The (optional) information displayed in a tooltip
 */
function cp_form_print_radiosLine($label,$name,$values,$required = true,$default = '',$tooltip = ''){
    echo    '<tr>',
                '<td class="label"><label>',$label,'</label></td>',
                '<td class="input" >',
                    cp_form_print_radios($name,$values,$required,$default),
                    ($tooltip != '')?cp_html_tooltip($tooltip):'',
                '</td>',
            '</tr>';
}

/**
 * Print a checkbox input
 * @param String $name          The field's name
 * @param String $label         The checkbox label
 * @param bool $required        True is thecheckbox must be required, true by default
 * @param bool $checked         True if the box is checked, false by defauly
 */
function cp_form_print_checkbox($name,$label,$required = true,$checked = false){
    echo '<input type="checkbox" name="',$name,'" id="',$name,'" ',($required)?'required':'',' ',($checked)?'checked':'','>',
            '<label for="',$name,'">',$label,'</label>';
}

/**
 * Print a form array line for a chackbox
 * @param String $name          The field's name
 * @param String $label         The checkbox label
 * @param bool $required        True is the checkbox must be required, true by default
 * @param bool $checked         True if the box is checked, false by defauly
 * @param String $tooltip       The (optional) information displayed in a tooltip
 */
function cp_form_print_checkboxLine($name,$label,$required = true, $checked = false,$tooltip = ''){
    echo '<tr>',
            '<td class="checkbox" colspan="2">',
                cp_form_print_checkbox($name,$label,$required,$checked),
                ($tooltip != '')?cp_html_tooltip($tooltip):'',
            '</td>',
            
        '</tr>';
}

/**
 * Print a form array line with submit and (optional) reset buttons
 * @param int $colspan          Value of the colspan
 * @param Array $submit         Value and name of submit button (0:value, 1:name)
 * @param String $resetValue    Value of reset button
 * @param bool $confirmSubmit    If true, the form submit will have to be confirmed
 * @param String $confirmSubmitLabel    Message to display in the confirmation box
 * @param bool $confirmReset     If true, the form reset will have to be confirmed
 * @param String $confirmResetLabel    Message to display in the confirmation box
 */ 
function cp_form_print_buttonsLine($colspan, $submit,$resetValue = '',$confirmSubmit = false, $confirmReset = false, $confirmSubmitLabel = '', $confirmResetLabel =''){
    if(!is_array($submit)){
        throw new Exception('[cp_form_print_buttonsLine] : $submit must be an array ');
    }
    echo '<tr>',
            '<td class="buttons" colspan="',$colspan,'">';
            
                if(!$confirmSubmit){
                    echo cp_print_button('submit',$submit[0],$submit[1]);
                }else{
                    echo cp_print_popUp($submit[0],'Vous êtes sûr ?',$confirmSubmitLabel,'submit','Oui je confirme','btnNewArticle');
                }
            
                
                if($resetValue != ''){
                    if(!$confirmReset){
                        echo cp_print_button('reset',$resetValue,'reset');
                    }else{
                        echo cp_print_popUp($resetValue,'Vous êtes sûr ?',$confirmResetLabel,'reset','Oui je confirme','reset');
                    }
                }
        echo '</td>',
        '</tr>';
   
}




/**
 * Print a form array line for a text area
 * @param String $label         The text area label
 * @param String $name          The field's name
 * @param String $value         The (optional) default value
 * @param bool $required        True is the field must be required, true by default
 * @param int $cols             The number of columns
 * @param int $rows             The number of rows
 * @param String $tooltip       The (optional) information displayed in a tooltip
 */
function cp_form_print_textAreaLine($label,$name,$value,$cols,$rows,$required = true,$tooltip = ''){
    echo '<tr class="textarea">',
            '<td><label for="',$name,'">',$label,($tooltip != '')?'  '.cp_html_tooltip($tooltip):'','</label></td>',
            '<td class="area">',
                '<textarea name="',$name,'" id="',$name,'" cols="',$cols,'" rows="',$rows,'" ',($required)?'required':'',' >',$value,'</textarea>',
            '</td>',
        '</tr>';
}


/**
 * Print a form array line for a file input
 * @param String $label         The file label
 * @param String $name          The field's name
 * @param bool $required        True is the file must be required, true by default
 * @param String $tooltip       The (optional) information displayed in a tooltip
 */
function cp_form_print_file($name,$label,$required = true,$tooltip = ''){
    echo '<tr>',
            '<td class="label"><label for="',$name,'">',$label,'</label></td>',
            '<td class="input">',
                '<input id="',$name,'" type="file" name="',$name,'" ',($required)?'required ':'','>',
                ($tooltip != '')?cp_html_tooltip($tooltip):'',
                '</td>',
        '</tr>';


}