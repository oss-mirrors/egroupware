<?php
/**
	* eGroupWare - eTemplates for Application timesheet
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2010-12-10 10:59
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package timesheet
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'timesheet.customfields','template' => '','lang' => '','group' => '0','version' => '1.2.002','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:1:"F";s:3:"80%";s:2:"c2";s:6:"header";}i:1;a:6:{s:1:"A";a:4:{s:4:"type";s:8:"template";s:4:"size";s:6:"status";s:4:"span";s:3:"all";s:4:"name";s:6:"status";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:2;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:13:"Custom fields";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:3;a:6:{s:1:"A";a:4:{s:4:"type";s:8:"template";s:4:"size";s:6:"fields";s:4:"span";s:3:"all";s:4:"name";s:6:"fields";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:4;a:6:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";s:4:"span";s:3:"all";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";s:4:"help";s:33:"saves the changes made and leaves";}i:2;a:4:{s:4:"type";s:6:"button";s:5:"label";s:5:"Apply";s:4:"name";s:13:"button[apply]";s:4:"help";s:19:"applies the changes";}i:3;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:4:"help";s:22:"leaves without saveing";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:4;s:4:"cols";i:6;}}','size' => '','style' => '.header { font-weight: bold; font-size: 120%; }
.error_msg { color: red; font-style: italics; }','modified' => '1163162665',);

$templ_data[] = array('name' => 'timesheet.customfields.fields','template' => '','lang' => '','group' => '0','version' => '1.2.004','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:7:"row,top";}i:1;a:7:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Name";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Label";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"type";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"Values for selectbox";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"Length<br>Rows";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Order";}s:1:"G";a:4:{s:4:"type";s:5:"label";s:5:"label";s:6:"Action";s:5:"align";s:6:"center";s:4:"help";s:18:"deletes this field";}}i:2;a:7:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:4:"size";s:5:"20,32";s:4:"name";s:12:"${row}[name]";s:4:"help";s:83:"the name used internaly (<= 20 chars), changeing it makes existing data unavailible";}s:1:"B";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"text";s:4:"size";s:4:",255";s:4:"name";s:13:"${row}[label]";s:4:"help";s:30:"the text displayed to the user";}i:2;a:2:{s:4:"type";s:5:"label";s:4:"name";s:13:"${row}[label]";}}s:1:"C";a:2:{s:4:"type";s:6:"select";s:4:"name";s:12:"${row}[type]";}s:1:"D";a:4:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"2,30";s:4:"name";s:14:"${row}[values]";s:4:"help";s:40:"each value is a line like <id>[=<label>]";}s:1:"E";a:5:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"3";i:1;a:4:{s:4:"type";s:4:"text";s:4:"size";s:1:"5";s:4:"name";s:11:"${row}[len]";s:4:"help";s:63:"max length of the input [, length of the inputfield (optional)]";}i:2;a:4:{s:4:"type";s:3:"int";s:4:"name";s:12:"${row}[rows]";s:4:"size";s:6:"0,10,2";s:4:"blur";s:1:"1";}i:3;a:6:{s:4:"type";s:3:"int";s:4:"size";s:6:"0,10,2";s:4:"name";s:12:"${row}[rows]";s:4:"help";s:70:"number of row for a multiline inputfield or line of a multi-select-box";s:4:"blur";s:1:"1";s:8:"disabled";s:1:"1";}}s:1:"F";a:4:{s:4:"type";s:3:"int";s:4:"size";s:4:"1,,3";s:4:"name";s:13:"${row}[order]";s:4:"help";s:45:"determines the order the fields are displayed";}s:1:"G";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:4:"name";s:23:"delete[$row_cont[name]]";s:4:"help";s:18:"deletes this field";}i:2;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Create";s:4:"name";s:21:"create$row_cont[name]";s:4:"help";s:19:"creates a new field";}}}}s:4:"rows";i:2;s:4:"cols";i:7;}}','size' => '','style' => '','modified' => '1164631589',);

$templ_data[] = array('name' => 'timesheet.customstatus','template' => '','lang' => '','group' => '0','version' => '1.7.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:3:{s:1:"D";s:3:"30%";s:1:"A";s:4:"100%";s:2:"h1";s:6:",!@msg";}i:1;a:4:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"name";s:3:"msg";s:4:"span";s:13:"all,redItalic";s:5:"align";s:6:"center";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:6:{s:4:"type";s:8:"groupbox";s:4:"data";a:2:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:1:"1";i:1;a:3:{s:4:"type";s:8:"template";s:7:"no_lang";s:1:"1";s:4:"name";s:27:"timesheet.customstatus.cats";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:3;a:4:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";s:4:"span";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";}i:3;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:3;s:4:"cols";i:4;s:4:"size";s:17:"100%,450,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"450";i:6;s:4:"auto";}}}','size' => '100%,450,,,,,auto','style' => '','modified' => '1237891752',);

$templ_data[] = array('name' => 'timesheet.customstatus.cats','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:3:{s:1:"D";s:3:"30%";s:2:"c1";s:7:"row,top";s:1:"A";s:3:"100";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Status";}s:1:"B";a:8:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,280,,,,,auto";s:4:"span";s:3:"all";s:4:"name";s:6:"statis";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:1:"B";s:2:"5%";s:2:"c2";s:3:"row";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Name";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";}}i:2;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:4:"size";s:6:"80,150";s:4:"blur";s:18:"--> enter new name";s:4:"name";s:12:"${row}[name]";}s:1:"B";a:7:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:5:"align";s:6:"center";s:4:"name";s:21:"delete[$row_cont[id]]";s:4:"help";s:18:"Delete this status";s:7:"onclick";s:37:"return confirm(\'Delete this status\');";}}}s:4:"rows";i:2;s:4:"cols";i:2;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"280";i:6;s:4:"auto";}}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:4;s:4:"size";s:17:"100%,300,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"300";i:6;s:4:"auto";}}}','size' => '100%,300,,,,,auto','style' => '','modified' => '1236867741',);

$templ_data[] = array('name' => 'timesheet.edit','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:8:{i:0;a:8:{s:2:"c2";s:2:"th";s:2:"c3";s:3:"row";s:1:"A";s:3:"100";s:2:"h6";s:14:",!@ts_modified";s:2:"c4";s:3:"row";s:2:"h2";s:2:"28";s:2:"h1";s:6:",!@msg";s:2:"h4";s:13:",@ts_viewtype";}i:1;a:2:{s:1:"A";a:5:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";s:7:"no_lang";s:1:"1";s:5:"align";s:6:"center";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:11:",,,ts_owner";s:5:"label";s:4:"User";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:8:"ts_owner";s:4:"span";s:3:"all";s:7:"no_lang";s:1:"1";}}i:3;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:13:",,,ts_project";s:5:"label";s:7:"Project";}s:1:"B";a:7:{s:4:"type";s:4:"grid";s:4:"size";s:7:",,,,1,1";s:4:"span";s:3:"all";s:4:"data";a:3:{i:0;a:2:{s:2:"h1";s:21:",@pm_integration=none";s:2:"h2";s:21:",@pm_integration=full";}i:1;a:1:{s:1:"A";a:6:{s:4:"type";s:21:"projectmanager-select";s:4:"size";s:4:"None";s:4:"name";s:5:"pm_id";s:4:"span";s:13:"all,fullWidth";s:4:"help";s:16:"Select a project";s:8:"onchange";i:1;}}i:2;a:1:{s:1:"A";a:5:{s:4:"type";s:4:"text";s:4:"name";s:10:"ts_project";s:4:"blur";s:16:"@ts_project_blur";s:4:"size";s:5:"65,80";s:4:"span";s:10:",fullWidth";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:7:"options";a:2:{i:4;s:1:"1";i:5;s:1:"1";}}}i:4;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:14:",,ts_unitprice";s:5:"label";s:9:"Unitprice";}s:1:"B";a:5:{s:4:"type";s:4:"grid";s:4:"span";s:3:"all";s:4:"data";a:2:{i:0;a:1:{s:1:"A";s:21:",@pm_integration=none";}i:1;a:2:{s:1:"A";a:4:{s:4:"type";s:24:"projectmanager-pricelist";s:4:"name";s:5:"pl_id";s:4:"size";s:4:"None";s:8:"onchange";s:209:"this.form[\'exec[ts_unitprice]\'].value=this.options[this.selectedIndex].text.lastIndexOf(\'(\') < 0 ? \'\' : this.options[this.selectedIndex].text.slice(this.options[this.selectedIndex].text.lastIndexOf(\'(\')+1,-1);";}s:1:"B";a:3:{s:4:"type";s:5:"float";s:4:"name";s:12:"ts_unitprice";s:4:"span";s:3:"all";}}}s:4:"rows";i:1;s:4:"cols";i:2;}}i:5;a:2:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:41:"General|Notes|Links|Custom Fields|History";s:4:"name";s:45:"tabs=general|notes|links|customfields|history";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:6;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Last modified";}s:1:"B";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:11:"ts_modified";s:8:"readonly";s:1:"1";}i:2;a:4:{s:4:"type";s:14:"select-account";s:4:"name";s:11:"ts_modifier";s:5:"label";s:2:"by";s:8:"readonly";s:1:"1";}}}i:7;a:2:{s:1:"A";a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:5:"2,0,0";s:4:"span";s:1:"2";i:1;a:8:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"6";i:1;a:4:{s:4:"type";s:6:"button";s:5:"label";s:4:"Edit";s:4:"name";s:12:"button[edit]";s:4:"help";s:15:"Edit this entry";}i:2;a:4:{s:4:"type";s:6:"button";s:4:"name";s:16:"button[save_new]";s:5:"label";s:10:"Save & New";s:4:"help";s:34:"Saves this entry and add a new one";}i:3;a:4:{s:4:"type";s:6:"button";s:4:"name";s:12:"button[save]";s:5:"label";s:4:"Save";s:4:"help";s:22:"Saves the changes made";}i:4;a:4:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";s:4:"help";s:24:"Applies the changes made";}i:5;a:5:{s:4:"type";s:6:"button";s:4:"name";s:14:"button[cancel]";s:5:"label";s:6:"Cancel";s:4:"help";s:44:"closes the window without saving the changes";s:7:"onclick";s:15:"window.close();";}i:6;a:2:{s:4:"type";s:4:"html";s:4:"name";s:2:"js";}}i:2;a:6:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:5:"align";s:5:"right";s:4:"name";s:14:"button[delete]";s:4:"help";s:17:"Delete this entry";s:7:"onclick";s:36:"return confirm(\'Delete this entry\');";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:7;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '.fullWidth select { widht: 100%; }
.fullWidth input { widht: 100%; }
.fullWidth textarea { widht: 100%; }','modified' => '1253795098',);

$templ_data[] = array('name' => 'timesheet.edit.customfields','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:12:"customfields";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,150,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"150";i:6;s:4:"auto";}}}','size' => '100%,150,,,,,auto','style' => '','modified' => '1163173930',);

$templ_data[] = array('name' => 'timesheet.edit.general','template' => '','lang' => '','group' => '0','version' => '1.7.002','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:8:{i:0;a:15:{s:2:"c1";s:3:"row";s:1:"A";s:2:"95";s:2:"c3";s:3:"row";s:2:"c4";s:3:"row";s:2:"c5";s:3:"row";s:1:"B";s:3:"120";s:1:"C";s:15:"80,@ts_viewtype";s:1:"D";s:13:",@ts_viewtype";s:2:"c2";s:3:"row";s:2:"h2";s:14:",!@ts_viewtype";s:2:"h1";s:13:",@ts_viewtype";s:2:"c6";s:3:"row";s:2:"h6";s:13:",@ts_viewtype";s:2:"c7";s:3:"row";s:2:"h7";s:14:",@no_ts_status";}i:1;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:11:",,,ts_title";s:5:"label";s:5:"Title";}s:1:"B";a:5:{s:4:"type";s:4:"text";s:4:"size";s:5:"65,80";s:4:"name";s:8:"ts_title";s:4:"blur";s:14:"@ts_title_blur";s:4:"span";s:13:"all,fullWidth";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"comment";}s:1:"B";a:4:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"5,50";s:4:"name";s:14:"ts_description";s:4:"span";s:13:"all,fullWidth";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:3;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:9:",,,cat_id";s:5:"label";s:8:"Category";}s:1:"B";a:4:{s:4:"type";s:10:"select-cat";s:4:"name";s:6:"cat_id";s:4:"size";s:4:"None";s:4:"span";s:3:"all";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:4;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:11:",,,ts_start";s:5:"label";s:4:"Date";}s:1:"B";a:4:{s:4:"type";s:4:"date";s:4:"name";s:8:"ts_start";s:6:"needed";s:1:"1";s:4:"size";s:2:",8";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"Starttime";}s:1:"D";a:3:{s:4:"type";s:13:"date-timeonly";s:4:"name";s:10:"start_time";s:4:"size";s:3:"H:i";}}i:5;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:14:",,,ts_duration";s:5:"label";s:8:"Duration";}s:1:"B";a:3:{s:4:"type";s:13:"date-duration";s:4:"name";s:11:"ts_duration";s:4:"size";s:3:",hm";}s:1:"C";a:3:{s:4:"type";s:5:"label";s:5:"label";s:10:"or endtime";s:4:"span";s:7:",noWrap";}s:1:"D";a:3:{s:4:"type";s:13:"date-timeonly";s:4:"name";s:8:"end_time";s:4:"size";s:3:"H:i";}}i:6;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"size";s:14:",,,ts_quantity";s:5:"label";s:8:"Quantity";}s:1:"B";a:6:{s:4:"type";s:5:"float";s:4:"name";s:11:"ts_quantity";s:4:"help";s:30:"empty if identical to duration";s:4:"blur";s:17:"@ts_quantity_blur";s:4:"size";s:4:",,,3";s:4:"span";s:3:"all";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:7;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Status";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:9:"ts_status";s:4:"help";s:32:"select a status of the timesheet";s:4:"size";s:13:"please select";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:7;s:4:"cols";i:4;s:4:"size";s:8:"100%,150";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:3:"150";}}}','size' => '100%,150','style' => '','modified' => '1236615245',);

$templ_data[] = array('name' => 'timesheet.edit.history','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:4:",top";}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:10:"historylog";s:4:"name";s:7:"history";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,150,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"150";i:6;s:4:"auto";}}}','size' => '100%,150,,,,,auto','style' => '','modified' => '1237823283',);

$templ_data[] = array('name' => 'timesheet.edit.links','template' => '','lang' => '','group' => '0','version' => '0.1.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:7:{s:1:"A";s:3:"100";s:2:"h1";s:6:",@view";s:2:"h2";s:13:",@status_only";s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:2:"c3";s:2:"th";s:2:"c4";s:11:"row_off,top";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:16:"Create new links";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:7:"link-to";s:4:"span";s:3:"all";s:4:"name";s:7:"link_to";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:3:"all";s:5:"label";s:14:"Existing links";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:3:{s:4:"type";s:9:"link-list";s:4:"span";s:3:"all";s:4:"name";s:7:"link_to";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:4;s:4:"cols";i:2;s:4:"size";s:17:"100%,150,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"150";i:6;s:4:"auto";}}}','size' => '100%,150,,,,,auto','style' => '','modified' => '1134775301',);

$templ_data[] = array('name' => 'timesheet.edit.notes','template' => '','lang' => '','group' => '0','version' => '1.5.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:1:{s:2:"c1";s:7:"row,top";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"8,70";s:4:"name";s:14:"ts_description";s:4:"span";s:10:",fullWidth";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:8:"100%,150";s:7:"options";a:2:{i:0;s:4:"100%";i:1;s:3:"150";}}}','size' => '100%,150','style' => '','modified' => '1134773787',);

$templ_data[] = array('name' => 'timesheet.editstatus','template' => '','lang' => '','group' => '0','version' => '1.7.004','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:3:{s:1:"D";s:3:"30%";s:1:"A";s:3:"100";s:2:"h1";s:6:",!@msg";}i:1;a:4:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"name";s:3:"msg";s:4:"span";s:13:"all,redItalic";s:5:"align";s:6:"center";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:6:{s:4:"type";s:8:"groupbox";s:4:"data";a:2:{i:0;a:1:{s:2:"h1";s:6:",!@msg";}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:1:"1";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:3:{s:1:"D";s:3:"30%";s:2:"c1";s:7:"row,top";s:1:"A";s:3:"100";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Status";}s:1:"B";a:8:{s:4:"type";s:4:"grid";s:4:"size";s:17:"100%,280,,,,,auto";s:4:"span";s:3:"all";s:4:"name";s:6:"statis";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"E";s:2:"5%";}i:1;a:5:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:2:"ID";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Name";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Parent";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"Only Admin";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";}}i:2;a:5:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:4:"name";s:10:"${row}[id]";}s:1:"B";a:4:{s:4:"type";s:4:"text";s:4:"size";s:6:"80,150";s:4:"blur";s:18:"--> enter new name";s:4:"name";s:12:"${row}[name]";}s:1:"C";a:3:{s:4:"type";s:6:"select";s:4:"name";s:14:"${row}[parent]";s:4:"size";s:13:"please select";}s:1:"D";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:13:"${row}[admin]";s:4:"help";s:33:"Only Admin can change this Status";}s:1:"E";a:7:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:5:"align";s:6:"center";s:4:"name";s:21:"delete[$row_cont[id]]";s:4:"help";s:18:"Delete this status";s:7:"onclick";s:37:"return confirm(\'Delete this status\');";}}}s:4:"rows";i:2;s:4:"cols";i:5;s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"280";i:6;s:4:"auto";}}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:4;s:4:"size";s:17:"100%,300,,,,,auto";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:3;a:4:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";s:4:"span";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:2;a:3:{s:4:"type";s:6:"button";s:4:"name";s:13:"button[apply]";s:5:"label";s:5:"Apply";}i:3;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:3;s:4:"cols";i:4;s:4:"size";s:17:"100%,450,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"450";i:6;s:4:"auto";}}}','size' => '100%,450,,,,,auto','style' => '','modified' => '1252352154',);

$templ_data[] = array('name' => 'timesheet.index','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:6:{i:0;a:4:{s:2:"h1";s:6:",!@msg";s:2:"h2";s:2:",1";s:2:"c4";s:7:"noPrint";s:2:"h5";s:2:",1";}i:1;a:2:{s:1:"A";a:5:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:7:"no_lang";s:1:"1";s:4:"name";s:3:"msg";s:5:"align";s:6:"center";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:8:"template";s:4:"name";s:5:"dates";}i:2;a:3:{s:4:"type";s:8:"template";s:4:"name";s:3:"add";s:5:"align";s:5:"right";}}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:3;a:2:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"name";s:2:"nm";s:4:"size";s:20:"timesheet.index.rows";s:4:"span";s:3:"all";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:4;a:2:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:3:"Add";s:4:"name";s:3:"add";s:7:"onclick";s:164:"window.open(egw::link(\'/index.php\',\'menuaction=timesheet.timesheet_ui.edit\'),\'_blank\',\'dependent=yes,width=600,height=400,scrollbars=yes,status=yes\'); return false;";}s:1:"B";a:7:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";s:5:"align";s:5:"right";i:1;a:5:{s:4:"type";s:8:"checkbox";s:4:"name";s:7:"use_all";s:5:"label";s:11:"whole query";s:8:"onchange";s:128:"if (this.checked==true && !confirm(\'Apply the action on the whole query, NOT only the shown timesheets!!!\')) this.checked=false;";s:4:"help";s:69:"Apply the action on the whole query, NOT only the shown timesheets!!!";}i:2;a:5:{s:4:"type";s:3:"box";s:4:"name";s:9:"cat_popup";s:4:"size";s:1:"1";s:4:"span";s:20:",action_popup prompt";i:1;a:5:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"3";i:1;a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:",promptheader";s:5:"label";s:15:"Change category";}i:2;a:5:{s:4:"type";s:10:"select-cat";s:4:"span";s:21:",action_popup-content";s:4:"name";s:3:"cat";s:4:"size";s:16:"None,,,timesheet";s:5:"label";s:19:"Select new category";}i:3;a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:5:"Apply";s:4:"name";s:10:"change_cat";}i:2;a:3:{s:4:"type";s:10:"buttononly";s:5:"label";s:6:"Cancel";s:7:"onclick";s:29:"hide_popup(this,\'cat_popup\');";}}}}i:3;a:5:{s:4:"type";s:6:"select";s:8:"onchange";s:16:"do_action(this);";s:4:"size";s:13:"Select action";s:4:"name";s:6:"action";s:4:"help";s:13:"Select action";}i:4;a:8:{s:4:"type";s:6:"button";s:4:"size";s:9:"arrow_ltr";s:5:"label";s:9:"Check all";s:4:"name";s:9:"check_all";s:4:"help";s:9:"Check all";s:7:"onclick";s:70:"toggle_all(this.form,form::name(\'nm[rows][checked][]\')); return false;";s:6:"needed";s:1:"1";s:4:"span";s:14:",checkAllArrow";}}}i:5;a:2:{s:1:"A";a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Export";s:7:"onclick";s:33:"timesheet_export(); return false;";s:4:"name";s:6:"export";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:5;s:4:"cols";i:2;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '/**
 * Add / remove link or category popup used for actions on multiple entries
 */

