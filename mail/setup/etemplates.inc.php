<?php
/**
 * EGroupware - eTemplates for Application mail
 * http://www.egroupware.org
 * generated by soetemplate::dump4setup() 2013-02-11 14:41
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package mail
 * @subpackage setup
 * @version $Id$
 */

$templ_version=1;

$templ_data[] = array('name' => 'mail.index','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:2:{i:0;a:3:{s:4:"type";s:4:"tree";s:4:"name";s:14:"nm[foldertree]";s:7:"onclick";s:37:"alert(\'onChange node=\'+arguments[0]);";}i:1;a:3:{s:4:"type";s:9:"nextmatch";s:4:"name";s:2:"nm";s:4:"size";s:4:"rows";}}','size' => '100%,,,,0,3','style' => '','modified' => '1360577272',);

$templ_data[] = array('name' => 'mail.index.rows','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:6:{s:2:"c1";s:2:"th";s:1:"A";s:2:"25";s:1:"F";s:2:"50";s:1:"E";s:3:"120";s:1:"D";s:3:"120";s:1:"C";s:2:"95";}i:1;a:6:{s:1:"A";a:4:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:2:"ID";s:4:"name";s:3:"uid";s:8:"readonly";s:1:"1";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:7:"subject";s:5:"label";s:7:"subject";}s:1:"C";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"date";s:4:"name";s:4:"date";s:5:"align";s:6:"center";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"to";s:4:"name";s:9:"toaddress";}s:1:"E";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"from";s:4:"name";s:11:"fromaddress";}s:1:"F";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"size";s:4:"name";s:4:"size";s:5:"align";s:6:"center";}}i:2;a:6:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:11:"${row}[uid]";s:8:"readonly";s:1:"1";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[subject]";}s:1:"C";a:4:{s:4:"type";s:9:"date-time";s:4:"name";s:12:"${row}[date]";s:8:"readonly";s:1:"1";s:5:"align";s:6:"center";}s:1:"D";a:3:{s:4:"type";s:9:"url-email";s:4:"name";s:17:"${row}[toaddress]";s:8:"readonly";s:1:"1";}s:1:"E";a:3:{s:4:"type";s:9:"url-email";s:4:"name";s:19:"${row}[fromaddress]";s:8:"readonly";s:1:"1";}s:1:"F";a:5:{s:4:"type";s:5:"label";s:4:"name";s:12:"${row}[size]";s:7:"no_lang";s:1:"1";s:8:"readonly";s:1:"1";s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:6;}}','size' => '','style' => '','modified' => '1360252030',);

$templ_data[] = array('name' => 'mail.TestConnection','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1360585356',);

