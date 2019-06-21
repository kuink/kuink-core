<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for module kuink
 *
 * All the kuink specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_kuink
 * @copyright 2015 Kuink
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Checks if is_null
 * @param mixed $obj
 * @return string
 */
function kuink_isnull($obj)
{
    return isset($obj) ? $obj : '';
}

// ////////////////////////////////////////////////////////////////////////////////////
// / Any other kuink functions go here. Each of them must have a name that
// / starts with kuinkkuink_

// Private debug functions
function kuink_mydebug($name, $value)
{
    //	$html = '<div style="background-color:#BBFF00; font-size:small; border: solid 1px; border-color:#ffffff;"><b><i>->&nbsp;</i>' . $name . '</b> <i>' . $value . '</i></div>';
    //	$layout = \kuink\UI\Layout\Layout::getInstance ();
    //	$layout->addHtml ( $html, 'debugMessages' );

    return;
}
function kuink_mydebugxml($name, $xml)
{
    $xml2print = str_replace('>', '&gt;', $xml);
    $xml2print = str_replace('<', '&lt;', $xml);
    
    kuink_mydebug($name, $xml2print);
    return 1;
}
function kuink_mydebugobj($name, $obj)
{
    $value = var_export($obj, true);
    $order = [
            "\r\n",
            "\n",
            "\r"
    ];
    $replace = '<br />';
    
    // Processes \r\n's first so they aren't converted twice.
    $newstr = str_replace($order, $replace, $value);
    
    kuink_mydebug($name, $newstr);
    // var_dump( $obj );
    
    return 1;
}
function kuink_get_string($identifier, $app_name = 'framework', $params = null) {
	return \Kuink\Core\Language::getString ( $identifier, $app_name, $params );
}
function kuink_get_help_string($identifier, $app_name = 'framework') {
	return \Kuink\Core\Language::getHelpString ( $identifier, $app_name );
}
function redirect($url, $permanent = false, $global = 0)
{
    if ($global == 1) {
        print('<script language="javascript">window.location.replace(' . $url . ');</script>') ;
    } else {
        header('Location: ' . $url, true, $permanent ? 301 : 302);
    }
    
    exit();
}
