<?php
// eTemplates for Application 'addressbook', generated by soetemplate::dump4setup() 2005-11-04 00:08

/* $Id$ */

$templ_version=1;

$templ_data[] = array('name' => 'addressbook.edit','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:4:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:5:{s:1:"A";s:3:"750";s:2:"c3";s:4:",top";s:2:"h3";s:3:"350";s:2:"h4";s:5:",!@id";s:2:"h5";s:13:",@hidebuttons";}i:1;a:1:{s:1:"A";a:4:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";s:7:"no_lang";s:1:"1";}}i:2;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"text";s:4:"name";s:2:"fn";s:8:"readonly";s:1:"1";s:4:"span";s:3:"all";}}i:3;a:1:{s:1:"A";a:4:{s:4:"type";s:3:"tab";s:5:"label";s:41:"personal|organisation|home|details|custom";s:4:"name";s:41:"personal|organisation|home|details|custom";s:4:"span";s:3:"all";}}i:4;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"owner";}i:2;a:3:{s:4:"type";s:14:"select-account";s:4:"name";s:5:"owner";s:8:"readonly";s:1:"1";}}i:2;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:5:"right";i:1;a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"last modified";}i:2;a:3:{s:4:"type";s:9:"date-time";s:4:"name";s:8:"last_mod";s:8:"readonly";s:1:"1";}}}}i:5;a:1:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";i:1;a:7:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"5";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Edit";s:4:"name";s:12:"button[edit]";}i:2;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Copy";s:4:"name";s:12:"button[copy]";}i:3;a:3:{s:4:"type";s:6:"button";s:5:"label";s:4:"Save";s:4:"name";s:12:"button[save]";}i:4;a:3:{s:4:"type";s:6:"button";s:5:"label";s:5:"Apply";s:4:"name";s:13:"button[apply]";}i:5;a:4:{s:4:"type";s:6:"button";s:5:"label";s:6:"Cancel";s:4:"name";s:14:"button[cancel]";s:7:"onclick";s:27:"self.close(); return false;";}}i:2;a:5:{s:4:"type";s:6:"button";s:5:"label";s:6:"Delete";s:4:"name";s:14:"button[delete]";s:7:"onclick";s:65:"return confirm(\'Are you shure you want to delete this contact?\');";s:5:"align";s:5:"right";}}}i:6;a:1:{s:1:"A";a:2:{s:4:"type";s:8:"template";s:4:"name";s:22:"addressbook.editphones";}}}s:4:"rows";i:6;s:4:"cols";i:1;}}','size' => '','style' => '.redItalic { color: red; font-style: italic; }','modified' => '1130404011',);

$templ_data[] = array('name' => 'addressbook.edit.custom','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:3:{i:0;a:1:{s:2:"h2";s:4:"100%";}i:1;a:1:{s:1:"A";a:1:{s:4:"type";s:12:"customfields";}}i:2;a:1:{s:1:"A";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:2;s:4:"cols";i:1;s:4:"size";s:4:",320";s:7:"options";a:1:{i:1;s:3:"320";}}}','size' => ',320','style' => '','modified' => '1130529421',);

$templ_data[] = array('name' => 'addressbook.edit.details','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:5:{i:0;a:4:{s:2:"c1";s:4:",top";s:2:"c2";s:4:",top";s:2:"c3";s:4:",top";s:2:"h4";s:4:"100%";}i:1;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"notes";}s:1:"C";a:3:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"4,80";s:4:"name";s:4:"note";}}i:2;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:" label";}s:1:"C";a:3:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"4,80";s:4:"name";s:5:"label";}}i:3;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"public key";}s:1:"C";a:3:{s:4:"type";s:8:"textarea";s:4:"size";s:4:"4,80";s:4:"name";s:6:"pubkey";}}i:4;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:4;s:4:"cols";i:3;s:4:"size";s:4:",320";s:7:"options";a:1:{i:1;s:3:"320";}}}','size' => ',320','style' => '','modified' => '1130410016',);

$templ_data[] = array('name' => 'addressbook.edit.home','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:7:{i:0;a:2:{s:2:"c6";s:9:",baseline";s:2:"h6";s:4:"100%";}i:1;a:3:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:6:"gohome";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"home street";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:14:"adr_two_street";}}i:2;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"home city";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:16:"adr_two_locality";}}i:3;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"home zip code";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:18:"adr_two_postalcode";}}i:4;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"home state";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:16:"adr_two_locality";}}i:5;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"home country";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:19:"adr_two_countryname";}}i:6;a:3:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:6;s:4:"cols";i:3;s:4:"size";s:4:",320";s:7:"options";a:1:{i:1;s:3:"320";}}}','size' => ',320','style' => '','modified' => '1130409535',);

