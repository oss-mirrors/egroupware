<?php
/**
	* eGroupWare - eTemplates for Application notifications
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2009-02-14 18:23
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package notifications
	* @subpackage setup
	* @version $Id$
	*/

$templ_version=1;

$templ_data[] = array('name' => 'notifications.checkmailbox','template' => '','lang' => '','group' => '0','version' => '1.7.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:2:{s:2:"c1";s:2:"th";s:2:"c2";s:7:"row,top";}i:1;a:4:{s:1:"A";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:3:{s:8:"readonly";s:4:"true";s:4:"type";s:5:"label";s:5:"label";s:6:"Folder";}}s:1:"B";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:3:{s:8:"readonly";s:4:"true";s:4:"type";s:5:"label";s:5:"label";s:7:"Subject";}}s:1:"C";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:3:{s:8:"readonly";s:4:"true";s:4:"type";s:5:"label";s:5:"label";s:4:"From";}}s:1:"D";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:3:{s:8:"readonly";s:4:"true";s:4:"type";s:5:"label";s:5:"label";s:8:"Received";}}}i:2;a:4:{s:1:"A";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:2:{s:4:"name";s:19:"${row}[mail_folder]";s:4:"type";s:5:"label";}}s:1:"B";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:4:{s:7:"no_lang";s:1:"1";s:4:"name";s:20:"${row}[mail_subject]";s:4:"size";s:103:"b,felamimail.uidisplay.display&uid=$row_cont[mail_uid]&mailbox=$row_cont[mail_folder_base64],,,,750x500";s:4:"type";s:5:"label";}}s:1:"C";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:3:{s:7:"no_lang";s:1:"1";s:4:"name";s:17:"${row}[mail_from]";s:4:"type";s:5:"label";}}s:1:"D";a:3:{s:4:"size";s:6:"1,,0,0";s:4:"type";s:4:"vbox";i:1;a:4:{s:8:"readonly";s:4:"true";s:4:"name";s:21:"${row}[mail_received]";s:4:"size";s:2:",8";s:4:"type";s:9:"date-time";}}}}s:4:"cols";i:4;s:4:"rows";i:2;}}','size' => '','style' => '','modified' => '1234631988',);

