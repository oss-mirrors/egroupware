<?php
/**
 * EGroupware - eTemplates for Application mail
 * http://www.egroupware.org
 * generated by soetemplate::dump4setup() 2013-08-28 16:05
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package mail
 * @subpackage setup
 * @version $Id$
 */

$templ_version=1;

$templ_data[] = array('name' => 'mail.display','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:2:{i:0;a:2:{s:4:"name";s:3:"msg";s:4:"type";s:4:"html";}i:1;a:10:{s:5:"width";s:4:"100%";s:4:"name";s:11:"mailDisplay";s:4:"type";s:4:"vbox";s:4:"size";s:1:"6";i:1;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:22:"mailDisplayHeadersFrom";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailDisplayHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"From";}i:2;a:3:{s:8:"readonly";s:4:"true";s:4:"name";s:18:"DisplayFromAddress";s:4:"type";s:9:"url-email";}}i:2;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:20:"mailDisplayHeadersTo";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailDisplayHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:2:"To";}i:2;a:3:{s:8:"readonly";s:4:"true";s:4:"name";s:16:"DisplayToAddress";s:4:"type";s:9:"url-email";}}i:3;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:22:"mailDisplayHeadersDate";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailDisplayHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Date";}i:2;a:4:{s:5:"align";s:4:"left";s:8:"readonly";s:4:"true";s:4:"name";s:11:"DisplayDate";s:4:"type";s:9:"date-time";}}i:4;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:25:"mailDisplayHeadersSubject";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailDisplayHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Subject";}i:2;a:4:{s:5:"align";s:4:"left";s:8:"readonly";s:4:"true";s:4:"name";s:14:"DisplaySubject";s:4:"type";s:5:"label";}}i:5;a:4:{s:4:"name";s:16:"mailDisplayIcons";s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Icons";}}i:6;a:4:{s:4:"name";s:20:"mailDisplayContainer";s:4:"type";s:3:"box";s:4:"size";s:1:"1";i:1;a:2:{s:4:"name";s:15:"mailDisplayBody";s:4:"type";s:4:"html";}}}}','size' => '','style' => '','modified' => '1370432711',);

$templ_data[] = array('name' => 'mail.importMessage','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:4:"file";}}i:2;a:1:{s:1:"A";a:1:{s:4:"type";s:3:"vfs";}}}s:4:"rows";i:2;s:4:"cols";i:1;}}','size' => '','style' => '','modified' => '1377698715',);

$templ_data[] = array('name' => 'mail.index','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:3:{i:0;a:5:{s:11:"autoloading";s:28:"mail.mail_ui.ajax_foldertree";s:7:"onclick";s:56:"app.mail.mail_changeFolder(widget.event_args[0],widget);";s:11:"parent_node";s:11:"tree_target";s:4:"name";s:14:"nm[foldertree]";s:4:"type";s:4:"tree";}i:1;a:2:{s:4:"name";s:3:"msg";s:4:"type";s:4:"html";}i:2;a:7:{s:9:"dock_side";s:10:"bottomDock";s:11:"orientation";s:1:"h";s:4:"name";s:12:"mailSplitter";s:4:"type";s:5:"split";s:4:"size";s:1:"2";i:1;a:4:{s:8:"onselect";s:21:"app.mail.mail_preview";s:4:"name";s:2:"nm";s:4:"type";s:9:"nextmatch";s:4:"size";s:15:"mail.index.rows";}i:2;a:10:{s:5:"width";s:4:"100%";s:4:"name";s:11:"mailPreview";s:4:"type";s:4:"vbox";s:4:"size";s:1:"6";i:1;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:22:"mailPreviewHeadersFrom";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailPreviewHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"From";}i:2;a:3:{s:8:"readonly";s:4:"true";s:4:"name";s:18:"previewFromAddress";s:4:"type";s:9:"url-email";}}i:2;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:20:"mailPreviewHeadersTo";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailPreviewHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:2:"To";}i:2;a:3:{s:8:"readonly";s:4:"true";s:4:"name";s:16:"previewToAddress";s:4:"type";s:9:"url-email";}}i:3;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:22:"mailPreviewHeadersDate";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailPreviewHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"Date";}i:2;a:4:{s:5:"align";s:4:"left";s:8:"readonly";s:4:"true";s:4:"name";s:11:"previewDate";s:4:"type";s:9:"date-time";}}i:4;a:7:{s:5:"width";s:4:"100%";s:4:"name";s:25:"mailPreviewHeadersSubject";s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:4:"span";s:19:",mailPreviewHeaders";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"Subject";}i:2;a:4:{s:5:"align";s:4:"left";s:8:"readonly";s:4:"true";s:4:"name";s:14:"previewSubject";s:4:"type";s:5:"label";}}i:5;a:4:{s:4:"name";s:16:"mailPreviewIcons";s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"Icons";}}i:6;a:4:{s:4:"name";s:20:"mailPreviewContainer";s:4:"type";s:3:"box";s:4:"size";s:1:"1";i:1;a:4:{s:11:"frameborder";s:1:"1";s:9:"scrolling";s:4:"auto";s:4:"name";s:13:"messageIFRAME";s:4:"type";s:6:"iframe";}}}}}','size' => '','style' => '','modified' => '1370427910',);