$templ_data[] = array('name' => 'addressbook.edit.organisation','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:13:{i:0;a:1:{s:3:"h12";s:4:"100%";}i:1;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:4:"gear";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"title";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:5:"title";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"department";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"org_unit";s:4:"size";s:2:"40";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:3;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:4;a:4:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:6:"gohome";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"company name";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:8:"org_name";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:5;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:15:"business street";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:14:"adr_one_street";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:6;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"address line 2";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:8:"address2";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:7;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"address line 3";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:8:"address3";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:8;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"business city";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:16:"adr_one_locality";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:9;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:17:"business zip code";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:18:"adr_one_postalcode";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:10;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"business state";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:14:"adr_one_region";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:11;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:16:"business country";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"40";s:4:"name";s:19:"adr_one_countryname";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:12;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:12;s:4:"cols";i:4;s:4:"size";s:4:",320";s:7:"options";a:1:{i:1;s:3:"320";}}}','size' => ',320','style' => '','modified' => '1130408337',);

$templ_data[] = array('name' => 'addressbook.edit.personal','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:12:{i:0;a:2:{s:2:"c9";s:4:",top";s:3:"h11";s:4:"100%";}i:1;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:8:"personal";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"prefix";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:6:"prefix";s:4:"size";s:2:"40";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"time zone";}s:1:"F";a:5:{s:4:"type";s:6:"select";i:1;a:1:{s:4:"type";s:5:"label";}i:2;a:1:{s:4:"type";s:5:"label";}s:4:"name";s:2:"tz";s:7:"no_lang";s:1:"1";}}i:2;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"first name";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:7:"n_given";s:4:"size";s:2:"40";}s:1:"D";a:2:{s:4:"type";s:5:"image";s:4:"name";s:12:"kaddressbook";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"home phone";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"tel_home";s:4:"size";s:2:"30";}}i:3;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:" middle name";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"n_middle";s:4:"size";s:2:"40";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"business phone";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"tel_work";s:4:"size";s:2:"30";}}i:4;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"last name";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"n_family";s:4:"size";s:2:"40";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"mobile phone";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"tel_cell";s:4:"size";s:2:"30";}}i:5;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"suffix";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"name";s:8:"n_suffix";s:4:"size";s:2:"40";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:3:{s:4:"type";s:6:"button";s:5:"label";s:17:"more phonenumbers";s:7:"onclick";s:36:"showphones(this.form); return false;";}}i:6;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:2:{s:4:"type";s:5:"image";s:4:"name";s:5:"email";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"home email";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:10:"email_home";s:4:"size";s:2:"30";}}i:7;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:" birthday";}s:1:"C";a:3:{s:4:"type";s:4:"date";i:1;a:1:{s:4:"type";s:5:"label";}s:4:"name";s:4:"bday";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"business email";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:14:"business email";s:4:"size";s:2:"30";}}i:8;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:9;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:6:"folder";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:8:"category";}s:1:"C";a:3:{s:4:"type";s:10:"select-cat";s:4:"name";s:6:"cat_id";s:4:"size";s:1:"3";}s:1:"D";a:2:{s:4:"type";s:5:"image";s:4:"name";s:15:"package_network";}s:1:"E";a:2:{s:4:"type";s:5:"label";s:5:"label";s:3:"url";}s:1:"F";a:3:{s:4:"type";s:4:"text";s:4:"name";s:3:"url";s:4:"size";s:2:"30";}}i:10;a:6:{s:1:"A";a:2:{s:4:"type";s:5:"image";s:4:"name";s:8:"password";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:7:"private";}s:1:"C";a:2:{s:4:"type";s:8:"checkbox";s:4:"name";s:7:"private";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}i:11;a:6:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}s:1:"E";a:1:{s:4:"type";s:5:"label";}s:1:"F";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:11;s:4:"cols";i:6;s:4:"size";s:4:",320";s:7:"options";a:1:{i:1;s:3:"320";}}}','size' => ',320','style' => '','modified' => '1130404323',);