.action_popup {
	position: fixed;
	top: 200px;
	left: 450px;
	width: 76ex;
	z-index: 20000;
	display: none;
	border-collapse:collapse;
	border-spacing:0px
}
.action_popup-content {
	display:block;
	padding:2ex;
	color:#666666;
	margin: -2px -1px 0px -2px;
}
.action_popup > table {
	width: 100%
}

','modified' => '1291996935',);

$templ_data[] = array('name' => 'timesheet.index.add','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:10:"buttononly";s:5:"label";s:3:"Add";s:4:"name";s:3:"add";s:7:"onclick";s:164:"window.open(egw::link(\'/index.php\',\'menuaction=timesheet.timesheet_ui.edit\'),\'_blank\',\'dependent=yes,width=600,height=400,scrollbars=yes,status=yes\'); return false;";}}','size' => '','style' => '','modified' => '1158042543',);

$templ_data[] = array('name' => 'timesheet.index.dates','template' => '','lang' => '','group' => '0','version' => '1.2.001','data' => 'a:1:{i:0;a:10:{s:4:"type";s:4:"hbox";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:1:"4";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Start";}i:2;a:2:{s:4:"type";s:4:"date";s:4:"name";s:9:"startdate";}i:3;a:2:{s:4:"type";s:5:"label";s:5:"label";s:3:"End";}i:4;a:3:{s:4:"type";s:4:"date";s:4:"name";s:7:"enddate";s:4:"help";s:30:"Leave it empty for a full week";}s:4:"span";s:12:",custom_hide";}}','size' => '','style' => '.custom_hide { visibility: hidden; }','modified' => '1142973260',);