$templ_data[] = array('name' => 'mail.index.rows','template' => '','lang' => '','group' => '0','version' => '1.9.001','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:9:{s:2:"c1";s:2:"th";s:1:"A";s:2:"25";s:1:"F";s:3:"120";s:1:"E";s:2:"95";s:2:"c2";s:16:"$row_cont[class]";s:1:"G";s:3:"120";s:1:"H";s:2:"50";s:1:"C";s:2:"20";s:1:"B";s:2:"20";}i:1;a:8:{s:1:"A";a:4:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:2:"ID";s:4:"name";s:3:"uid";s:8:"readonly";s:1:"1";}s:1:"B";a:4:{s:4:"type";s:16:"nextmatch-header";s:4:"name";s:6:"status";s:5:"label";s:3:"St.";s:4:"help";s:6:"Status";}s:1:"C";a:4:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:3:"...";s:4:"name";s:11:"attachments";s:4:"help";s:16:"attachments, ...";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:4:"name";s:7:"subject";s:5:"label";s:7:"subject";}s:1:"E";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"date";s:4:"name";s:4:"date";s:5:"align";s:6:"center";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"to";s:4:"name";s:9:"toaddress";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"from";s:4:"name";s:11:"fromaddress";}s:1:"H";a:4:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:4:"size";s:4:"name";s:4:"size";s:5:"align";s:6:"center";}}i:2;a:8:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"name";s:11:"${row}[uid]";s:8:"readonly";s:1:"1";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"span";s:12:"1,status_img";}s:1:"C";a:2:{s:4:"type";s:4:"html";s:4:"name";s:19:"${row}[attachments]";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:4:"name";s:15:"${row}[subject]";}s:1:"E";a:4:{s:4:"type";s:15:"date-time_today";s:4:"name";s:12:"${row}[date]";s:8:"readonly";s:1:"1";s:5:"align";s:6:"center";}s:1:"F";a:3:{s:4:"type";s:9:"url-email";s:4:"name";s:17:"${row}[toaddress]";s:8:"readonly";s:1:"1";}s:1:"G";a:3:{s:4:"type";s:9:"url-email";s:4:"name";s:19:"${row}[fromaddress]";s:8:"readonly";s:1:"1";}s:1:"H";a:5:{s:4:"type";s:8:"vfs-size";s:4:"name";s:12:"${row}[size]";s:7:"no_lang";s:1:"1";s:8:"readonly";s:1:"1";s:5:"align";s:5:"right";}}}s:4:"rows";i:2;s:4:"cols";i:8;}}','size' => '','style' => '','modified' => '1360252030',);

