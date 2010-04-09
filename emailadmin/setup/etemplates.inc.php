<?php
/**
	* eGroupWare - eTemplates for Application emailadmin
	* http://www.egroupware.org
	* generated by soetemplate::dump4setup() 2010-04-09 09:58
	*
	* @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
	* @package emailadmin
	* @subpackage setup
	* @version $Id: class.soetemplate.inc.php 29386 2010-03-05 08:18:46Z ralfbecker $
	*/

$templ_version=1;

$templ_data[] = array('name' => 'emailadmin.edit','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:2:{s:2:"h1";s:6:",!@msg";s:1:"A";s:4:"100%";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:5:"align";s:6:"center";s:4:"name";s:3:"msg";s:4:"span";s:10:",redItalic";}}i:2;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:3:{s:1:"C";s:3:"50%";s:1:"B";s:3:"40%";s:1:"D";s:2:"5%";}i:1;a:4:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:5:"label";s:2:"ID";s:4:"name";s:13:"ea_profile_id";s:8:"readonly";s:1:"1";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"Profile Name";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:14:"ea_description";s:5:"align";s:5:"right";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"align";s:5:"right";}}}s:4:"rows";i:1;s:4:"cols";i:4;s:4:"size";s:3:"98%";s:7:"options";a:1:{i:0;s:3:"98%";}}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:3:"tab";s:5:"label";s:45:"Global|SMTP|IMAP|Signature|Stationery|History";s:4:"name";s:50:"tabs=global|SMTP|IMAP|signature|stationery|history";}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";i:1;a:3:{s:4:"type";s:6:"button";s:4:"name";s:4:"save";s:5:"label";s:4:"Save";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:5:"Apply";s:4:"name";s:5:"apply";}i:3;a:3:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:6:"cancel";}}i:2;a:5:{s:4:"type";s:6:"button";s:4:"name";s:6:"delete";s:5:"label";s:6:"Delete";s:5:"align";s:5:"right";s:7:"onclick";s:60:"return confirm(\'Do you really want to delete this Profile\');";}}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '.redItalic { color: red; font-style: italics; }','modified' => '1255612671',);

$templ_data[] = array('name' => 'emailadmin.edit.global','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:4:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:5:"label";s:12:"Organisation";s:4:"size";s:1:"1";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:50:"enter your default mail domain (from: user@domain)";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:17:"ea_default_domain";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"name of organisation";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:20:"ea_organisation_name";}}}s:4:"rows";i:2;s:4:"cols";i:2;s:7:"options";a:0:{}}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:21:"profile access rights";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"can be used by application";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:10:"ea_appname";s:4:"size";s:15:"any application";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"can be used by group";}s:1:"B";a:3:{s:4:"type";s:14:"select-account";s:4:"size";s:16:"any group,groups";s:4:"name";s:8:"ea_group";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:19:"can be used by user";}s:1:"B";a:3:{s:4:"type";s:14:"select-account";s:4:"size";s:17:"any user,accounts";s:4:"name";s:7:"ea_user";}}}s:4:"rows";i:3;s:4:"cols";i:2;}}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:14:"global options";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:5:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"profile is active";}s:1:"B";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:9:"ea_active";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:37:"users can define their own identities";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:26:"ea_user_defined_identities";s:4:"size";s:6:"yes,no";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:40:"users can define their own emailaccounts";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:24:"ea_user_defined_accounts";s:4:"size";s:6:"yes,no";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"order";}s:1:"B";a:2:{s:4:"type";s:3:"int";s:4:"name";s:8:"ea_order";}}}s:4:"rows";i:4;s:4:"cols";i:2;}}}}s:4:"rows";i:3;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1255426691',);

$templ_data[] = array('name' => 'emailadmin.edit.history','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,400,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}','size' => '100%,400,,,,,auto','style' => '','modified' => '1255591575',);

$templ_data[] = array('name' => 'emailadmin.edit.IMAP','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:1:{s:2:"h3";s:27:",!@ea_imap_login_type=admin";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"select type of IMAP server";}i:2;a:3:{s:4:"type";s:6:"select";s:4:"name";s:12:"ea_imap_type";s:5:"align";s:5:"right";}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:15:"server settings";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:34:"IMAP server hostname or ip address";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:14:"ea_imap_server";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:16:"IMAP server port";}s:1:"B";a:2:{s:4:"type";s:3:"int";s:4:"name";s:12:"ea_imap_port";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"imap server logintyp";}s:1:"B";a:3:{s:4:"type";s:6:"select";s:4:"name";s:18:"ea_imap_login_type";s:8:"onchange";i:1;}}}s:4:"rows";i:3;s:4:"cols";i:2;}}}i:3;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:1:"2";s:5:"label";s:42:"Use predefined username and password below";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"username";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:21:"ea_imap_auth_username";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"password";}s:1:"B";a:2:{s:4:"type";s:6:"passwd";s:4:"name";s:21:"ea_imap_auth_password";}}}s:4:"rows";i:3;s:4:"cols";i:2;}}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:19:"encryption settings";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:20:"encrypted connection";}s:1:"B";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";i:1;a:4:{s:4:"type";s:5:"radio";s:4:"size";s:1:"1";s:5:"label";s:8:"STARTTLS";s:4:"name";s:22:"ea_imap_tsl_encryption";}i:2;a:4:{s:4:"type";s:5:"radio";s:4:"name";s:22:"ea_imap_tsl_encryption";s:4:"size";s:1:"2";s:5:"label";s:3:"TLS";}i:3;a:4:{s:4:"type";s:5:"radio";s:4:"name";s:22:"ea_imap_tsl_encryption";s:4:"size";s:1:"3";s:5:"label";s:3:"SSL";}i:4;a:4:{s:4:"type";s:5:"radio";s:4:"name";s:22:"ea_imap_tsl_encryption";s:4:"size";s:1:"0";s:5:"label";s:13:"no encryption";}}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:27:"do not validate certificate";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:16:"ea_imap_tsl_auth";s:4:"size";s:6:"yes,no";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}i:5;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:14:"sieve settings";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:5:{i:0;a:1:{s:2:"h1";s:2:",1";}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"Sieve server or ip address";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:20:"ea_imap_sieve_server";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"enable Sieve";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:20:"ea_imap_enable_sieve";s:4:"size";s:6:"yes,no";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"Sieve server port";}s:1:"B";a:2:{s:4:"type";s:3:"int";s:4:"name";s:18:"ea_imap_sieve_port";}}i:4;a:2:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:1:"2";s:5:"label";s:78:"Vacation messages with start-  and end-date require an admin account to be set";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:4;s:4:"cols";i:2;}}}i:6;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:32:"Cyrus IMAP server administration";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:4:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:39:"enable Cyrus IMAP server administration";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"admin username";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:18:"ea_imap_admin_user";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"admin password";}s:1:"B";a:2:{s:4:"type";s:6:"passwd";s:4:"name";s:16:"ea_imap_admin_pw";}}}s:4:"rows";i:3;s:4:"cols";i:2;}}}}s:4:"rows";i:6;s:4:"cols";i:1;s:4:"size";s:17:"100%,400,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}','size' => '100%,400,,,,,auto','style' => '','modified' => '1255602377',);

$templ_data[] = array('name' => 'emailadmin.edit.signature','template' => '','lang' => '','group' => '0','version' => '1.7.004','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:3:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:37:"users can define their own signatures";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:26:"ea_user_defined_signatures";s:4:"size";s:6:"yes,no";}}i:2;a:2:{s:1:"A";a:4:{s:4:"type";s:8:"htmlarea";s:4:"span";s:1:"2";s:4:"name";s:20:"ea_default_signature";s:4:"size";s:17:"advanced,,700,180";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,400,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}','size' => '100%,400,,,,,auto','style' => '','modified' => '1270799878',);

$templ_data[] = array('name' => 'emailadmin.edit.SMTP','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:26:"Select type of SMTP Server";}i:2;a:3:{s:4:"type";s:6:"select";s:4:"name";s:12:"ea_smtp_type";s:5:"align";s:5:"right";}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:13:"SMTP settings";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:34:"SMTP-Server hostname or IP address";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:14:"ea_smtp_server";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:16:"SMTP-Server Port";}s:1:"B";a:2:{s:4:"type";s:3:"int";s:4:"name";s:12:"ea_smtp_port";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:19:"smtp authentication";i:1;a:5:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"Use SMTP auth";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:12:"ea_smtp_auth";s:4:"size";s:6:"yes,no";}}i:2;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:29:"send using this eMail-Address";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:18:"smtp_senders_email";}}i:3;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"username";}s:1:"B";a:2:{s:4:"type";s:4:"text";s:4:"name";s:21:"ea_smtp_auth_username";}}i:4;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"password";}s:1:"B";a:2:{s:4:"type";s:6:"passwd";s:4:"name";s:21:"ea_smtp_auth_password";}}}s:4:"rows";i:4;s:4:"cols";i:2;s:7:"options";a:0:{}}}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:12:"smtp options";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:2:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:32:"user can edit forwarding address";}s:1:"B";a:3:{s:4:"type";s:8:"checkbox";s:4:"name";s:24:"ea_editforwardingaddress";s:4:"size";s:6:"yes,no";}}}s:4:"rows";i:1;s:4:"cols";i:2;}}}}s:4:"rows";i:4;s:4:"cols";i:1;s:4:"size";s:17:"100%,400,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}','size' => '100%,400,,,,,auto','style' => '','modified' => '1255430255',);

$templ_data[] = array('name' => 'emailadmin.edit.stationery','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:8:"groupbox";s:4:"size";s:1:"1";s:5:"label";s:16:"active templates";i:1;a:5:{s:4:"type";s:4:"grid";s:7:"options";a:0:{}s:4:"data";a:3:{i:0;a:0:{}i:1;a:2:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:5:"label";s:44:"users can utilize these stationery templates";}s:1:"B";a:4:{s:4:"type";s:6:"select";s:4:"name";s:30:"ea_stationery_active_templates";i:1;a:3:{s:4:"type";s:3:"box";s:4:"name";s:30:"ea_stationery_active_templates";s:4:"size";s:3:",10";}s:4:"size";s:1:"5";}}i:2;a:2:{s:1:"A";a:6:{s:4:"type";s:4:"html";s:4:"span";s:1:"2";s:8:"readonly";s:1:"1";s:4:"help";s:27:"manage stationery templates";s:4:"name";s:27:"manage_stationery_templates";s:5:"align";s:5:"right";}s:1:"B";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:2;}}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:17:"100%,400,,,,,auto";s:7:"options";a:3:{i:0;s:4:"100%";i:1;s:3:"400";i:6;s:4:"auto";}}}','size' => '100%,400,,,,,auto','style' => '','modified' => '1255600503',);

$templ_data[] = array('name' => 'emailadmin.index','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}s:4:"data";a:6:{i:0;a:2:{s:2:"h2";s:6:",!@msg";s:2:"h1";s:11:",!@subtitle";}i:1;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:8:"readonly";s:1:"1";s:5:"align";s:6:"center";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"html";s:4:"name";s:8:"subtitle";s:8:"readonly";s:1:"1";s:5:"align";s:6:"center";}i:2;a:3:{s:4:"type";s:4:"html";s:4:"name";s:13:"addJavaScript";s:8:"readonly";s:1:"1";}}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"span";s:10:",redItalic";s:4:"name";s:3:"msg";s:5:"align";s:6:"center";}}i:3;a:1:{s:1:"A";a:5:{s:4:"type";s:6:"button";s:4:"name";s:10:"AddProfile";s:5:"label";s:3:"Add";s:5:"align";s:5:"right";s:7:"onclick";s:165:"window.open(egw::link(\'/index.php\',\'menuaction=emailadmin.emailadmin_ui.add\'),\'_blank\',\'dependent=yes,width=850,height=540,scrollbars=yes,status=yes\'); return false;";}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:9:"nextmatch";s:4:"size";s:21:"emailadmin.index.rows";s:4:"span";s:3:"all";s:4:"name";s:2:"nm";}}i:5;a:1:{s:1:"A";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"3";s:5:"align";s:5:"right";i:1;a:5:{s:4:"type";s:6:"button";s:4:"name";s:6:"delete";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:7:"onclick";s:34:"return confirm(\'Delete Profiles\');";}i:2;a:4:{s:4:"type";s:10:"buttononly";s:4:"span";s:15:",selectAllArrow";s:7:"onclick";s:71:"toggle_all(this.form,form::name(\'nm[rows][selected][]\')); return false;";s:4:"size";s:9:"arrow_ltr";}i:3;a:1:{s:4:"type";s:5:"label";}}}}s:4:"rows";i:5;s:4:"cols";i:1;}}','size' => '100%','style' => '.redItalic { color: red; font-style: italics; }','modified' => '1255529643',);

$templ_data[] = array('name' => 'emailadmin.index.rows','template' => '','lang' => '','group' => '0','version' => '1.7.003','data' => 'a:1:{i:0;a:7:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:3:{s:2:"c1";s:2:"th";s:2:"c2";s:3:"row";s:1:"P";s:2:"1%";}i:1;a:16:{s:1:"A";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:2:"ID";s:4:"name";s:13:"ea_profile_id";}s:1:"B";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:11:"Description";s:4:"name";s:14:"ea_description";}s:1:"C";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:10:"domainname";s:4:"name";s:17:"ea_default_domain";}s:1:"D";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:16:"SMTP Server Name";s:4:"name";s:14:"ea_smtp_server";}s:1:"E";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:16:"SMTP Server Type";s:4:"name";s:12:"ea_smtp_type";}s:1:"F";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:16:"SMTP Server Port";s:4:"name";s:12:"ea_smtp_port";}s:1:"G";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:16:"IMAP Server Name";s:4:"name";s:14:"ea_imap_server";}s:1:"H";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:16:"IMAP Server Type";s:4:"name";s:12:"ea_imap_type";}s:1:"I";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:16:"IMAP Server Port";s:4:"name";s:12:"ea_imap_port";}s:1:"J";a:3:{s:4:"type";s:16:"nextmatch-header";s:5:"label";s:22:"IMAP Server Login Type";s:4:"name";s:18:"ea_imap_login_type";}s:1:"K";a:3:{s:4:"type";s:16:"nextmatch-header";s:4:"name";s:10:"ea_appname";s:5:"label";s:11:"Application";}s:1:"L";a:3:{s:4:"type";s:16:"nextmatch-header";s:4:"name";s:8:"ea_group";s:5:"label";s:5:"Group";}s:1:"M";a:3:{s:4:"type";s:16:"nextmatch-header";s:4:"name";s:7:"ea_user";s:5:"label";s:4:"User";}s:1:"N";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:5:"order";s:4:"name";s:8:"ea_order";}s:1:"O";a:3:{s:4:"type";s:20:"nextmatch-sortheader";s:5:"label";s:6:"Active";s:4:"name";s:9:"ea_active";}s:1:"P";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Action";}i:2;a:4:{s:4:"type";s:10:"buttononly";s:4:"size";s:5:"check";s:5:"label";s:10:"Select All";s:7:"onclick";s:61:"toggle_all(this.form,form::name(\'selected[]\')); return false;";}}}i:2;a:16:{s:1:"A";a:2:{s:4:"type";s:5:"label";s:4:"name";s:21:"${row}[ea_profile_id]";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:4:"name";s:22:"${row}[ea_description]";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:4:"name";s:25:"${row}[ea_default_domain]";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:4:"name";s:22:"${row}[ea_smtp_server]";}s:1:"E";a:3:{s:4:"type";s:6:"select";s:4:"name";s:20:"${row}[ea_smtp_type]";s:8:"readonly";s:1:"1";}s:1:"F";a:2:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[ea_smtp_port]";}s:1:"G";a:2:{s:4:"type";s:5:"label";s:4:"name";s:22:"${row}[ea_imap_server]";}s:1:"H";a:3:{s:4:"type";s:6:"select";s:4:"name";s:20:"${row}[ea_imap_type]";s:8:"readonly";s:1:"1";}s:1:"I";a:2:{s:4:"type";s:5:"label";s:4:"name";s:20:"${row}[ea_imap_port]";}s:1:"J";a:2:{s:4:"type";s:5:"label";s:4:"name";s:26:"${row}[ea_imap_login_type]";}s:1:"K";a:3:{s:4:"type";s:6:"select";s:4:"name";s:18:"${row}[ea_appname]";s:8:"readonly";s:1:"1";}s:1:"L";a:4:{s:4:"type";s:14:"select-account";s:4:"name";s:16:"${row}[ea_group]";s:8:"readonly";s:1:"1";s:4:"size";s:7:",groups";}s:1:"M";a:4:{s:4:"type";s:14:"select-account";s:4:"name";s:15:"${row}[ea_user]";s:8:"readonly";s:1:"1";s:4:"size";s:9:",accounts";}s:1:"N";a:3:{s:4:"type";s:5:"label";s:4:"name";s:16:"${row}[ea_order]";s:7:"no_lang";s:1:"1";}s:1:"O";a:2:{s:4:"type";s:5:"label";s:4:"name";s:17:"${row}[ea_active]";}s:1:"P";a:6:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"4";i:1;a:6:{s:4:"type";s:6:"button";s:4:"size";s:4:"edit";s:5:"label";s:4:"Edit";s:4:"help";s:17:"Edit this Profile";s:4:"name";s:30:"edit[$row_cont[ea_profile_id]]";s:7:"onclick";s:205:"window.open(egw::link(\'/index.php\',\'menuaction=emailadmin.emailadmin_ui.edit&profileid=$row_cont[ea_profile_id]\'),\'ea_profile\',\'dependent=yes,width=850,height=540,scrollbars=yes,status=yes\'); return false;";}i:2;a:6:{s:4:"type";s:6:"button";s:4:"size";s:6:"delete";s:5:"label";s:6:"Delete";s:4:"name";s:32:"delete[$row_cont[ea_profile_id]]";s:7:"onclick";s:60:"return confirm(\'Do you really want to delete this Profile\');";s:4:"help";s:19:"Delete this Profile";}i:3;a:3:{s:4:"type";s:8:"checkbox";s:4:"size";s:24:"$row_cont[ea_profile_id]";s:4:"name";s:10:"selected[]";}i:4;a:1:{s:4:"type";s:5:"label";}}}}s:4:"rows";i:2;s:4:"cols";i:16;s:4:"size";s:4:"100%";s:5:"align";s:6:"center";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1255607501',);

