<?php
/**
 * EGroupware - eTemplates for Application phpgwapi
 * http://www.egroupware.org
 * generated by soetemplate::dump4setup() 2015-02-13 20:28
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package phpgwapi
 * @subpackage setup
 * @version $Id$
 */

$templ_version=1;

$templ_data[] = array('name' => 'phpgwapi.about.detail','template' => '','lang' => '','group' => '0','version' => '1.4.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:7:"no_lang";s:1:"1";s:4:"size";s:9:"70%,,,,,4";s:4:"data";a:5:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:4:"html";s:4:"name";s:5:"image";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:4:"name";s:4:"name";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:2;s:4:"size";s:6:",,,,,4";s:7:"options";a:1:{i:5;s:1:"4";}}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:4:"html";s:4:"name";s:11:"description";}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"name";s:4:"note";s:7:"no_lang";s:1:"1";}}i:4;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:4:{s:2:"c1";s:7:"row,top";s:2:"c2";s:7:"row,top";s:2:"c3";s:7:"row,top";s:2:"c4";s:7:"row,top";}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:6:"author";s:4:"span";s:2:"th";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:4:"name";s:6:"author";s:7:"no_lang";s:1:"1";}}i:2;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:5:"label";s:10:"maintainer";s:4:"span";s:2:"th";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:10:"maintainer";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"version";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:7:"version";s:7:"no_lang";s:1:"1";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"license";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:7:"license";}}}s:4:"rows";i:4;s:4:"cols";i:2;s:4:"size";s:6:",,,,,3";s:7:"options";a:1:{i:5;s:1:"3";}}}}s:4:"rows";i:4;s:4:"cols";i:1;s:7:"options";a:2:{i:0;s:3:"70%";i:5;s:1:"4";}}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:5:",,,,5";s:7:"options";a:1:{i:4;s:1:"5";}}}','size' => ',,,,5','style' => '','modified' => '1175950403',);

$templ_data[] = array('name' => 'phpgwapi.about.index','template' => '','lang' => '','group' => '0','version' => '1.8.001','data' => 'a:2:{i:0;a:3:{s:4:"type";s:3:"tab";s:4:"name";s:50:"general|applications|templates|languages|changelog";s:5:"label";s:50:"General|Applications|Templates|Languages|Changelog";}i:1;a:7:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";}i:1;a:7:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"name";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"author";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"maintainer";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"version";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"license";}s:1:"G";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"details";}}i:2;a:7:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:4:"name";s:16:"${row}[appImage]";s:7:"no_lang";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[appName]";s:7:"no_lang";s:1:"1";}s:1:"C";a:3:{s:4:"type";s:4:"html";s:4:"name";s:17:"${row}[appAuthor]";s:7:"no_lang";s:1:"1";}s:1:"D";a:3:{s:4:"type";s:4:"html";s:4:"name";s:21:"${row}[appMaintainer]";s:7:"no_lang";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:4:"name";s:18:"${row}[appVersion]";s:7:"no_lang";s:1:"1";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:18:"${row}[appLicense]";s:7:"no_lang";s:1:"1";}s:1:"G";a:4:{s:4:"type";s:4:"html";s:5:"align";s:6:"center";s:4:"name";s:17:"${row}[appDetail]";s:7:"no_lang";s:1:"1";}}}s:4:"rows";i:2;s:4:"cols";i:7;s:4:"name";s:4:"rows";s:8:"disabled";s:1:"1";s:7:"options";a:0:{}}}','size' => '','style' => '','modified' => '1285755915',);

$templ_data[] = array('name' => 'phpgwapi.about.index.applications','template' => '','lang' => '','group' => '0','version' => '14.1','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:236:"<strong>This is a list of your available applications.</strong><br />For a complete list of applications available for eGroupWare visit <a href="http://www.egroupware.org/applications" target="_blank">www.egroupware.org/applications</a>";}}i:2;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:7:"row,top";}i:1;a:7:{s:1:"A";a:5:{s:4:"type";s:5:"label";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:4:"size";s:3:",,1";s:4:"name";s:12:"applications";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:7:"options";a:1:{i:2;s:1:"1";}}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"name";s:12:"applications";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"name";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"author";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"maintainer";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"version";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"license";}s:1:"G";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"details";}}i:2;a:7:{s:1:"A";a:5:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[appImage]";s:5:"align";s:6:"center";s:4:"span";s:12:",et2_appicon";}s:1:"B";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:15:"${row}[appName]";}s:1:"C";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:17:"${row}[appAuthor]";}s:1:"D";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:21:"${row}[appMaintainer]";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:18:"${row}[appVersion]";}s:1:"F";a:4:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:18:"${row}[appLicense]";s:5:"align";s:6:"center";}s:1:"G";a:4:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:5:"align";s:6:"center";s:4:"name";s:18:"${row}[appDetails]";}}}s:4:"rows";i:2;s:4:"cols";i:7;s:4:"name";s:12:"applications";s:7:"options";a:0:{}}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:19:"100%,500,0,,5,,auto";s:7:"options";a:5:{i:0;s:4:"100%";i:1;s:3:"500";i:6;s:4:"auto";i:4;s:1:"5";i:2;s:1:"0";}}}','size' => '100%,500,0,,5,,auto','style' => '','modified' => '1175950363',);