$templ_data[] = array('name' => 'timesheet.index.rows','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:9:{s:2:"c1";s:2:"th";s:2:"c2";s:16:"$row_cont[class]";s:1:"A";s:3:"15%";s:1:"B";s:3:"50%";s:1:"H";s:14:",@no_owner_col";s:1:"G";s:13:",@no_ts_total";s:1:"F";s:17:",@no_ts_unitprice";s:1:"E";s:16:",@no_ts_quantity";s:1:"I";s:14:",@no_ts_status";}i:1;a:11:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"Date";s:4:"name";s:8:"ts_start";}s:1:"B";a:7:{s:4:"type";s:4:"grid";s:7:"no_lang";s:1:"1";s:4:"data";a:5:{i:0;a:2:{s:2:"h1";s:21:",@pm_integration=full";s:2:"h2";s:22:",!@pm_integration=full";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:22:"nextmatch-filterheader";s:4:"size";s:12:"All projects";s:4:"name";s:10:"ts_project";s:7:"no_lang";s:1:"1";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:22:"nextmatch-customfilter";s:4:"size";s:34:"projectmanager-select,All projects";s:4:"name";s:5:"pm_id";s:8:"onchange";i:1;}}i:3;a:1:{s:1:"A";a:6:{s:4:"type";s:22:"nextmatch-customfilter";s:4:"size";s:10:"link-entry";i:1;a:5:{s:4:"type";s:10:"link-entry";s:4:"size";s:15:"infolog,infolog";s:4:"name";s:11:"nm[info_id]";s:8:"onchange";i:1;s:4:"blur";s:14:"select Infolog";}i:2;a:2:{s:4:"type";s:6:"button";s:5:"label";s:6:"submit";}s:8:"onchange";i:1;s:4:"name";s:6:"linked";}}i:4;a:1:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:5:"Title";s:4:"name";s:8:"ts_title";}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:7:",,,,0,0";s:7:"options";a:2:{i:4;s:1:"0";i:5;s:1:"0";}}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Category";s:4:"name";s:6:"cat_id";}s:1:"D";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Duration";s:4:"name";s:11:"ts_duration";}i:2;a:4:{s:4:"type";s:13:"date-duration";s:4:"name";s:8:"duration";s:4:"size";s:6:",h,,,1";s:8:"readonly";s:1:"1";}}s:1:"E";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:6:"2,,0,0";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:8:"Quantity";s:4:"name";s:11:"ts_quantity";}i:2;a:4:{s:4:"type";s:5:"float";s:4:"name";s:8:"quantity";s:8:"readonly";s:1:"1";s:4:"size";s:4:",,,3";}}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:5:"Price";s:4:"name";s:12:"ts_unitprice";}s:1:"G";a:4:{s:4:"type";s:4:"vbox";s:4:"size";s:1:"2";i:1;a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:5:"Total";s:4:"name";s:8:"ts_total";}i:2;a:4:{s:4:"type";s:5:"float";s:4:"name";s:5:"price";s:8:"readonly";s:1:"1";s:4:"size";s:4:",,,2";}}s:1:"H";a:5:{s:4:"type";s:22:"nextmatch-filterheader";s:4:"name";s:8:"ts_owner";s:4:"size";s:4:"User";s:7:"no_lang";s:1:"1";s:4:"span";s:18:",$cont[ownerClass]";}s:1:"I";a:4:{s:4:"type";s:22:"nextmatch-filterheader";s:4:"name";s:9:"ts_status";s:8:"onchange";i:1;s:4:"size";s:10:"All status";}s:1:"J";a:2:{s:4:"type";s:22:"nextmatch-customfields";s:4:"name";s:12:"customfields";}s:1:"K";a:5:{s:4:"type";s:4:"hbox";s:4:"span";s:8:",noPrint";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:5:"label";s:5:"label";s:7:"Actions";s:4:"span";s:8:",noPrint";s:5:"align";s:5:"right";}i:2;a:7:{s:4:"type";s:6:"button";s:5:"label";s:9:"Check all";s:4:"size";s:5:"check";s:4:"name";s:9:"check_all";s:6:"needed";s:1:"1";s:4:"help";s:9:"Check all";s:7:"onclick";s:60:"toggle_all(this.form,form::name(\'checked[]\')); return false;";}}}i:2;a:11:{s:1:"A";a:4:{s:4:"type";s:9:"date-time";s:4:"name";s:16:"${row}[ts_start]";s:8:"readonly";s:1:"1";s:4:"size";s:2:",8";}s:1:"B";a:5:{s:4:"type";s:4:"vbox";s:4:"size";s:6:"3,,0,0";i:1;a:3:{s:4:"type";s:4:"link";s:4:"name";s:15:"${row}[ts_link]";s:7:"no_lang";s:1:"1";}i:2;a:4:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[ts_title]";s:7:"no_lang";s:1:"1";s:4:"span";s:22:",$row_cont[titleClass]";}i:3;a:3:{s:4:"type";s:5:"label";s:4:"name";s:22:"${row}[ts_description]";s:7:"no_lang";s:1:"1";}}s:1:"C";a:4:{s:4:"type";s:10:"select-cat";s:8:"readonly";s:1:"1";s:4:"name";s:14:"${row}[cat_id]";s:4:"span";s:7:",noWrap";}s:1:"D";a:4:{s:4:"type";s:13:"date-duration";s:4:"name";s:19:"${row}[ts_duration]";s:8:"readonly";s:1:"1";s:4:"size";s:6:",h,,,1";}s:1:"E";a:5:{s:4:"type";s:5:"float";s:4:"name";s:19:"${row}[ts_quantity]";s:7:"no_lang";s:1:"1";s:4:"size";s:4:",,,3";s:8:"readonly";s:1:"1";}s:1:"F";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:20:"${row}[ts_unitprice]";}s:1:"G";a:5:{s:4:"type";s:5:"float";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[ts_total]";s:4:"size";s:4:",,,2";s:8:"readonly";s:1:"1";}s:1:"H";a:4:{s:4:"type";s:14:"select-account";s:4:"name";s:16:"${row}[ts_owner]";s:8:"readonly";s:1:"1";s:4:"span";s:18:",$cont[ownerClass]";}s:1:"I";a:3:{s:4:"type";s:6:"select";s:4:"name";s:17:"${row}[ts_status]";s:8:"readonly";s:1:"1";}s:1:"J";a:3:{s:4:"type";s:17:"customfields-list";s:4:"name";s:4:"$row";s:8:"readonly";s:1:"1";}s:1:"K";a:8:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";i:1;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"view";s:5:"label";s:4:"View";s:4:"name";s:22:"view[$row_cont[ts_id]]";s:7:"onclick";s:187:"window.open(egw::link(\'/index.php\',\'menuaction=timesheet.timesheet_ui.view&ts_id=$row_cont[ts_id]\'),\'_blank\',\'dependent=yes,width=600,height=400,scrollbars=yes,status=yes\'); return false;";s:4:"help";s:15:"View this entry";}i:2;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:4:"Edit";s:4:"name";s:22:"edit[$row_cont[ts_id]]";s:4:"help";s:15:"Edit this entry";s:7:"onclick";s:187:"window.open(egw::link(\'/index.php\',\'menuaction=timesheet.timesheet_ui.edit&ts_id=$row_cont[ts_id]\'),\'_blank\',\'dependent=yes,width=600,height=400,scrollbars=yes,status=yes\'); return false;";}i:3;a:6:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"name";s:24:"delete[$row_cont[ts_id]]";s:4:"help";s:17:"Delete this entry";s:7:"onclick";s:36:"return confirm(\'Delete this entry\');";}s:4:"span";s:8:",noPrint";i:4;a:4:{s:4:"type";s:8:"checkbox";s:4:"size";s:16:"$row_cont[ts_id]";s:4:"name";s:9:"checked[]";s:4:"help";s:47:"Select multiple timeshhets for a further action";}s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:11;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1250534976',);

