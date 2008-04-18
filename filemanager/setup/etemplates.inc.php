<?php
/**
	* eGroupWare - eTemplates for Application filemanager
	* http://www.egroupware.org 
	* generated by soetemplate::dump4setup() 2008-04-18 13:06
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package filemanager
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'filemanager.file','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:10:",redItalic";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:27:"General|Permissions|Preview";s:4:"name";s:21:"general|perms|preview";s:4:"span";s:3:"all";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";}i:2;a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:7:"onclick";s:15:"window.close();";}}}}s:4:"rows";i:3;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1204554434',);

$templ_data[] = array('name' => 'filemanager.file','template' => '','lang' => '','group' => '0','version' => '1.5.002','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:40:"General|Permissions|Extended ACL|Preview";s:4:"name";s:26:"general|perms|eacl|preview";s:4:"span";s:3:"all";}}i:3;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";}i:3;a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:7:"onclick";s:15:"window.close();";}}}}s:4:"rows";i:3;s:4:"cols";i:1;}}','size' => '','style' => '.eaclAccount select,.eaclRights select { width: 160px; }','modified' => '1207725030',);

$templ_data[] = array('name' => 'filemanager.file.eacl','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:4:",top";s:2:"c2";s:7:",bottom";s:2:"h2";s:11:",!@is_owner";}i:1;a:3:{s:1:"A";a:5:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:4:"span";s:3:"all";s:5:"label";s:28:"Extended access control list";i:1;a:7:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,200,,,,,auto";s:4:"data";a:3:{i:0;a:7:{s:1:"A";s:2:"80";s:1:"B";s:2:"80";s:1:"D";s:2:"16";s:2:"h2";s:4:",!@1";s:1:"C";s:3:"20%";s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Owner";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Rights";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Inherited";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:13:"${row}[owner]";s:8:"readonly";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:14:"${row}[rights]";s:8:"readonly";s:1:"1";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[path]";}s:1:"D";a:5:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"name";s:39:"delete[$row_cont[ino]-$row_cont[owner]]";s:7:"onclick";s:43:"return confirm(\'Delete this extended ACL\');";}}}s:4:"name";s:4:"eacl";s:4:"rows";i:2;s:4:"cols";i:4;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"200";i:6;s:4:"auto";}}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}i:2;a:3:{s:1:"A";a:5:{s:4:"type";s:14:"select-account";s:4:"size";s:15:"select one,both";s:4:"name";s:11:"eacl[owner]";s:4:"span";s:12:",eaclAccount";s:5:"label";s:5:"Owner";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:12:"eacl[rights]";s:4:"span";s:11:",eaclRights";s:5:"label";s:6:"Rights";}s:1:"C";a:3:{s:4:"type";s:6:"button";s:5:"label";s:3:"Add";s:4:"name";s:12:"button[eacl]";}}}s:4:"rows";i:2;s:4:"cols";i:3;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1207724932',);

$templ_data[] = array('name' => 'filemanager.file.general','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:9:{i:0;a:2:{s:1:"A";s:2:"80";s:2:"h1";s:2:"60";}i:1;a:2:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:4:"name";s:4:"icon";s:4:"span";s:9:",mimeHuge";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";s:6:"needed";s:1:"1";s:4:"span";s:9:",fileName";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"hrule";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:4:"mime";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Directory";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:3:"dir";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Size";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:5:"hsize";s:5:"label";s:17:"%s ($cont[size]b)";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Created";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"ctime";s:8:"readonly";s:1:"1";}}i:7;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Modified";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"mtime";s:8:"readonly";s:1:"1";}}i:8;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:8;s:4:"cols";i:2;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1204554817',);

$templ_data[] = array('name' => 'filemanager.file.perms','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"h3";s:9:",!@is_dir";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:12:"Accessrights";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:3:{s:1:"A";s:2:"80";s:2:"h5";s:2:",1";s:2:"h4";s:8:",@is_dir";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Owner";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[owner]";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Group";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[group]";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Other";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[other]";}}i:4;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:10:"Executable";s:4:"name";s:17:"perms[executable]";}}i:5;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:43:"Only owner can rename or delete the content";s:4:"name";s:13:"perms[sticky]";}}}s:4:"rows";i:5;s:4:"cols";i:2;s:7:"options";a:0:{}}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:5:"Owner";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:1:{s:1:"A";s:2:"80";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"User";}s:1:"B";a:4:{s:4:"type";s:14:"select-account";s:4:"size";s:13:"root,accounts";s:4:"name";s:3:"uid";s:5:"label";s:12:"@ro_uid_root";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Group";}s:1:"B";a:4:{s:4:"type";s:14:"select-account";s:4:"size";s:11:"root,groups";s:4:"name";s:3:"gid";s:5:"label";s:12:"@ro_gid_root";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:43:"Modify all Subdirectories and their content";s:4:"name";s:11:"modify_subs";}}}s:4:"rows";i:3;s:4:"cols";i:1;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1204567746',);

$templ_data[] = array('name' => 'filemanager.file.preview','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:5:{s:2:"c1";s:4:",top";s:2:"h1";s:16:",!@mime=/^image/";s:2:"h3";s:22:",@mime=/^(image|text)/";s:2:"h2";s:18:"280,!@text_content";s:2:"c2";s:4:",top";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"image";s:4:"name";s:4:"link";s:4:"span";s:13:",previewImage";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"textarea";s:4:"name";s:12:"text_content";s:4:"span";s:12:",previewText";s:8:"readonly";s:1:"1";}}i:3;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"No preview available";}}}s:4:"rows";i:3;s:4:"cols";i:1;s:4:"size";s:18:"450,300,,,10,,auto";s:7:"options";a:4:{i:0;s:3:"450";i:1;s:3:"300";i:6;s:4:"auto";i:4;s:2:"10";}}}','size' => '450,300,,,10,,auto','style' => '','modified' => '1204567479',);

$templ_data[] = array('name' => 'filemanager.index','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:1:"A";s:3:"250";s:2:"h1";s:10:",!@nm[msg]";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:7:"nm[msg]";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:10:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"7";i:1;a:4:{s:4:"type";s:6:"button";s:4:"size";s:4:"goup";s:5:"label";s:2:"Up";s:4:"name";s:10:"button[up]";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:12:"button[home]";s:4:"size";s:6:"gohome";s:5:"label";s:25:"Go to your home directory";}s:4:"span";s:3:"all";i:3;a:6:{s:4:"type";s:4:"text";s:4:"name";s:8:"nm[path]";s:4:"size";s:2:"80";s:5:"label";s:4:"Path";s:8:"onchange";i:1;s:4:"span";s:8:",address";}i:4;a:4:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[go]";s:4:"size";s:9:"key_enter";s:5:"label";s:5:"Go to";}i:5;a:2:{s:4:"type";s:5:"image";s:4:"name";s:15:"buttonseparator";}i:6;a:5:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[createdir]";s:4:"size";s:16:"button_createdir";s:5:"label";s:16:"Create directory";s:7:"onclick";s:128:"var dir = prompt(egw::lang(\'New directory\')); if (!dir) return false; document.getElementById(form::name(\'nm[path]\')).value=dir;";}i:7;a:4:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[paste]";s:4:"size";s:9:"editpaste";s:4:"help";s:20:"$cont[paste_tooltip]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:22:"filemanager.index.rows";s:4:"span";s:3:"all";s:4:"name";s:2:"nm";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:4:"file";s:4:"name";s:6:"upload";s:4:"help";s:42:"Select file to upload in current directory";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Upload";s:4:"name";s:14:"button[upload]";}}s:1:"B";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:6:"select";s:4:"name";s:6:"action";s:4:"size";s:16:"Select action...";s:8:"onchange";i:1;}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:70:"toggle_all(this.form,form::name(\'nm[rows][checked][]\')); return false;";s:6:"needed";s:1:"1";s:4:"span";s:14:",checkAllArrow";}}}}s:4:"rows";i:4;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1204374710',);

$templ_data[] = array('name' => 'filemanager.index.rows','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:6:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"B";s:3:"30%";s:1:"D";s:3:"120";s:1:"E";s:3:"120";s:1:"J";s:2:"70";}i:1;a:10:{s:1:"A";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Type";s:4:"name";s:4:"mime";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Size";s:4:"name";s:4:"size";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Modified";s:4:"name";s:5:"mtime";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"Created";s:4:"name";s:5:"ctime";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:11:"Permissions";s:4:"name";s:4:"mode";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"uid";s:5:"label";s:5:"Owner";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"gid";s:5:"label";s:5:"Group";}s:1:"I";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:7:"Comment";s:4:"name";s:7:"comment";}s:1:"J";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"2,,0,0";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:5:"check";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:60:"toggle_all(this.form,form::name(\'checked[]\')); return false;";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}}}i:2;a:10:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:5:"label";s:15:"$row_cont[mime]";s:4:"name";s:12:"${row}[icon]";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:5:"label";s:4:"size";s:14:",@${row}[link]";s:4:"name";s:12:"${row}[name]";s:7:"no_lang";s:1:"1";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:4:"name";s:13:"${row}[hsize]";s:5:"align";s:5:"right";}s:1:"D";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[mtime]";s:8:"readonly";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[ctime]";s:8:"readonly";s:1:"1";}s:1:"F";a:3:{s:4:"type";s:5:"label";s:5:"label";s:16:"$row_cont[perms]";s:4:"span";s:6:",perms";}s:1:"G";a:3:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[user]";s:7:"no_lang";s:1:"1";}s:1:"H";a:3:{s:4:"type";s:5:"label";s:4:"name";s:13:"${row}[group]";s:7:"no_lang";s:1:"1";}s:1:"I";a:2:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[comment]";}s:1:"J";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:21:"edit[$row_cont[path]]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:192:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path=$row_cont[path]\'),\'fileprefs\',\'dependent=yes,width=495,height=400,scrollbars=yes,status=yes\'); return false;";}i:2;a:7:{s:4:"type";s:6:"button";s:4:"name";s:23:"delete[$row_cont[path]]";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"help";s:29:"Delete this file or directory";s:7:"onclick";s:48:"return confirm(\'Delete this file or directory\');";s:5:"align";s:6:"center";}i:3;a:4:{s:4:"type";s:8:"checkbox";s:4:"name";s:9:"checked[]";s:5:"align";s:5:"right";s:4:"size";s:15:"$row_cont[path]";}s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:10;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1204370567',);

$templ_data[] = array('name' => 'filemanager.search','template' => '','lang' => '','group' => '0','version' => '1.3.001','data' => 'a:4:{i:0;a:9:{s:4:"type";s:8:"groupbox";s:4:"name";s:10:"debuginfos";s:4:"size";s:1:"4";s:5:"label";s:10:"Debuginfos";s:8:"disabled";s:1:"1";i:1;a:3:{s:4:"type";s:8:"textarea";s:4:"name";s:7:"message";s:8:"readonly";s:1:"1";}i:2;a:1:{s:4:"type";s:5:"label";}i:3;a:1:{s:4:"type";s:5:"label";}i:4;a:1:{s:4:"type";s:5:"label";}}i:1;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:2:{s:1:"C";s:3:"120";s:1:"D";s:3:"120";}i:1;a:5:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:12:"searchstring";s:4:"name";s:17:"searchstringlabel";}s:1:"B";a:3:{s:4:"type";s:4:"text";s:4:"span";s:1:"2";s:4:"name";s:12:"searchstring";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:3:{s:4:"type";s:6:"button";s:5:"label";s:12:"start search";s:4:"name";s:12:"start_search";}s:1:"E";a:1:{s:4:"type";s:5:"label";}}i:2;a:5:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"mime type";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:8:"checkall";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:5:"label";s:3:"All";s:4:"name";s:8:"alllabel";}s:1:"D";a:4:{s:4:"type";s:5:"label";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:5:"files";s:4:"name";s:14:"checkonlyfiles";}}}s:4:"rows";i:1;s:4:"cols";i:1;}s:1:"E";a:1:{s:4:"type";s:5:"label";}}i:3;a:5:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:14:"checkonlyfiles";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:5:"label";s:5:"files";s:4:"name";s:9:"filelabel";}s:1:"D";a:4:{s:4:"type";s:6:"button";s:5:"label";s:12:"clear search";s:4:"name";s:12:"clear_search";s:7:"onclick";s:71:"menuaction=filemanager.uifilemanager.index&action=search&actioncd=clear";}s:1:"E";a:1:{s:4:"type";s:5:"label";}}i:4;a:5:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:13:"checkonlydirs";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:5:"label";s:9:"directory";s:4:"name";s:8:"dirlabel";}s:1:"D";a:4:{s:4:"type";s:8:"checkbox";s:5:"label";s:5:"Debug";s:4:"name";s:5:"debug";s:8:"disabled";s:1:"1";}s:1:"E";a:1:{s:4:"type";s:5:"label";}}i:5;a:5:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:15:"created between";s:4:"name";s:12:"createdlabel";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:13:"searchcreated";}s:1:"C";a:3:{s:4:"type";s:4:"date";s:4:"name";s:15:"datecreatedfrom";s:4:"size";s:2:",8";}s:1:"D";a:3:{s:4:"type";s:4:"date";s:4:"name";s:13:"datecreatedto";s:4:"size";s:2:",8";}s:1:"E";a:4:{s:4:"type";s:8:"textarea";s:4:"name";s:17:"searchcreatedtext";s:8:"readonly";s:1:"1";s:7:"no_lang";s:1:"1";}}i:6;a:5:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:16:"modified between";s:4:"name";s:13:"modifiedlabel";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:14:"searchmodified";}s:1:"C";a:3:{s:4:"type";s:4:"date";s:4:"name";s:16:"datemodifiedfrom";s:4:"size";s:2:",8";}s:1:"D";a:3:{s:4:"type";s:4:"date";s:4:"name";s:14:"datemodifiedto";s:4:"size";s:2:",8";}s:1:"E";a:4:{s:4:"type";s:8:"textarea";s:4:"name";s:18:"searchmodifiedtext";s:8:"readonly";s:1:"1";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:6;s:4:"cols";i:5;}i:2;a:1:{s:4:"type";s:5:"hrule";}i:3;a:3:{s:4:"type";s:9:"nextmatch";s:4:"size";s:4:"rows";s:4:"name";s:2:"nm";}}','size' => '','style' => '','modified' => '1173101430',);

$templ_data[] = array('name' => 'filemanager.search.rows','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";}i:1;a:8:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"Id";s:4:"name";s:11:"vfs_file_id";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"file";s:4:"name";s:7:"fulldir";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:9:"Directory";s:4:"name";s:13:"vfs_directory";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"name";s:4:"name";s:8:"vfs_name";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"comment";s:4:"name";s:11:"vfs_comment";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:9:"mime type";s:4:"name";s:13:"vfs_mime_type";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"created";s:4:"name";s:11:"vfs_created";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"modified";s:4:"name";s:12:"vfs_modified";}}i:2;a:8:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_file_id]";s:7:"no_lang";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:4:"name";s:15:"${row}[fulldir]";s:7:"no_lang";s:1:"1";}s:1:"C";a:3:{s:4:"type";s:4:"html";s:4:"name";s:21:"${row}[vfs_directory]";s:7:"no_lang";s:1:"1";}s:1:"D";a:3:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[vfs_name]";s:7:"no_lang";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_comment]";s:7:"no_lang";s:1:"1";}s:1:"F";a:3:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[vfs_mime_type]";s:7:"no_lang";s:1:"1";}s:1:"G";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_created]";s:7:"no_lang";s:1:"1";}s:1:"H";a:3:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[vfs_modified]";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:2;s:4:"cols";i:8;s:4:"size";s:9:"100%,auto";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:4:"auto";}}}','size' => '100%,auto','style' => '','modified' => '1173104345',);

$templ_data[] = array('name' => 'filemanager.search.rows','template' => '','lang' => '','group' => '0','version' => '1.4.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"A";s:2:"20";}i:1;a:10:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Type";s:4:"name";s:13:"vfs_mime_type";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"Id";s:4:"name";s:11:"vfs_file_id";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"File";s:4:"name";s:7:"fulldir";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:9:"Directory";s:4:"name";s:13:"vfs_directory";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"name";s:4:"name";s:8:"vfs_name";}s:1:"F";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:9:"mime type";s:4:"name";s:9:"mime_type";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Size";s:4:"name";s:8:"vfs_size";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"comment";s:4:"name";s:11:"vfs_comment";}s:1:"I";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"created";s:4:"name";s:11:"vfs_created";}s:1:"J";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"modified";s:4:"name";s:12:"vfs_modified";}}i:2;a:10:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:5:"label";s:24:"$row_cont[vfs_mime_type]";s:4:"name";s:12:"${row}[icon]";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_file_id]";s:7:"no_lang";s:1:"1";}s:1:"C";a:4:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[file]";s:7:"no_lang";s:1:"1";s:4:"size";s:21:",$row_cont[file_link]";}s:1:"D";a:4:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[vfs_directory]";s:7:"no_lang";s:1:"1";s:4:"size";s:20:",$row_cont[dir_link]";}s:1:"E";a:4:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[vfs_name]";s:7:"no_lang";s:1:"1";s:4:"size";s:21:",$row_cont[file_link]";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[vfs_mime_type]";}s:1:"G";a:2:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[vfs_size]";}s:1:"H";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_comment]";s:7:"no_lang";s:1:"1";}s:1:"I";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_created]";s:7:"no_lang";s:1:"1";}s:1:"J";a:3:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[vfs_modified]";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:2;s:4:"cols";i:10;s:4:"size";s:9:"100%,auto";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:4:"auto";}}}','size' => '100%,auto','style' => '','modified' => '1173104345',);

$templ_data[] = array('name' => 'filemanager.search.rows','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"A";s:2:"20";}i:1;a:12:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Type";s:4:"name";s:13:"vfs_mime_type";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"Id";s:4:"name";s:11:"vfs_file_id";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"File";s:4:"name";s:7:"fulldir";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:9:"Directory";s:4:"name";s:13:"vfs_directory";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"name";s:4:"name";s:8:"vfs_name";}s:1:"F";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:9:"mime type";s:4:"name";s:9:"mime_type";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Size";s:4:"name";s:8:"vfs_size";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"comment";s:4:"name";s:11:"vfs_comment";}s:1:"I";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"created";s:4:"name";s:11:"vfs_created";}s:1:"J";a:3:{s:4:"type";s:23:"nextmatch-accountfilter";s:4:"size";s:10:"Created by";s:4:"name";s:16:"vfs_createdby_id";}s:1:"K";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"modified";s:4:"name";s:12:"vfs_modified";}s:1:"L";a:3:{s:4:"type";s:23:"nextmatch-accountfilter";s:4:"size";s:11:"Modified by";s:4:"name";s:17:"vfs_modifiedby_id";}}i:2;a:12:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:5:"label";s:24:"$row_cont[vfs_mime_type]";s:4:"name";s:12:"${row}[icon]";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_file_id]";s:7:"no_lang";s:1:"1";}s:1:"C";a:4:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[file]";s:7:"no_lang";s:1:"1";s:4:"size";s:21:",$row_cont[file_link]";}s:1:"D";a:4:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[vfs_directory]";s:7:"no_lang";s:1:"1";s:4:"size";s:20:",$row_cont[dir_link]";}s:1:"E";a:4:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[vfs_name]";s:7:"no_lang";s:1:"1";s:4:"size";s:21:",$row_cont[file_link]";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[vfs_mime_type]";}s:1:"G";a:3:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[vfs_size]";s:5:"align";s:5:"right";}s:1:"H";a:3:{s:4:"type";s:5:"label";s:4:"name";s:19:"${row}[vfs_comment]";s:7:"no_lang";s:1:"1";}s:1:"I";a:4:{s:4:"type";s:9:"date-time";s:4:"name";s:19:"${row}[vfs_created]";s:4:"size";s:11:"Y-m-d H:i:s";s:8:"readonly";s:1:"1";}s:1:"J";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:24:"${row}[vfs_createdby_id]";s:8:"readonly";s:1:"1";}s:1:"K";a:4:{s:4:"type";s:9:"date-time";s:4:"name";s:20:"${row}[vfs_modified]";s:4:"size";s:11:"Y-m-d H:i:s";s:8:"readonly";s:1:"1";}s:1:"L";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:25:"${row}[vfs_modifiedby_id]";s:8:"readonly";s:1:"1";}}}s:4:"rows";i:2;s:4:"cols";i:12;s:4:"size";s:9:"100%,auto";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:4:"auto";}}}','size' => '100%,auto','style' => '','modified' => '1173104345',);

