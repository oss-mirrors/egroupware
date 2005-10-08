<?php
	/**************************************************************************\
	* eGroupWare - Insert File Dialog, File Manager -plugin for tinymce        *
	* http://www.eGroupWare.org                                                *
	* Authors Al Rashid <alrashid@klokan.sk>                                   *
	*     and Xiang Wei ZHUO <wei@zhuo.org>                                    *
	* Modified for eGW by Cornelius Weiss <egw@von-und-zu-weiss.de>            *
	* --------------------------------------------                             *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; version 2 of the License.                      *
	\**************************************************************************/


function dirs($dir,$abs_path) {
        $d = dir($dir);
        $dirs = array();
        while (false !== ($entry = $d->read())) {
                if(is_dir($dir.'/'.$entry) && substr($entry,0,1) != '.')  {
                        $path['path'] = $dir.'/'.$entry;
                        $path['name'] = $entry;
                        $dirs[$entry] = $path;
                }
        }
        $d->close();
        ksort($dirs);
        $cntDir = count($dirs);
        for($i=0; $i<$cntDir; $i++) {
                $name = key($dirs);
                $current_dir = $abs_path.'/'.$dirs[$name]['name'];
                echo ", '".sanitize2($current_dir)."/'\n";
                dirs($dirs[$name]['path'],$current_dir);
                next($dirs);
        }
}

function checkName($name) {
        $name = str_replace('../', '', $name);
        $name = str_replace('./', '', $name);
        return $name;
}
function sanitize2($name) {
        return str_replace("'", "\'", $name);
}

function unsanitize($name) {
        return str_replace("\'", "'", $name);
}

function pathSlashes($path) {
        if ('/' != substr($path,0,1)) $path =  '/'.$path;
        if ('/' != substr($path,-1,1)) $path = $path.'/';
        return $path;
}
function alertSanitize($path) {
        return ( sanitize2(str_replace("\\", "\\\\", $path)) );
}

function percent($p, $w) 
{ 
	return (real)(100 * ($p / $w)); 
} 

function unpercent($percent, $whole) 
{ 
	return (real)(($percent * $whole) / 100); 
} 
?>