$templ_data[] = array('name' => 'addressbook.editphones','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:19:{i:0;a:1:{s:2:"c3";s:2:"th";}i:1;a:4:{s:1:"A";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";s:4:"span";s:16:"all,windowheader";i:1;a:5:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"2";s:5:"align";s:6:"center";i:1;a:3:{s:4:"type";s:5:"label";s:5:"label";s:20:"Edit Phonenumbers - ";s:5:"align";s:5:"right";}i:2;a:3:{s:4:"type";s:5:"label";s:7:"no_lang";s:1:"1";s:4:"name";s:2:"fn";}}}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:2;a:4:{s:1:"A";a:3:{s:4:"type";s:5:"label";s:4:"span";s:13:"all,redItalic";s:4:"name";s:3:"msg";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:3;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"Description";}s:1:"C";a:2:{s:4:"type";s:5:"label";s:5:"label";s:6:"Number";}s:1:"D";a:2:{s:4:"type";s:5:"label";s:5:"label";s:4:"pref";}}i:4;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"home phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_home2";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:8:"tel_home";s:4:"name";s:10:"tel_prefer";}}i:5;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:14:"business phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_work2";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:8:"tel_work";s:4:"name";s:10:"tel_prefer";}}i:6;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:"mobile phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_cell2";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:8:"tel_cell";s:4:"name";s:10:"tel_prefer";}}i:7;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:3:"fax";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:7:"tel_fax";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"tel_fax";s:4:"name";s:10:"tel_prefer";}}i:8;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"car phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:7:"tel_car";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"tel_car";s:4:"name";s:10:"tel_prefer";}}i:9;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"video phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_video";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:9:"tel_video";s:4:"name";s:10:"tel_prefer";}}i:10;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:5:"pager";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_pager";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:9:"tel_pager";s:4:"name";s:10:"tel_prefer";}}i:11;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"voice phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_voice";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:9:"tel_voice";s:4:"name";s:10:"tel_prefer";}}i:12;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:13:"message phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:7:"tel_msg";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"tel_msg";s:4:"name";s:10:"tel_prefer";}}i:13;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:9:"bbs phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:7:"tel_bbs";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:7:"tel_bbs";s:4:"name";s:10:"tel_prefer";}}i:14;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:11:"modem phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:9:"tel_modem";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:9:"tel_modem";s:4:"name";s:10:"tel_prefer";}}i:15;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:10:"isdn phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:8:"tel_isdn";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:8:"tel_isdn";s:4:"name";s:10:"tel_prefer";}}i:16;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:2:{s:4:"type";s:5:"label";s:5:"label";s:12:" Other Phone";}s:1:"C";a:3:{s:4:"type";s:4:"text";s:4:"size";s:2:"30";s:4:"name";s:6:"ophone";}s:1:"D";a:3:{s:4:"type";s:5:"radio";s:4:"size";s:6:"ophone";s:4:"name";s:10:"tel_prefer";}}i:17;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:1:{s:4:"type";s:5:"label";}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}i:18;a:4:{s:1:"A";a:1:{s:4:"type";s:5:"label";}s:1:"B";a:4:{s:4:"type";s:4:"hbox";s:4:"size";s:1:"1";s:4:"span";s:1:"3";i:1;a:3:{s:4:"type";s:6:"button";s:5:"label";s:2:"Ok";s:7:"onclick";s:36:"hidephones(this.form); return false;";}}s:1:"C";a:1:{s:4:"type";s:5:"label";}s:1:"D";a:1:{s:4:"type";s:5:"label";}}}s:4:"rows";i:18;s:4:"cols";i:4;s:4:"size";s:13:",,,editphones";s:7:"options";a:1:{i:3;s:10:"editphones";}}}','size' => ',,,editphones','style' => '.editphones{ 
position: fixed;
top: 50px;
left: 240px;
display:none;
border: 1px solid #000000;
background-color: #ffffff; 
}

.windowheader{
background-image:url(http://solomon/egroupware/phpgwapi/templates/idots/images/appbox-header-background.png);
background-repeat:repeat-x;
height: 20px;
border-spacing: 0px;
border-collapse:collapse;
border-bottom: #9c9c9c 1px solid; 
}','modified' => '1130581740',);

$templ_data[] = array('name' => 'addressbook.search','template' => '','lang' => '','group' => '0','version' => '','data' => 'a:1:{i:0;a:6:{s:4:"type";s:4:"grid";s:4:"data";a:2:{i:0;a:0:{}i:1;a:1:{s:1:"A";a:2:{s:4:"type";s:14:"advancedsearch";s:4:"name";s:4:"advs";}}}s:4:"rows";i:1;s:4:"cols";i:1;s:4:"size";s:4:"100%";s:7:"options";a:1:{i:0;s:4:"100%";}}}','size' => '100%','style' => '','modified' => '1130609044',);

