<?php
/**
	* eGroupWare - eTemplates for Application filemanager
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2009-05-11 22:46
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package filemanager
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'filemanager.file','template' => '','lang' => '','group' => '0','version' => '1.5.004','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:54:"General|Permissions|Extended ACL|Preview|Custom fields";s:4:"name";s:33:"general|perms|eacl|preview|custom";s:4:"span";s:3:"all";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";}i:3;a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:7:"onclick";s:15:"window.close();";}}i:2;a:6:{s:4:"type";s:10:"buttononly";s:5:"label";s:9:"Superuser";s:5:"align";s:5:"right";s:4:"help";s:48:"Enter setup user and password to get root rights";s:7:"onclick";s:121:"set_style_by_class(\'fieldset\',\'superuser\',\'display\',\'inline\'); document.getElementById(form::name(\'sudo[user]\')).focus();";s:4:"name";s:4:"sudo";}}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:29:"Enter setup user and password";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"User";s:4:"size";s:13:",,,sudo[user]";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:10:"sudo[user]";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:8:"Password";s:4:"size";s:15:",,,sudo[passwd]";}s:1:"B";a:2:{s:4:"type";s:6:"passwd";s:4:"name";s:12:"sudo[passwd]";}}i:3;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Submit";s:4:"name";s:13:"button[setup]";}}}s:7:"options";a:0:{}s:4:"rows";i:3;s:4:"cols";i:2;}s:4:"span";s:10:",superuser";}}}s:4:"rows";i:4;s:4:"cols";i:1;}}','size' => '','style' => '.eaclAccount select,.eaclRights select { width: 160px; }
.superuser { 
  position: absolute; 
  top: 130px; 
  left: 120px; 
  width: 200px; 
  background-color: white;
  z-index: 1;
  display: none;
}','modified' => '1223224423',);

$templ_data[] = array('name' => 'filemanager.file','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:54:"General|Permissions|Extended ACL|Preview|Custom fields";s:4:"name";s:38:"tabs=general|perms|eacl|preview|custom";s:4:"span";s:3:"all";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";}i:3;a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:7:"onclick";s:15:"window.close();";}}i:2;a:6:{s:4:"type";s:10:"buttononly";s:5:"label";s:9:"Superuser";s:5:"align";s:5:"right";s:4:"help";s:48:"Enter setup user and password to get root rights";s:7:"onclick";s:121:"set_style_by_class(\'fieldset\',\'superuser\',\'display\',\'inline\'); document.getElementById(form::name(\'sudo[user]\')).focus();";s:4:"name";s:4:"sudo";}}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:29:"Enter setup user and password";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"User";s:4:"size";s:13:",,,sudo[user]";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:10:"sudo[user]";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:8:"Password";s:4:"size";s:15:",,,sudo[passwd]";}s:1:"B";a:2:{s:4:"type";s:6:"passwd";s:4:"name";s:12:"sudo[passwd]";}}i:3;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Submit";s:4:"name";s:13:"button[setup]";}}}s:7:"options";a:0:{}s:4:"rows";i:3;s:4:"cols";i:2;}s:4:"span";s:10:",superuser";}}}s:4:"rows";i:4;s:4:"cols";i:1;}}','size' => '','style' => '.eaclAccount select,.eaclRights select { width: 160px; }
.superuser { 
  position: absolute; 
  top: 130px; 
  left: 120px; 
  width: 200px; 
  background-color: white;
  z-index: 1;
  display: none;
}','modified' => '1223224423',);

$templ_data[] = array('name' => 'filemanager.file.custom','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:12:"customfields";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:18:"450,300,,,10,,auto";}}','size' => '450,300,,,10,,auto','style' => '','modified' => '1223224487',);

$templ_data[] = array('name' => 'filemanager.file.eacl','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:4:",top";s:2:"c2";s:7:",bottom";s:2:"h2";s:11:",!@is_owner";}i:1;a:3:{s:1:"A";a:5:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:4:"span";s:3:"all";s:5:"label";s:28:"Extended access control list";i:1;a:7:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,200,,,,,auto";s:4:"data";a:3:{i:0;a:7:{s:1:"A";s:2:"80";s:1:"B";s:2:"80";s:1:"D";s:2:"16";s:2:"h2";s:4:",!@1";s:1:"C";s:3:"20%";s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Owner";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Rights";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Inherited";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:13:"${row}[owner]";s:8:"readonly";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:14:"${row}[rights]";s:8:"readonly";s:1:"1";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[path]";}s:1:"D";a:5:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"name";s:39:"delete[$row_cont[ino]-$row_cont[owner]]";s:7:"onclick";s:43:"return confirm(\'Delete this extended ACL\');";}}}s:4:"name";s:4:"eacl";s:4:"rows";i:2;s:4:"cols";i:4;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"200";i:6;s:4:"auto";}}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}i:2;a:3:{s:1:"A";a:5:{s:4:"type";s:14:"select-account";s:4:"size";s:15:"select one,both";s:4:"name";s:11:"eacl[owner]";s:4:"span";s:12:",eaclAccount";s:5:"label";s:5:"Owner";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:12:"eacl[rights]";s:4:"span";s:11:",eaclRights";s:5:"label";s:6:"Rights";}s:1:"C";a:3:{s:4:"type";s:6:"button";s:5:"label";s:3:"Add";s:4:"name";s:12:"button[eacl]";}}}s:4:"rows";i:2;s:4:"cols";i:3;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1207724932',);

$templ_data[] = array('name' => 'filemanager.file.general','template' => '','lang' => '','group' => '0','version' => '1.5.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:9:{i:0;a:2:{s:1:"A";s:2:"80";s:2:"h1";s:2:"60";}i:1;a:2:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:4:"name";s:4:"icon";s:4:"span";s:9:",mimeHuge";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";s:6:"needed";s:1:"1";s:4:"span";s:9:",fileName";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"hrule";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:4:"mime";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Directory";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:3:"dir";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Size";}s:1:"B";a:3:{s:4:"type";s:8:"vfs-size";s:4:"name";s:4:"size";s:4:"size";s:1:"1";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Created";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"ctime";s:8:"readonly";s:1:"1";}}i:7;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Modified";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"mtime";s:8:"readonly";s:1:"1";}}i:8;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:10:",,,comment";s:5:"label";s:7:"Comment";}s:1:"B";a:3:{s:4:"type";s:8:"textarea";s:4:"name";s:7:"comment";s:4:"span";s:8:",comment";}}}s:4:"rows";i:8;s:4:"cols";i:2;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1204554817',);

$templ_data[] = array('name' => 'filemanager.file.general','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:10:{i:0;a:4:{s:1:"A";s:2:"80";s:2:"h1";s:2:"60";s:2:"h3";s:10:",!@is_link";s:2:"h6";s:9:",@is_link";}i:1;a:2:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:4:"name";s:4:"icon";s:4:"span";s:9:",mimeHuge";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";s:6:"needed";s:1:"1";s:4:"span";s:9:",fileName";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"hrule";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"Link";s:4:"size";s:10:",,,symlink";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"span";s:9:",fileName";s:4:"name";s:7:"symlink";s:8:"readonly";s:1:"1";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:4:"mime";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Directory";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:3:"dir";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Size";}s:1:"B";a:3:{s:4:"type";s:8:"vfs-size";s:4:"name";s:4:"size";s:4:"size";s:1:"1";}}i:7;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Created";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"ctime";s:8:"readonly";s:1:"1";}}i:8;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Modified";}s:1:"B";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:5:"mtime";s:8:"readonly";s:1:"1";}}i:9;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:10:",,,comment";s:5:"label";s:7:"Comment";}s:1:"B";a:3:{s:4:"type";s:8:"textarea";s:4:"name";s:7:"comment";s:4:"span";s:8:",comment";}}}s:4:"rows";i:9;s:4:"cols";i:2;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1204554817',);

$templ_data[] = array('name' => 'filemanager.file.perms','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"h3";s:9:",!@is_dir";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:12:"Accessrights";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:3:{s:1:"A";s:2:"80";s:2:"h5";s:2:",1";s:2:"h4";s:8:",@is_dir";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Owner";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[owner]";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Group";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[group]";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Other";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"perms[other]";}}i:4;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:10:"Executable";s:4:"name";s:17:"perms[executable]";}}i:5;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:43:"Only owner can rename or delete the content";s:4:"name";s:13:"perms[sticky]";}}}s:4:"rows";i:5;s:4:"cols";i:2;s:7:"options";a:0:{}}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:5:"Owner";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:1:{s:1:"A";s:2:"80";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"User";}s:1:"B";a:4:{s:4:"type";s:14:"select-account";s:4:"size";s:13:"root,accounts";s:4:"name";s:3:"uid";s:5:"label";s:12:"@ro_uid_root";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Group";}s:1:"B";a:4:{s:4:"type";s:14:"select-account";s:4:"size";s:11:"root,groups";s:4:"name";s:3:"gid";s:5:"label";s:12:"@ro_gid_root";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:43:"Modify all Subdirectories and their content";s:4:"name";s:11:"modify_subs";}}}s:4:"rows";i:3;s:4:"cols";i:1;s:4:"size";s:12:"450,300,,,10";s:7:"options";a:3:{i:0;s:3:"450";i:1;s:3:"300";i:4;s:2:"10";}}}','size' => '450,300,,,10','style' => '','modified' => '1204567746',);

$templ_data[] = array('name' => 'filemanager.file.preview','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:5:{s:2:"c1";s:4:",top";s:2:"h1";s:16:",!@mime=/^image/";s:2:"h3";s:22:",@mime=/^(image|text)/";s:2:"h2";s:18:"280,!@text_content";s:2:"c2";s:4:",top";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"image";s:4:"name";s:4:"link";s:4:"span";s:13:",previewImage";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"textarea";s:4:"name";s:12:"text_content";s:4:"span";s:12:",previewText";s:8:"readonly";s:1:"1";}}i:3;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"No preview available";}}}s:4:"rows";i:3;s:4:"cols";i:1;s:4:"size";s:18:"450,300,,,10,,auto";s:7:"options";a:4:{i:0;s:3:"450";i:1;s:3:"300";i:6;s:4:"auto";i:4;s:2:"10";}}}','size' => '450,300,,,10,,auto','style' => '','modified' => '1204567479',);

$templ_data[] = array('name' => 'filemanager.index','template' => '','lang' => '','group' => '0','version' => '0.1','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:1:"A";s:3:"250";s:2:"h1";s:10:",!@nm[msg]";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:7:"nm[msg]";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:13:{s:4:"type";s:4:"hbox";s:4:"size";s:2:"10";i:1;a:4:{s:4:"type";s:5:"image";s:5:"label";s:2:"Up";s:4:"name";s:4:"goup";s:4:"size";s:40:"filemanager.filemanager_ui.index&path=..";}i:2;a:4:{s:4:"type";s:5:"image";s:4:"name";s:6:"gohome";s:4:"size";s:39:"filemanager.filemanager_ui.index&path=~";s:5:"label";s:25:"Go to your home directory";}s:4:"span";s:3:"all";i:3;a:5:{s:4:"type";s:3:"box";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:3:"vfs";s:4:"name";s:8:"nm[path]";s:7:"onclick";s:87:"path=document.getElementById(form::name(\'path\')); path.value=$path; path.form.submit();";s:4:"span";s:14:",pathSelection";}s:4:"span";s:23:",pathSelectionContainer";i:2;a:3:{s:4:"type";s:4:"text";s:4:"name";s:4:"path";s:4:"size";s:2:"16";}}i:4;a:4:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[go]";s:4:"size";s:9:"key_enter";s:5:"label";s:5:"Go to";}i:5;a:2:{s:4:"type";s:5:"image";s:4:"name";s:15:"buttonseparator";}i:6;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:23:"edit[{$cont[nm][path]}]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:194:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path={$cont[nm][path]}\'),\'fileprefs\',\'dependent=yes,width=495,height=425,scrollbars=yes,status=yes\'); return false;";}i:7;a:5:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[createdir]";s:4:"size";s:35:"button_createdir,createdir_disabled";s:5:"label";s:16:"Create directory";s:7:"onclick";s:128:"var dir = prompt(egw::lang(\'New directory\')); if (!dir) return false; document.getElementById(form::name(\'nm[path]\')).value=dir;";}i:8;a:5:{s:4:"type";s:6:"button";s:4:"size";s:18:"link,link_disabled";s:5:"label";s:13:"Create a link";s:4:"name";s:15:"button[symlink]";s:7:"onclick";s:129:"var link = prompt(egw::lang(\'Link target\')); if (!link) return false; document.getElementById(form::name(\'nm[path]\')).value=link;";}i:9;a:4:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[paste]";s:4:"size";s:28:"editpaste,editpaste_disabled";s:4:"help";s:20:"$cont[paste_tooltip]";}i:10;a:4:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[linkpaste]";s:4:"size";s:28:"linkpaste,linkpaste_disabled";s:4:"help";s:24:"$cont[linkpaste_tooltip]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:22:"filemanager.index.rows";s:4:"span";s:3:"all";s:4:"name";s:2:"nm";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"file";s:4:"name";s:8:"upload[]";s:4:"help";s:42:"Select file to upload in current directory";s:8:"onchange";s:99:"xajax_doXMLHTTP(\'filemanager_ui::ajax_check_upload_target\',this.id,this.value,\'{$cont[nm][path]}\');";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Upload";s:4:"name";s:14:"button[upload]";}}s:1:"B";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:6:"select";s:4:"name";s:6:"action";s:4:"size";s:16:"Select action...";s:8:"onchange";i:1;}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:70:"toggle_all(this.form,form::name(\'nm[rows][checked][]\')); return false;";s:6:"needed";s:1:"1";s:4:"span";s:14:",checkAllArrow";}}}}s:4:"rows";i:4;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1241620382',);

$templ_data[] = array('name' => 'filemanager.index','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:1:"A";s:3:"250";s:2:"h1";s:10:",!@nm[msg]";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:7:"nm[msg]";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:13:{s:4:"type";s:4:"hbox";s:4:"size";s:2:"10";i:1;a:4:{s:4:"type";s:6:"button";s:4:"size";s:4:"goup";s:5:"label";s:2:"Up";s:4:"name";s:10:"button[up]";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:12:"button[home]";s:4:"size";s:6:"gohome";s:5:"label";s:25:"Go to your home directory";}s:4:"span";s:3:"all";i:3;a:6:{s:4:"type";s:4:"text";s:4:"name";s:8:"nm[path]";s:4:"size";s:2:"80";s:5:"label";s:4:"Path";s:8:"onchange";i:1;s:4:"span";s:8:",address";}i:4;a:4:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[go]";s:4:"size";s:9:"key_enter";s:5:"label";s:5:"Go to";}i:5;a:2:{s:4:"type";s:5:"image";s:4:"name";s:15:"buttonseparator";}i:6;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:23:"edit[{$cont[nm][path]}]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:194:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path={$cont[nm][path]}\'),\'fileprefs\',\'dependent=yes,width=495,height=425,scrollbars=yes,status=yes\'); return false;";}i:7;a:5:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[createdir]";s:4:"size";s:35:"button_createdir,createdir_disabled";s:5:"label";s:16:"Create directory";s:7:"onclick";s:128:"var dir = prompt(egw::lang(\'New directory\')); if (!dir) return false; document.getElementById(form::name(\'nm[path]\')).value=dir;";}i:8;a:5:{s:4:"type";s:6:"button";s:4:"size";s:18:"link,link_disabled";s:5:"label";s:13:"Create a link";s:4:"name";s:15:"button[symlink]";s:7:"onclick";s:129:"var link = prompt(egw::lang(\'Link target\')); if (!link) return false; document.getElementById(form::name(\'nm[path]\')).value=link;";}i:9;a:4:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[paste]";s:4:"size";s:28:"editpaste,editpaste_disabled";s:4:"help";s:20:"$cont[paste_tooltip]";}i:10;a:4:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[linkpaste]";s:4:"size";s:28:"linkpaste,linkpaste_disabled";s:4:"help";s:24:"$cont[linkpaste_tooltip]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:22:"filemanager.index.rows";s:4:"span";s:3:"all";s:4:"name";s:2:"nm";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"file";s:4:"name";s:8:"upload[]";s:4:"help";s:42:"Select file to upload in current directory";s:8:"onchange";s:99:"xajax_doXMLHTTP(\'filemanager_ui::ajax_check_upload_target\',this.id,this.value,\'{$cont[nm][path]}\');";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Upload";s:4:"name";s:14:"button[upload]";}}s:1:"B";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:6:"select";s:4:"name";s:6:"action";s:4:"size";s:16:"Select action...";s:8:"onchange";i:1;}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:70:"toggle_all(this.form,form::name(\'nm[rows][checked][]\')); return false;";s:6:"needed";s:1:"1";s:4:"span";s:14:",checkAllArrow";}}}}s:4:"rows";i:4;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1237464702',);

$templ_data[] = array('name' => 'filemanager.index','template' => '','lang' => '','group' => '0','version' => '1.7.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:1:"A";s:3:"250";s:2:"h1";s:10:",!@nm[msg]";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:7:"nm[msg]";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:13:{s:4:"type";s:4:"hbox";s:4:"size";s:2:"10";i:1;a:4:{s:4:"type";s:5:"image";s:5:"label";s:2:"Up";s:4:"name";s:4:"goup";s:4:"size";s:40:"filemanager.filemanager_ui.index&path=..";}i:2;a:4:{s:4:"type";s:5:"image";s:4:"name";s:6:"gohome";s:4:"size";s:39:"filemanager.filemanager_ui.index&path=~";s:5:"label";s:25:"Go to your home directory";}s:4:"span";s:3:"all";i:3;a:6:{s:4:"type";s:4:"text";s:4:"name";s:8:"nm[path]";s:4:"size";s:2:"80";s:5:"label";s:4:"Path";s:8:"onchange";i:1;s:4:"span";s:8:",address";}i:4;a:4:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[go]";s:4:"size";s:9:"key_enter";s:5:"label";s:5:"Go to";}i:5;a:2:{s:4:"type";s:5:"image";s:4:"name";s:15:"buttonseparator";}i:6;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:23:"edit[{$cont[nm][path]}]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:194:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path={$cont[nm][path]}\'),\'fileprefs\',\'dependent=yes,width=495,height=425,scrollbars=yes,status=yes\'); return false;";}i:7;a:5:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[createdir]";s:4:"size";s:35:"button_createdir,createdir_disabled";s:5:"label";s:16:"Create directory";s:7:"onclick";s:128:"var dir = prompt(egw::lang(\'New directory\')); if (!dir) return false; document.getElementById(form::name(\'nm[path]\')).value=dir;";}i:8;a:5:{s:4:"type";s:6:"button";s:4:"size";s:18:"link,link_disabled";s:5:"label";s:13:"Create a link";s:4:"name";s:15:"button[symlink]";s:7:"onclick";s:129:"var link = prompt(egw::lang(\'Link target\')); if (!link) return false; document.getElementById(form::name(\'nm[path]\')).value=link;";}i:9;a:4:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[paste]";s:4:"size";s:28:"editpaste,editpaste_disabled";s:4:"help";s:20:"$cont[paste_tooltip]";}i:10;a:4:{s:4:"type";s:6:"button";s:4:"name";s:17:"button[linkpaste]";s:4:"size";s:28:"linkpaste,linkpaste_disabled";s:4:"help";s:24:"$cont[linkpaste_tooltip]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:22:"filemanager.index.rows";s:4:"span";s:3:"all";s:4:"name";s:2:"nm";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"file";s:4:"name";s:8:"upload[]";s:4:"help";s:42:"Select file to upload in current directory";s:8:"onchange";s:99:"xajax_doXMLHTTP(\'filemanager_ui::ajax_check_upload_target\',this.id,this.value,\'{$cont[nm][path]}\');";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Upload";s:4:"name";s:14:"button[upload]";}}s:1:"B";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:4:{s:4:"type";s:6:"select";s:4:"name";s:6:"action";s:4:"size";s:16:"Select action...";s:8:"onchange";i:1;}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:70:"toggle_all(this.form,form::name(\'nm[rows][checked][]\')); return false;";s:6:"needed";s:1:"1";s:4:"span";s:14:",checkAllArrow";}}}}s:4:"rows";i:4;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1237464702',);

$templ_data[] = array('name' => 'filemanager.index.rows','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:6:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"B";s:3:"30%";s:1:"D";s:3:"120";s:1:"E";s:3:"120";s:1:"K";s:2:"70";}i:1;a:11:{s:1:"A";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Type";s:4:"name";s:4:"mime";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Size";s:4:"name";s:4:"size";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Modified";s:4:"name";s:5:"mtime";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"Created";s:4:"name";s:5:"ctime";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:11:"Permissions";s:4:"name";s:4:"mode";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"uid";s:5:"label";s:5:"Owner";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"gid";s:5:"label";s:5:"Group";}s:1:"I";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:7:"Comment";s:4:"name";s:7:"comment";}s:1:"J";a:3:{s:4:"type";s:22:"nextmatch-customfields";s:8:"readonly";s:1:"1";s:4:"name";s:12:"customfields";}s:1:"K";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"2,,0,0";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:5:"check";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:60:"toggle_all(this.form,form::name(\'checked[]\')); return false;";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}}}i:2;a:11:{s:1:"A";a:4:{s:4:"type";s:5:"image";s:5:"label";s:15:"$row_cont[mime]";s:4:"name";s:12:"${row}[icon]";s:5:"align";s:6:"center";}s:1:"B";a:4:{s:4:"type";s:5:"label";s:4:"size";s:14:",@${row}[link]";s:4:"name";s:12:"${row}[name]";s:7:"no_lang";s:1:"1";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:4:"name";s:13:"${row}[hsize]";s:5:"align";s:5:"right";}s:1:"D";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[mtime]";s:8:"readonly";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[ctime]";s:8:"readonly";s:1:"1";}s:1:"F";a:3:{s:4:"type";s:5:"label";s:5:"label";s:16:"$row_cont[perms]";s:4:"span";s:6:",perms";}s:1:"G";a:3:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[user]";s:7:"no_lang";s:1:"1";}s:1:"H";a:3:{s:4:"type";s:5:"label";s:4:"name";s:13:"${row}[group]";s:7:"no_lang";s:1:"1";}s:1:"I";a:2:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[comment]";}s:1:"J";a:3:{s:4:"type";s:17:"customfields-list";s:8:"readonly";s:1:"1";s:4:"name";s:4:"$row";}s:1:"K";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:21:"edit[$row_cont[path]]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:192:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path=$row_cont[path]\'),\'fileprefs\',\'dependent=yes,width=495,height=400,scrollbars=yes,status=yes\'); return false;";}i:2;a:7:{s:4:"type";s:6:"button";s:4:"name";s:23:"delete[$row_cont[path]]";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"help";s:29:"Delete this file or directory";s:7:"onclick";s:48:"return confirm(\'Delete this file or directory\');";s:5:"align";s:6:"center";}i:3;a:4:{s:4:"type";s:8:"checkbox";s:4:"name";s:9:"checked[]";s:5:"align";s:5:"right";s:4:"size";s:15:"$row_cont[path]";}s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:11;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1204370567',);

$templ_data[] = array('name' => 'filemanager.index.rows','template' => '','lang' => '','group' => '0','version' => '1.5.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:6:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"B";s:3:"30%";s:1:"D";s:3:"120";s:1:"E";s:3:"120";s:1:"K";s:2:"70";}i:1;a:11:{s:1:"A";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Type";s:4:"name";s:4:"mime";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Name";s:4:"name";s:4:"name";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Size";s:4:"name";s:4:"size";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Modified";s:4:"name";s:5:"mtime";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:7:"Created";s:4:"name";s:5:"ctime";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:11:"Permissions";s:4:"name";s:4:"mode";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"uid";s:5:"label";s:5:"Owner";}s:1:"H";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:3:"gid";s:5:"label";s:5:"Group";}s:1:"I";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:7:"Comment";s:4:"name";s:7:"comment";}s:1:"J";a:3:{s:4:"type";s:22:"nextmatch-customfields";s:8:"readonly";s:1:"1";s:4:"name";s:12:"customfields";}s:1:"K";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"2,,0,0";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";}i:2;a:8:{s:4:"type";s:6:"button";s:4:"size";s:5:"check";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:60:"toggle_all(this.form,form::name(\'checked[]\')); return false;";s:6:"needed";s:1:"1";s:5:"align";s:5:"right";}}}i:2;a:11:{s:1:"A";a:3:{s:4:"type";s:8:"vfs-mime";s:4:"name";s:12:"${row}[path]";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:3:"vfs";s:4:"name";s:4:"$row";s:7:"no_lang";s:1:"1";}s:1:"C";a:3:{s:4:"type";s:8:"vfs-size";s:4:"name";s:12:"${row}[size]";s:5:"align";s:5:"right";}s:1:"D";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[mtime]";s:8:"readonly";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:13:"${row}[ctime]";s:8:"readonly";s:1:"1";}s:1:"F";a:2:{s:4:"type";s:8:"vfs-mode";s:4:"name";s:12:"${row}[mode]";}s:1:"G";a:3:{s:4:"type";s:7:"vfs-uid";s:4:"name";s:11:"${row}[uid]";s:7:"no_lang";s:1:"1";}s:1:"H";a:3:{s:4:"type";s:7:"vfs-gid";s:4:"name";s:11:"${row}[gid]";s:7:"no_lang";s:1:"1";}s:1:"I";a:2:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[comment]";}s:1:"J";a:3:{s:4:"type";s:17:"customfields-list";s:8:"readonly";s:1:"1";s:4:"name";s:4:"$row";}s:1:"K";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:13:"Edit settings";s:4:"name";s:21:"edit[$row_cont[path]]";s:4:"help";s:39:"Rename, change permissions or ownership";s:7:"onclick";s:192:"window.open(egw::link(\'/index.php\',\'menuaction=filemanager.filemanager_ui.file&path=$row_cont[path]\'),\'fileprefs\',\'dependent=yes,width=495,height=425,scrollbars=yes,status=yes\'); return false;";}i:2;a:7:{s:4:"type";s:6:"button";s:4:"name";s:23:"delete[$row_cont[path]]";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"help";s:29:"Delete this file or directory";s:7:"onclick";s:48:"return confirm(\'Delete this file or directory\');";s:5:"align";s:6:"center";}i:3;a:4:{s:4:"type";s:8:"checkbox";s:4:"name";s:9:"checked[]";s:5:"align";s:5:"right";s:4:"size";s:17:""$row_cont[path]"";}s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:11;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1204370567',);

$templ_data[] = array('name' => 'filemanager.select','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:2:{s:2:"c1";s:4:",top";s:1:"A";s:2:"32";}i:1;a:2:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:5:"align";s:6:"center";s:4:"data";a:3:{i:0;a:2:{s:2:"h1";s:2:"40";s:2:"h2";s:2:"40";}i:1;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"size";s:4:"home";s:4:"name";s:4:"home";s:5:"align";s:6:"center";s:5:"label";s:4:"Home";}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"size";s:16:"$row_cont/navbar";s:5:"label";s:9:"$row_cont";s:5:"align";s:6:"center";s:4:"name";s:6:"${row}";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"name";s:4:"apps";s:7:"options";a:0:{}}s:1:"B";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:3:{s:2:"c2";s:11:"selectFiles";s:2:"h3";s:35:",@mode=/(open-multiple|select-dir)/";s:2:"h4";s:15:",!@options-mime";}i:1;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";i:1;a:2:{s:4:"type";s:4:"html";s:4:"name";s:2:"js";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[up]";s:4:"size";s:4:"goup";}i:3;a:4:{s:4:"type";s:3:"vfs";s:4:"name";s:4:"path";s:7:"onclick";s:87:"path=document.getElementById(form::name(\'path\')); path.value=$path; path.form.submit();";s:4:"span";s:11:",selectPath";}i:4;a:3:{s:4:"type";s:4:"text";s:4:"name";s:4:"path";s:4:"span";s:12:",displayNone";}}}i:2;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,400,,,,,auto";s:4:"name";s:3:"dir";s:4:"data";a:2:{i:0;a:3:{s:2:"c1";s:3:"row";s:1:"A";s:2:"20";s:1:"C";s:23:"1%,!@mode=open-multiple";}i:1;a:3:{s:1:"A";a:4:{s:4:"type";s:8:"vfs-mime";s:4:"name";s:12:"${row}[path]";s:4:"span";s:11:",selectIcon";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:3:"vfs";s:4:"name";s:4:"$row";s:7:"onclick";s:18:"$row_cont[onclick]";}s:1:"C";a:4:{s:4:"type";s:8:"checkbox";s:4:"size";s:17:""$row_cont[name]"";s:4:"name";s:10:"selected[]";s:5:"align";s:5:"right";}}}s:4:"rows";i:1;s:4:"cols";i:3;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";s:4:"span";s:11:",selectName";s:6:"needed";s:1:"1";}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"select";s:4:"size";s:9:"All files";s:4:"name";s:4:"mime";s:4:"span";s:11:",selectMime";s:8:"onchange";i:1;}}i:5;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"@label";s:4:"name";s:10:"button[ok]";}i:2;a:4:{s:4:"type";s:10:"buttononly";s:4:"name";s:14:"button[cancel]";s:5:"label";s:6:"Cancel";s:7:"onclick";s:15:"window.close();";}s:5:"align";s:5:"right";}}}s:4:"rows";i:5;s:4:"cols";i:1;s:7:"options";a:1:{i:0;s:4:"100%";}s:4:"size";s:4:"100%";}}}s:4:"rows";i:1;s:4:"cols";i:2;s:4:"size";s:3:"600";s:7:"options";a:1:{i:0;s:3:"600";}}}','size' => '600','style' => '','modified' => '1239794780',);

$templ_data[] = array('name' => 'filemanager.select','template' => '','lang' => '','group' => '0','version' => '1.7.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:2:{s:2:"c1";s:4:",top";s:1:"A";s:2:"32";}i:1;a:2:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:5:"align";s:6:"center";s:4:"data";a:3:{i:0;a:2:{s:2:"h2";s:2:"40";s:2:"h1";s:4:",!@0";}i:1;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"size";s:9:"favorites";s:5:"label";s:9:"Favorites";s:5:"align";s:6:"center";s:4:"name";s:9:"favorites";}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"size";s:16:"$row_cont/navbar";s:5:"label";s:9:"$row_cont";s:5:"align";s:6:"center";s:4:"name";s:6:"${row}";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"name";s:4:"apps";s:7:"options";a:0:{}}s:1:"B";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:3:{s:2:"c2";s:11:"selectFiles";s:2:"h3";s:35:",@mode=/(open-multiple|select-dir)/";s:2:"h4";s:15:",!@options-mime";}i:1;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"5";i:1;a:2:{s:4:"type";s:4:"html";s:4:"name";s:2:"js";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:10:"button[up]";s:4:"size";s:4:"goup";s:5:"label";s:2:"Up";}i:3;a:4:{s:4:"type";s:6:"button";s:4:"name";s:12:"button[home]";s:4:"size";s:6:"gohome";s:5:"label";s:25:"Go to your home directory";}i:4;a:4:{s:4:"type";s:3:"box";s:4:"size";s:1:"1";i:1;a:4:{s:4:"type";s:3:"vfs";s:4:"name";s:4:"path";s:7:"onclick";s:87:"path=document.getElementById(form::name(\'path\')); path.value=$path; path.form.submit();";s:4:"span";s:11:",selectPath";}s:4:"span";s:20:",selectPathContainer";}i:5;a:3:{s:4:"type";s:4:"text";s:4:"name";s:4:"path";s:4:"span";s:12:",displayNone";}}}i:2;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,400,,,,,auto";s:4:"name";s:3:"dir";s:4:"data";a:2:{i:0;a:3:{s:2:"c1";s:3:"row";s:1:"A";s:2:"20";s:1:"C";s:23:"1%,!@mode=open-multiple";}i:1;a:3:{s:1:"A";a:4:{s:4:"type";s:8:"vfs-mime";s:4:"name";s:12:"${row}[path]";s:4:"span";s:11:",selectIcon";s:5:"align";s:6:"center";}s:1:"B";a:3:{s:4:"type";s:3:"vfs";s:4:"name";s:4:"$row";s:7:"onclick";s:18:"$row_cont[onclick]";}s:1:"C";a:4:{s:4:"type";s:8:"checkbox";s:4:"size";s:17:""$row_cont[name]"";s:4:"name";s:10:"selected[]";s:5:"align";s:5:"right";}}}s:4:"rows";i:1;s:4:"cols";i:3;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";s:4:"span";s:11:",selectName";s:6:"needed";s:1:"1";}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"select";s:4:"size";s:9:"All files";s:4:"name";s:4:"mime";s:4:"span";s:11:",selectMime";s:8:"onchange";i:1;}}i:5;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"@label";s:4:"name";s:10:"button[ok]";}i:2;a:4:{s:4:"type";s:10:"buttononly";s:4:"name";s:14:"button[cancel]";s:5:"label";s:6:"Cancel";s:7:"onclick";s:15:"window.close();";}s:5:"align";s:5:"right";}}}s:4:"rows";i:5;s:4:"cols";i:1;s:7:"options";a:1:{i:0;s:4:"100%";}s:4:"size";s:4:"100%";}}}s:4:"rows";i:1;s:4:"cols";i:2;s:4:"size";s:3:"600";s:7:"options";a:1:{i:0;s:3:"600";}}}','size' => '600','style' => '','modified' => '1239794780',);

