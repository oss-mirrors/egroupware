<?php
/**
	* eGroupWare - eTemplates for Application importexport
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2010-03-03 11:38
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package importexport
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'importexport.definition_index','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:4:{s:4:"span";s:13:"all,redItalic";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";s:4:"type";s:5:"label";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:7:"row,top";}i:1;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";s:4:"span";s:11:",lr_padding";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:5:"label";s:4:"Name";s:4:"span";s:11:",lr_padding";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:5:"label";s:11:"Application";s:4:"span";s:11:",lr_padding";}s:1:"D";a:4:{s:5:"align";s:6:"center";s:4:"type";s:5:"label";s:5:"label";s:13:"Allowed users";s:4:"span";s:11:",lr_padding";}s:1:"E";a:5:{s:5:"label";s:3:"Add";s:5:"align";s:6:"center";s:4:"type";s:6:"button";s:4:"span";s:11:",lr_padding";s:7:"onclick";s:213:"window.open(egw::link(\'/index.php\',\'menuaction=importexport.uidefinitions.wizzard\'),\'\',\'dependent=yes,width=400,height=400,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false; return false;";}s:1:"F";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:6:{s:5:"label";s:6:"Delete";s:4:"name";s:15:"delete_selected";s:4:"type";s:6:"button";s:4:"help";s:31:"delete ALL selected definitions";s:4:"size";s:6:"delete";s:7:"onclick";s:65:"return confirm(\'Do you really want to DELETE this definitions?\');";}i:2;a:5:{s:5:"label";s:6:"Export";s:4:"name";s:15:"export_selected";s:4:"type";s:6:"button";s:4:"help";s:31:"export ALL selected definitions";s:4:"size";s:10:"fileexport";}}}i:2;a:6:{s:1:"A";a:4:{s:7:"no_lang";s:1:"1";s:4:"type";s:5:"image";s:4:"span";s:11:",lr_padding";s:4:"name";s:12:"${row}[type]";}s:1:"B";a:4:{s:7:"no_lang";s:1:"1";s:4:"name";s:12:"${row}[name]";s:4:"type";s:5:"label";s:4:"span";s:11:",lr_padding";}s:1:"C";a:4:{s:7:"no_lang";s:1:"1";s:4:"name";s:19:"${row}[application]";s:4:"type";s:5:"label";s:4:"span";s:11:",lr_padding";}s:1:"D";a:6:{s:7:"no_lang";s:1:"1";s:4:"type";s:14:"select-account";s:4:"span";s:11:",lr_padding";s:8:"readonly";s:1:"1";s:4:"name";s:21:"${row}[allowed_users]";s:4:"size";s:6:"5,both";}s:1:"E";a:5:{s:5:"align";s:6:"center";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:5:"label";s:4:"Edit";s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:7:"onclick";s:237:"window.open(egw::link(\'/index.php\',\'menuaction=importexport.uidefinitions.edit&definition=$row_cont[name]\'),\'\',\'dependent=yes,width=500,height=500,location=no,menubar=no,toolbar=no,scrollbars=yes,status=yes\'); return false; return false;";}i:2;a:6:{s:5:"label";s:6:"Delete";s:7:"onclick";s:41:"return confirm(\'Delete this definition\');";s:4:"name";s:32:"delete[$row_cont[definition_id]]";s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:4:"help";s:21:"Delete this eTemplate";}}s:1:"F";a:4:{s:5:"align";s:6:"center";s:4:"name";s:34:"selected[$row_cont[definition_id]]";s:4:"type";s:8:"checkbox";s:4:"help";s:34:"select this eTemplate to delete it";}}}s:4:"cols";i:6;s:4:"rows";i:2;}}}s:4:"cols";i:1;s:4:"rows";i:2;s:4:"size";s:4:"100%";}}','size' => '100%','style' => '.redItalic { color:red; font-style:italic;}			td.lr_padding { padding-left: 5px; padding-right: 5px; }','modified' => '1145972373',);

$templ_data[] = array('name' => 'importexport.export_dialog','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:1:{s:2:"c3";s:15:"save_definition";}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:3:"tab";s:5:"label";s:25:"General|Selection|Options";s:4:"name";s:37:"general_tab|selection_tab|options_tab";}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"checkbox";s:5:"label";s:18:"Save as definition";s:4:"name";s:18:"save_as_definition";}}i:4;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:3:"all";i:1;a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Export";s:4:"name";s:6:"export";s:7:"onclick";s:36:"xajax_eT_wrapper(this);return false;";}i:2;a:4:{s:4:"type";s:6:"button";s:5:"label";s:7:"Preview";s:4:"name";s:7:"preview";s:7:"onclick";s:36:"xajax_eT_wrapper(this);return false;";}}i:2;a:5:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:5:"align";s:5:"right";s:4:"name";s:6:"cancel";s:7:"onclick";s:29:"window.close(); return false;";}}}i:5;a:1:{s:1:"A";a:6:{s:4:"type";s:3:"box";s:4:"size";s:1:"1";s:4:"name";s:11:"preview-box";s:6:"needed";s:1:"1";i:1;a:1:{s:4:"type";s:5:"label";}s:4:"span";s:12:",preview-box";}}i:6;a:1:{s:1:"A";a:7:{s:4:"type";s:3:"box";s:4:"size";s:1:"1";s:4:"span";s:20:",preview-box-buttons";s:4:"name";s:19:"preview-box-buttons";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:2:"OK";s:5:"align";s:6:"center";s:7:"onclick";s:167:"document.getElementById(form::name(\'preview-box\')).style.display=\'none\'; document.getElementById(form::name(\'preview-box-buttons\')).style.display=\'none\'; return false;";}s:6:"needed";s:1:"1";s:5:"align";s:6:"center";}}}s:4:"rows";i:6;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '.preview-box {
  position: absolute;
  top: 0px;
  left: 0px;
  width: 400px;
  height: 360px;
  overflow: scroll;
  background-color: white;
  z-index: 999;
  display: none;
}
.preview-box-buttons {
  position: absolute;
  top: 365px;
  left: 0px;
  width: 400px;
  height: 20px;

  z-index: 999;
  display: none;
}','modified' => '1158220473',);

$templ_data[] = array('name' => 'importexport.export_dialog.general_tab','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:6:"export";}s:1:"B";a:2:{s:4:"type";s:8:"template";s:4:"name";s:46:"importexport.export_dialog.general_tab_content";}}}s:4:"rows";i:1;s:4:"cols";i:2;s:4:"size";s:6:",200px";s:7:"options";a:1:{i:1;s:5:"200px";}}}','size' => ',200px','style' => '','modified' => '1158223670',);

$templ_data[] = array('name' => 'importexport.export_dialog.general_tab_content','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:3:{s:2:"c2";s:14:"select_appname";s:2:"c3";s:17:"select_definition";s:2:"c4";s:13:"select_plugin";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:14:"some nice text";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:18:"Select application";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:7:"appname";s:8:"onchange";s:73:"xajax_doXMLHTTP(\'importexport.uiexport.ajax_get_definitions\',this.value);";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"Select definition";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:10:"definition";s:8:"onchange";s:52:"export_dialog.change_definition(this); return false;";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Select plugin";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:8:"onchange";s:80:"xajax_doXMLHTTP(\'importexport.uiexport.ajax_get_definition_options\',this.value);";s:7:"no_lang";s:1:"1";s:4:"name";s:6:"plugin";}}i:5;a:2:{s:1:"A";a:6:{s:4:"type";s:3:"box";s:4:"span";s:3:"all";s:4:"name";s:18:"plugin_description";s:6:"needed";s:1:"1";s:4:"size";s:1:"1";i:1;a:7:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:6:"needed";s:1:"1";i:1;a:4:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:6:"needed";s:1:"1";s:5:"label";s:11:"Description";}i:2;a:2:{s:4:"type";s:5:"label";s:4:"name";s:18:"plugin_description";}s:4:"name";s:11:"description";s:7:"no_lang";s:1:"1";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:5;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1158223021',);

$templ_data[] = array('name' => 'importexport.export_dialog.options_tab','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"name";s:19:"plugin_options_html";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:6:",200px";s:7:"options";a:1:{i:1;s:5:"200px";}}}','size' => ',200px','style' => '','modified' => '1158223824',);

$templ_data[] = array('name' => 'importexport.export_dialog.selection_tab','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"name";s:21:"plugin_selectors_html";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:6:",200px";s:7:"options";a:1:{i:1;s:5:"200px";}}}','size' => ',200px','style' => '','modified' => '1158223796',);

$templ_data[] = array('name' => 'importexport.import_definition','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:92:"Import definitions (Attension: Existing definitions with equal names will be overwritten!!!)";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:4:"file";s:4:"name";s:11:"import_file";}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Import";s:4:"name";s:6:"import";}}}s:4:"rows";i:3;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1150533844',);

$templ_data[] = array('name' => 'importexport.import_dialog','template' => '','lang' => '','group' => '0','version' => '0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:1:{s:2:"h1";s:10:",!@message";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"Application";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:7:"appname";s:8:"onchange";s:87:"xajax_doXMLHTTP(\'importexport.importexport_import_ui.ajax_get_definitions\',this.value);";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:28:"Please select file to import";}s:1:"B";a:3:{s:4:"type";s:4:"file";s:4:"name";s:4:"file";s:8:"onchange";s:142:"xajax_doXMLHTTP(\'importexport.importexport_import_ui.ajax_get_definitions\', document.getElementById(form::name(\'appname\')).value, this.value);";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"Import definition";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:10:"definition";}}i:5;a:2:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:6;a:2:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";s:4:"span";s:3:"all";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Import";s:4:"name";s:6:"import";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:6:"cancel";s:5:"label";s:6:"Cancel";}i:3;a:4:{s:4:"type";s:8:"checkbox";s:4:"name";s:7:"dry-run";s:5:"label";s:9:"Test only";s:5:"align";s:5:"right";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:6;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1266439013',);

$templ_data[] = array('name' => 'importexport.schedule_edit','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:9:{i:0;a:1:{s:2:"h1";s:10:",!@message";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:7:"message";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";}s:1:"B";a:2:{s:4:"type";s:6:"select";s:4:"name";s:4:"type";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"Application";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:7:"appname";s:8:"onchange";s:137:"xajax_doXMLHTTP(\'importexport.importexport_schedule_ui.ajax_get_plugins\', document.getElementById(form::name(\'type\')).value, this.value);";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Plugin";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:6:"plugin";s:4:"size";s:9:"Select...";s:8:"onchange";s:144:"xajax_doXMLHTTP(\'importexport.importexport_schedule_ui.ajax_get_definitions\', document.getElementById(form::name(\'appname\')).value, this.value);";}}i:5;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"Definition";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:10:"definition";s:4:"size";s:9:"Select...";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Target";}s:1:"B";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"50";s:4:"name";s:6:"target";}}i:7;a:2:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"c1";s:2:"th";}i:1;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Year";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Month";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:3:"Day";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"Day of week";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Hour";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Minute";}}i:2;a:6:{s:1:"A";a:3:{s:4:"type";s:4:"text";s:4:"name";s:4:"year";s:4:"size";s:1:"5";}s:1:"B";a:3:{s:4:"type";s:4:"text";s:4:"name";s:5:"month";s:4:"size";s:1:"5";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:3:"day";s:4:"size";s:1:"5";}s:1:"D";a:4:{s:4:"type";s:4:"text";s:4:"name";s:3:"dow";s:5:"align";s:6:"center";s:4:"size";s:1:"5";}s:1:"E";a:3:{s:4:"type";s:4:"text";s:4:"name";s:4:"hour";s:4:"size";s:1:"5";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:3:"min";s:4:"size";s:1:"5";}}i:3;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:1:"3";s:5:"label";s:11:"(* for all)";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"(0-6, 0=Sun)";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"(0-23)";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:3;s:4:"cols";i:6;s:4:"name";s:8:"schedule";s:4:"span";s:3:"all";s:7:"options";a:0:{}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:8;a:2:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:3:"all";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:4:"save";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:6:"cancel";s:5:"label";s:6:"Cancel";s:7:"onclick";s:13:"self.close();";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:8;s:4:"cols";i:2;}}','size' => '','style' => '','modified' => '1267036378',);

$templ_data[] = array('name' => 'importexport.schedule_index','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:11:"all,message";s:4:"name";s:4:"@msg";}}i:2;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"name";s:9:"scheduled";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:4:",top";}i:1;a:8:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Type";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"Application";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Plugin";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"Definition";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Target";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Last Run";}s:1:"G";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:6:"2,,0,0";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Next Run";}i:2;a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"Schedule";}}s:1:"H";a:1:{s:4:"type";s:5:"label";}}i:2;a:8:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[type]";}s:1:"B";a:3:{s:4:"type";s:10:"select-app";s:4:"name";s:15:"${row}[appname]";s:8:"readonly";s:1:"1";}s:1:"C";a:4:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:14:"${row}[plugin]";s:8:"readonly";s:1:"1";}s:1:"D";a:3:{s:4:"type";s:6:"select";s:4:"name";s:18:"${row}[definition]";s:8:"readonly";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:14:"${row}[target]";}s:1:"F";a:6:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"4";i:1;a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:16:"${row}[last_run]";s:8:"readonly";s:1:"1";}i:2;a:3:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[record_count]";s:7:"no_lang";s:1:"1";}i:3;a:3:{s:4:"type";s:5:"label";s:4:"name";s:14:"${row}[result]";s:7:"no_lang";s:1:"1";}i:4;a:2:{s:4:"type";s:5:"label";s:4:"name";s:14:"${row}[errors]";}}s:1:"G";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:6:"2,,0,0";i:1;a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:12:"${row}[next]";s:8:"readonly";s:1:"1";}i:2;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:13:"${row}[times]";}}s:1:"H";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:6:"2,,0,0";i:1;a:4:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:4:"name";s:21:"edit[${row_cont[id]}]";s:7:"onclick";s:198:"window.open(egw::link(\'/index.php\',\'menuaction=importexport.importexport_schedule_ui.edit&id={$row_cont[id]}\'),\'_blank\',\'dependent=yes,width=600,height=450,scrollbars=yes,status=yes\'); return false;";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:4:"name";s:23:"delete[{$row_cont[id]}]";}}}}s:4:"rows";i:2;s:4:"cols";i:8;}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";s:4:"span";s:3:"all";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:3:"add";s:4:"name";s:3:"add";s:7:"onclick";s:179:"window.open(egw::link(\'/index.php\',\'menuaction=importexport.importexport_schedule_ui.edit\'),\'_blank\',\'dependent=yes,width=600,height=450,scrollbars=yes,status=yes\'); return false;";}}}}s:4:"rows";i:3;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1267034207',);

$templ_data[] = array('name' => 'importexport.wizzardbox','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c2";s:7:",bottom";s:2:"c1";s:4:",top";s:1:"A";s:4:"100%";}i:1;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:7:"no_lang";s:1:"1";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"hbox";s:7:"no_lang";s:1:"1";s:4:"size";s:1:"1";i:1;a:3:{s:4:"type";s:5:"image";s:7:"no_lang";s:1:"1";s:4:"name";s:12:"importexport";}}i:2;a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";i:1;a:2:{s:4:"type";s:8:"template";s:4:"name";s:15:"wizzard_content";}s:4:"span";s:16:",wizzard_content";}}}i:2;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:3:"all";i:1;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:4:{s:4:"type";s:6:"button";s:4:"name";s:16:"button[previous]";s:5:"label";s:8:"previous";s:7:"onclick";s:37:"xajax_eT_wrapper(this); return false;";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:12:"button[next]";s:5:"label";s:4:"next";s:7:"onclick";s:37:"xajax_eT_wrapper(this); return false;";}i:3;a:4:{s:4:"type";s:6:"button";s:4:"name";s:14:"button[finish]";s:5:"label";s:6:"finish";s:7:"onclick";s:37:"xajax_eT_wrapper(this); return false;";}}i:2;a:5:{s:4:"type";s:6:"button";s:4:"name";s:14:"button[cancel]";s:5:"label";s:6:"cancel";s:7:"onclick";s:29:"window.close(); return false;";s:5:"align";s:5:"right";}s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:4:",400";s:7:"options";a:1:{i:1;s:3:"400";}}}','size' => ',400','style' => '.wizzard_content fieldset {
   height: 347px;
   width: 250px;
   max-height:347px;
   overflow:auto;
}','modified' => '1145975378',);

$templ_data[] = array('name' => 'importexport.wizzard_chooseallowedusers','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:13:"allowed_users";s:4:"size";s:8:"5,groups";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1146312041',);

$templ_data[] = array('name' => 'importexport.wizzard_chooseapp','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:11:"application";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1145976439',);

$templ_data[] = array('name' => 'importexport.wizzard_choosename','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:4:"text";s:4:"name";s:4:"name";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1146317310',);

$templ_data[] = array('name' => 'importexport.wizzard_chooseplugin','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:6:"select";s:7:"no_lang";s:1:"1";s:4:"name";s:6:"plugin";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1145976573',);

$templ_data[] = array('name' => 'importexport.wizzard_close','template' => '','lang' => '','group' => '0','version' => '0.0.1','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}}s:4:"rows";i:1;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1146648383',);