$templ_data[] = array('name' => 'phpgwapi.about.index.changelog','template' => '','lang' => '','group' => '0','version' => '1.8.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"textarea";s:4:"name";s:9:"changelog";s:8:"readonly";s:1:"1";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,500,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"500";i:6;s:4:"auto";}}}','size' => '100%,500,,,,,auto','style' => '','modified' => '1285756882',);

$templ_data[] = array('name' => 'phpgwapi.about.index.general','template' => '','lang' => '','group' => '0','version' => '1.8.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:4:"logo";}}i:2;a:1:{s:1:"A";a:2:{s:4:"type";s:4:"html";s:4:"name";s:10:"apiVersion";}}i:3;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"hrule";}}i:4;a:1:{s:1:"A";a:2:{s:4:"type";s:4:"html";s:4:"name";s:12:"text_content";}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:17:"600,500,,,5,,auto";s:7:"options";a:4:{i:0;s:3:"600";i:1;s:3:"500";i:6;s:4:"auto";i:4;s:1:"5";}}}','size' => '600,500,,,5,,auto','style' => '','modified' => '1175950348',);

$templ_data[] = array('name' => 'phpgwapi.about.index.languages','template' => '','lang' => '','group' => '0','version' => '1.8.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:225:"<b>This is a list of your available languages</b><br />For a complete list of languages available for EGroupware visit <a href="http://community.egroupware.org/languages" target="_blank">community.egroupware.org/languages</a>";}}i:2;a:1:{s:1:"A";a:7:{s:4:"type";s:4:"grid";s:4:"size";s:3:",,0";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"language";}}i:2;a:1:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:16:"${row}[langName]";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"name";s:12:"translations";s:7:"options";a:1:{i:2;s:1:"0";}}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:18:"100%,500,,,5,,auto";s:7:"options";a:4:{i:0;s:4:"100%";i:1;s:3:"500";i:6;s:4:"auto";i:4;s:1:"5";}}}','size' => '100%,500,,,5,,auto','style' => '','modified' => '1175950333',);

$templ_data[] = array('name' => 'phpgwapi.about.index.templates','template' => '','lang' => '','group' => '0','version' => '1.8.001','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:223:"<strong>This is a list of your available templates</strong><br />For a complete list of templates available for eGroupWare visit <a href="http://www.egroupware.org/templates" target="_blank">www.egroupware.org/templates</a>";}}i:2;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"name";s:9:"templates";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:7:"row,top";}i:1;a:7:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"name";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"author";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"maintainer";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"version";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"license";}s:1:"G";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"details";}}i:2;a:7:{s:1:"A";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:21:"${row}[templateImage]";}s:1:"B";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:20:"${row}[templateName]";}s:1:"C";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:22:"${row}[templateAuthor]";}s:1:"D";a:3:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:26:"${row}[templateMaintainer]";}s:1:"E";a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:23:"${row}[templateVersion]";}s:1:"F";a:4:{s:4:"type";s:4:"html";s:7:"no_lang";s:1:"1";s:4:"name";s:23:"${row}[templateLicense]";s:5:"align";s:6:"center";}s:1:"G";a:3:{s:4:"type";s:4:"html";s:4:"name";s:23:"${row}[templateDetails]";s:5:"align";s:6:"center";}}}s:4:"rows";i:2;s:4:"cols";i:7;s:7:"options";a:0:{}}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:18:"100%,500,,,5,,auto";s:7:"options";a:4:{i:0;s:4:"100%";i:1;s:3:"500";i:6;s:4:"auto";i:4;s:1:"5";}}}','size' => '100%,500,,,5,,auto','style' => '','modified' => '1175950302',);

