<?php
// eTemplates for Application 'infolog', generated by etemplate.dump() 2002-10-18 00:10

/* $Id$ */

$templ_data[] = array('name' => 'infolog.delete','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:6:{i:0;a:1:{s:2:\"h4\";s:3:\"100\";}i:1;a:1:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:16:\"InfoLog - Delete\";s:4:\"name\";s:9:\"appheader\";}}i:2;a:1:{s:1:\"A\";a:1:{s:4:\"type\";s:5:\"hrule\";}}i:3;a:1:{s:1:\"A\";a:4:{s:4:\"type\";s:8:\"template\";s:4:\"size\";s:4:\"main\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:18:\"infolog.index.rows\";}}i:4;a:1:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:45:\"Are you shure you want to delete this entry ?\";s:5:\"align\";s:6:\"center\";}}i:5;a:1:{s:1:\"A\";a:6:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:4:\"3,20\";s:5:\"align\";s:6:\"center\";i:1;a:5:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:12:\"Yes - Delete\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:6:\"delete\";s:4:\"help\";s:16:\"Delete the entry\";}i:2;a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:11:\"No - Cancel\";s:4:\"name\";s:6:\"cancel\";s:4:\"help\";s:22:\"Abort without deleting\";}i:3;a:1:{s:4:\"type\";s:5:\"label\";}}}}','size' => '100%,,0,,0,0','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034538934',);

$templ_data[] = array('name' => 'infolog.edit','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:13:{i:0;a:11:{s:1:\"A\";s:3:\"103\";s:1:\"B\";s:3:\"300\";s:1:\"C\";s:3:\"100\";s:2:\"c3\";s:2:\"th\";s:2:\"c4\";s:3:\"row\";s:2:\"c5\";s:3:\"row\";s:2:\"c6\";s:3:\"row\";s:2:\"c8\";s:2:\"th\";s:2:\"c9\";s:3:\"row\";s:3:\"c10\";s:3:\"row\";s:3:\"c11\";s:2:\"th\";}i:1;a:4:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"size\";s:1:\"b\";s:4:\"span\";s:12:\"3,headertext\";s:4:\"name\";s:9:\"appheader\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:10:\"Categories\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:4:\"cats\";s:4:\"help\";s:37:\"Edit or create categories for IngoLog\";}}i:2;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"hrule\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:4:\"Type\";}s:1:\"B\";a:5:{s:4:\"type\";s:6:\"select\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:9:\"info_type\";s:8:\"onchange\";s:1:\"1\";s:4:\"help\";s:46:\"Type of the log-entry: Note, Phonecall or ToDo\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:8:\"Category\";}s:1:\"B\";a:5:{s:4:\"type\";s:10:\"select-cat\";s:4:\"size\";s:4:\"None\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:8:\"info_cat\";s:4:\"help\";s:32:\"select a category for this entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:5;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Contact\";}s:1:\"B\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"40,64\";s:4:\"name\";s:9:\"info_from\";s:4:\"help\";s:80:\"Custom contact-information, leave emtpy to use information from most recent link\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:11:\"Phone/Email\";}s:1:\"D\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"40,64\";s:4:\"name\";s:9:\"info_addr\";s:4:\"help\";s:76:\"Custom contact-address, leave empty to use information from most recent link\";}}i:6;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Subject\";}s:1:\"B\";a:6:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"64,64\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:12:\"info_subject\";s:6:\"needed\";s:1:\"1\";s:4:\"help\";s:29:\"a short subject for the entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:7;a:4:{s:1:\"A\";a:5:{s:4:\"type\";s:3:\"tab\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:28:\"Description|Links|Delegation\";s:4:\"name\";s:28:\"description|links|delegation\";s:4:\"help\";s:78:\"longer textual description|Links of this entry|responsible user, priority, ...\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:8;a:4:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:21:\"Dates, Status, Access\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:9;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:9:\"Startdate\";}s:1:\"B\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:14:\"info_startdate\";s:4:\"help\";s:115:\"when should the ToDo or Phonecall be started, it shows up from that date in the filter open or own open (startpage)\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Enddate\";}s:1:\"D\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:12:\"info_enddate\";s:4:\"help\";s:49:\"til when should the ToDo or Phonecall be finished\";}}i:10;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Status\";}s:1:\"B\";a:3:{s:4:\"type\";s:6:\"select\";s:4:\"name\";s:11:\"info_status\";s:4:\"help\";s:12:\"@status_help\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Private\";}s:1:\"D\";a:4:{s:4:\"type\";s:8:\"checkbox\";s:4:\"size\";s:14:\"private,public\";s:4:\"name\";s:11:\"info_access\";s:4:\"help\";s:87:\"should this entry only be visible to you and people you grant privat access via the ACL\";}}i:11;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:5:\"Owner\";}s:1:\"B\";a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,2\";s:4:\"name\";s:10:\"info_owner\";s:8:\"readonly\";s:1:\"1\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:13:\"Last modified\";}s:1:\"D\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:17:\"info_datemodified\";s:8:\"readonly\";s:1:\"1\";}}i:12;a:4:{s:1:\"A\";a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:4:\"Save\";s:4:\"name\";s:4:\"save\";s:4:\"help\";s:16:\"Saves this entry\";}s:1:\"B\";a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:6:\"Cancel\";s:4:\"name\";s:6:\"cancel\";s:4:\"help\";s:31:\"leave without saveing the entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:6:\"Delete\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:6:\"delete\";s:4:\"help\";s:17:\"delete this entry\";}}}','size' => '100%','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034157948',);

$templ_data[] = array('name' => 'infolog.edit','template' => '','lang' => '','group' => '0','version' => '0.9.15.002','data' => 'a:13:{i:0;a:11:{s:1:\"A\";s:3:\"103\";s:1:\"B\";s:3:\"300\";s:1:\"C\";s:3:\"100\";s:2:\"c3\";s:2:\"th\";s:2:\"c4\";s:3:\"row\";s:2:\"c5\";s:3:\"row\";s:2:\"c6\";s:3:\"row\";s:2:\"c8\";s:2:\"th\";s:2:\"c9\";s:3:\"row\";s:3:\"c10\";s:3:\"row\";s:3:\"c11\";s:2:\"th\";}i:1;a:4:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"size\";s:1:\"b\";s:4:\"span\";s:12:\"3,headertext\";s:4:\"name\";s:9:\"appheader\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:10:\"Categories\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:4:\"cats\";s:4:\"help\";s:37:\"Edit or create categories for IngoLog\";}}i:2;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"hrule\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:4:\"Type\";}s:1:\"B\";a:5:{s:4:\"type\";s:6:\"select\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:9:\"info_type\";s:8:\"onchange\";s:1:\"1\";s:4:\"help\";s:46:\"Type of the log-entry: Note, Phonecall or ToDo\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:8:\"Category\";}s:1:\"B\";a:5:{s:4:\"type\";s:10:\"select-cat\";s:4:\"size\";s:4:\"None\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:8:\"info_cat\";s:4:\"help\";s:32:\"select a category for this entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:5;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Contact\";}s:1:\"B\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"40,64\";s:4:\"name\";s:9:\"info_from\";s:4:\"help\";s:80:\"Custom contact-information, leave emtpy to use information from most recent link\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:11:\"Phone/Email\";}s:1:\"D\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"40,64\";s:4:\"name\";s:9:\"info_addr\";s:4:\"help\";s:76:\"Custom contact-address, leave empty to use information from most recent link\";}}i:6;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Subject\";}s:1:\"B\";a:6:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"64,64\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:12:\"info_subject\";s:6:\"needed\";s:1:\"1\";s:4:\"help\";s:29:\"a short subject for the entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:7;a:4:{s:1:\"A\";a:5:{s:4:\"type\";s:3:\"tab\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:28:\"Description|Links|Delegation\";s:4:\"name\";s:28:\"description|links|delegation\";s:4:\"help\";s:78:\"longer textual description|Links of this entry|responsible user, priority, ...\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:8;a:4:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:21:\"Dates, Status, Access\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}}i:9;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:9:\"Startdate\";}s:1:\"B\";a:4:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:14:\"info_startdate\";s:4:\"help\";s:115:\"when should the ToDo or Phonecall be started, it shows up from that date in the filter open or own open (startpage)\";}i:2;a:4:{s:4:\"type\";s:8:\"checkbox\";s:5:\"label\";s:8:\"%s Today\";s:4:\"name\";s:9:\"set_today\";s:4:\"help\";s:21:\"check to set startday\";}}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Enddate\";}s:1:\"D\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:12:\"info_enddate\";s:4:\"help\";s:49:\"til when should the ToDo or Phonecall be finished\";}}i:10;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Status\";}s:1:\"B\";a:3:{s:4:\"type\";s:6:\"select\";s:4:\"name\";s:11:\"info_status\";s:4:\"help\";s:12:\"@status_help\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Private\";}s:1:\"D\";a:4:{s:4:\"type\";s:8:\"checkbox\";s:4:\"size\";s:14:\"private,public\";s:4:\"name\";s:11:\"info_access\";s:4:\"help\";s:87:\"should this entry only be visible to you and people you grant privat access via the ACL\";}}i:11;a:4:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:5:\"Owner\";}s:1:\"B\";a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,2\";s:4:\"name\";s:10:\"info_owner\";s:8:\"readonly\";s:1:\"1\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:13:\"Last modified\";}s:1:\"D\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:17:\"info_datemodified\";s:8:\"readonly\";s:1:\"1\";}}i:12;a:4:{s:1:\"A\";a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:4:\"Save\";s:4:\"name\";s:4:\"save\";s:4:\"help\";s:16:\"Saves this entry\";}s:1:\"B\";a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:6:\"Cancel\";s:4:\"name\";s:6:\"cancel\";s:4:\"help\";s:31:\"leave without saveing the entry\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:6:\"Delete\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:6:\"delete\";s:4:\"help\";s:17:\"delete this entry\";}}}','size' => '100%','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034875600',);

$templ_data[] = array('name' => 'infolog.edit.delegation','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:7:{i:0;a:8:{s:1:\"A\";s:3:\"100\";s:2:\"h6\";s:3:\"120\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:3:\"row\";s:2:\"c3\";s:2:\"th\";s:2:\"c4\";s:3:\"row\";s:2:\"c5\";s:3:\"row\";s:2:\"c6\";s:3:\"row\";}i:1;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:8:\"Priority\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:2;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:8:\"Priority\";}s:1:\"B\";a:3:{s:4:\"type\";s:6:\"select\";s:4:\"name\";s:8:\"info_pri\";s:4:\"help\";s:31:\"select a priority for this task\";}}i:3;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:10:\"Delegation\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:11:\"Responsible\";}s:1:\"B\";a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:5:\"Owner\";s:4:\"name\";s:16:\"info_responsible\";s:4:\"help\";s:66:\"select a responsible user: a person you want to delegate this task\";}}i:5;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Confirm\";}s:1:\"B\";a:3:{s:4:\"type\";s:6:\"select\";s:4:\"name\";s:12:\"info_confirm\";s:4:\"help\";s:87:\"do you want a confirmation of the responsible on: accepting, finishing the task or both\";}}i:6;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%','style' => '','modified' => '1034160051',);

$templ_data[] = array('name' => 'infolog.edit.description','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:3:{i:0;a:3:{s:1:\"A\";s:3:\"100\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:3:\"row\";}i:1;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:11:\"Description\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:2;a:2:{s:1:\"A\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"B\";a:5:{s:4:\"type\";s:8:\"textarea\";s:4:\"size\";s:5:\"15,80\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:8:\"info_des\";s:4:\"help\";s:44:\"enter a textual description of the log-entry\";}}}','size' => '100%,,0','style' => '','modified' => '1034103624',);

$templ_data[] = array('name' => 'infolog.edit.links','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:7:{i:0;a:8:{s:1:\"A\";s:3:\"100\";s:2:\"h6\";s:3:\"112\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:3:\"row\";s:2:\"c3\";s:3:\"row\";s:2:\"c4\";s:2:\"th\";s:2:\"c5\";s:3:\"row\";s:2:\"c6\";s:11:\"row_off,top\";}i:1;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:16:\"Custom regarding\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:2;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Contact\";}s:1:\"B\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"64,64\";s:4:\"name\";s:9:\"info_from\";s:4:\"help\";s:66:\"enter a custom contact, leave empty if linked entry should be used\";}}i:3;a:2:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:11:\"Phone/Email\";}s:1:\"B\";a:4:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:5:\"64,64\";s:4:\"name\";s:9:\"info_addr\";s:4:\"help\";s:70:\"enter a custom phone/email, leave empty if linked entry should be used\";}}i:4;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:5:\"Links\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:5;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:6:\"linkto\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:7:\"link_to\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:6;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:8:\"linklist\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:5:\"links\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%','style' => '','modified' => '1034106791',);

$templ_data[] = array('name' => 'infolog.edit.links','template' => '','lang' => '','group' => '0','version' => '0.9.15.002','data' => 'a:5:{i:0;a:6:{s:1:\"A\";s:3:\"100\";s:2:\"h4\";s:3:\"164\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:3:\"row\";s:2:\"c3\";s:2:\"th\";s:2:\"c4\";s:11:\"row_off,top\";}i:1;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:16:\"Create new links\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:2;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:7:\"link-to\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:7:\"link_to\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:14:\"Existing links\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:2:{s:1:\"A\";a:3:{s:4:\"type\";s:9:\"link-list\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:7:\"link_to\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%','style' => '','modified' => '1034157822',);

$templ_data[] = array('name' => 'infolog.index','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:5:{i:0;a:1:{s:1:\"A\";s:3:\"90%\";}i:1;a:5:{s:1:\"A\";a:5:{s:4:\"type\";s:5:\"label\";s:4:\"size\";s:1:\"b\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:7:\"InfoLog\";s:4:\"name\";s:9:\"appheader\";}s:1:\"B\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:4:\"Add:\";}s:1:\"C\";a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"task\";s:5:\"label\";s:4:\"ToDo\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:9:\"add[task]\";s:4:\"help\";s:14:\"Add a new ToDo\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:5:\"phone\";s:5:\"label\";s:9:\"Phonecall\";s:4:\"name\";s:10:\"add[phone]\";s:4:\"help\";s:19:\"Add a new Phonecall\";}s:1:\"E\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"note\";s:5:\"label\";s:4:\"Note\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:14:\"Add a new Note\";}}i:2;a:5:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"hrule\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:5:{s:1:\"A\";a:4:{s:4:\"type\";s:9:\"nextmatch\";s:4:\"size\";s:18:\"infolog.index.rows\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:2:\"nm\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:5:{s:1:\"A\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:3:\"Add\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:15:\"Add a new Entry\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%,,0,,0,0','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034338369',);

$templ_data[] = array('name' => 'infolog.index','template' => '','lang' => '','group' => '0','version' => '0.9.15.002','data' => 'a:6:{i:0;a:2:{s:1:\"A\";s:3:\"90%\";s:2:\"h3\";s:7:\",!@main\";}i:1;a:5:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:7:\"InfoLog\";s:4:\"name\";s:9:\"appheader\";}s:1:\"B\";a:3:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:4:\"Add:\";}s:1:\"C\";a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"task\";s:5:\"label\";s:4:\"ToDo\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:9:\"add[task]\";s:4:\"help\";s:14:\"Add a new ToDo\";}s:1:\"D\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:5:\"phone\";s:5:\"label\";s:9:\"Phonecall\";s:4:\"name\";s:10:\"add[phone]\";s:4:\"help\";s:19:\"Add a new Phonecall\";}s:1:\"E\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"note\";s:5:\"label\";s:4:\"Note\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:14:\"Add a new Note\";}}i:2;a:5:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"hrule\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:5:{s:1:\"A\";a:4:{s:4:\"type\";s:8:\"template\";s:4:\"size\";s:4:\"main\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:4:\"rows\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:5:{s:1:\"A\";a:4:{s:4:\"type\";s:9:\"nextmatch\";s:4:\"size\";s:18:\"infolog.index.rows\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:2:\"nm\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}i:5;a:5:{s:1:\"A\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"span\";s:3:\"all\";s:5:\"label\";s:3:\"Add\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:15:\"Add a new Entry\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"D\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"E\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%,,0,,0,0','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034538966',);

$templ_data[] = array('name' => 'infolog.index','template' => '','lang' => '','group' => '0','version' => '0.9.15.003','data' => 'a:6:{i:0;a:2:{s:1:\"A\";s:3:\"95%\";s:2:\"h3\";s:7:\",!@main\";}i:1;a:3:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:7:\"InfoLog\";s:4:\"name\";s:9:\"appheader\";}s:1:\"B\";a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:11:\",headertext\";s:5:\"label\";s:4:\"Add:\";s:5:\"align\";s:5:\"right\";}s:1:\"C\";a:5:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:1:\"3\";i:1;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"task\";s:5:\"label\";s:4:\"ToDo\";s:5:\"align\";s:5:\"right\";s:4:\"name\";s:9:\"add[task]\";s:4:\"help\";s:14:\"Add a new ToDo\";}i:2;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:5:\"phone\";s:5:\"label\";s:9:\"Phonecall\";s:4:\"name\";s:10:\"add[phone]\";s:4:\"help\";s:19:\"Add a new Phonecall\";}i:3;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:4:\"note\";s:5:\"label\";s:4:\"Note\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:14:\"Add a new Note\";}}}i:2;a:3:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"hrule\";s:4:\"span\";s:3:\"all\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}}i:3;a:3:{s:1:\"A\";a:4:{s:4:\"type\";s:8:\"template\";s:4:\"size\";s:4:\"main\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:18:\"infolog.index.rows\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}}i:4;a:3:{s:1:\"A\";a:4:{s:4:\"type\";s:9:\"nextmatch\";s:4:\"size\";s:20:\"infolog.index.rows,1\";s:4:\"span\";s:3:\"all\";s:4:\"name\";s:2:\"nm\";}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}}i:5;a:3:{s:1:\"A\";a:5:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:1:\"2\";s:4:\"span\";s:3:\"all\";i:1;a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:3:\"Add\";s:4:\"name\";s:9:\"add[note]\";s:4:\"help\";s:15:\"Add a new Entry\";}i:2;a:4:{s:4:\"type\";s:6:\"button\";s:5:\"label\";s:6:\"Cancel\";s:4:\"name\";s:6:\"cancel\";s:4:\"help\";s:17:\"Back to main list\";}}s:1:\"B\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"C\";a:1:{s:4:\"type\";s:5:\"label\";}}}','size' => '100%,,0,,0,0','style' => '.headertext { color: black; font-size: 120%; }','modified' => '1034772949',);

$templ_data[] = array('name' => 'infolog.index.rows','template' => '','lang' => '','group' => '0','version' => '0.9.15.001','data' => 'a:3:{i:0;a:9:{s:1:\"A\";s:2:\"2%\";s:1:\"B\";s:2:\"4%\";s:1:\"D\";s:2:\"8%\";s:1:\"E\";s:2:\"8%\";s:1:\"F\";s:2:\"8%\";s:1:\"G\";s:2:\"3%\";s:1:\"H\";s:2:\"3%\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:3:\"row\";}i:1;a:8:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:4:\"Type\";}s:1:\"B\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Status\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Subject\";}s:1:\"D\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Startdate Enddate\";}s:1:\"E\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Owner Responsible\";}s:1:\"F\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:12:\"last changed\";}s:1:\"G\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:3:\"Sub\";}s:1:\"H\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Action\";}}i:2;a:8:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:20:\"$row_cont[info_type]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:17:\"${row}[info_type]\";}s:1:\"B\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:22:\"$row_cont[info_status]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:19:\"${row}[info_status]\";}s:1:\"C\";a:3:{s:4:\"type\";s:5:\"label\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:20:\"${row}[info_subject]\";}s:1:\"D\";a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:22:\"${row}[info_startdate]\";s:8:\"readonly\";s:1:\"1\";}s:1:\"E\";a:4:{s:4:\"type\";s:8:\"template\";s:4:\"size\";s:4:\"$row\";s:4:\"name\";s:5:\"owner\";s:8:\"readonly\";s:1:\"1\";}s:1:\"F\";a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:11:\",accounts,0\";s:4:\"name\";s:25:\"${row}[info_lastmodified]\";s:8:\"readonly\";s:1:\"1\";}s:1:\"G\";a:1:{s:4:\"type\";s:5:\"label\";}s:1:\"H\";a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:8:\"edit.gif\";s:5:\"label\";s:4:\"Edit\";s:4:\"name\";s:24:\"edit[$row_cont[info_id]]\";s:4:\"help\";s:14:\"Edit the entry\";}}}','size' => '','style' => '','modified' => '1034352181',);

$templ_data[] = array('name' => 'infolog.index.rows','template' => '','lang' => '','group' => '0','version' => '0.9.15.002','data' => 'a:3:{i:0;a:9:{s:1:\"A\";s:2:\"2%\";s:1:\"B\";s:2:\"4%\";s:1:\"D\";s:2:\"8%\";s:1:\"E\";s:2:\"8%\";s:1:\"F\";s:2:\"8%\";s:1:\"G\";s:14:\"3%,@no_actions\";s:1:\"H\";s:14:\"3%,@no_actions\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:7:\"row,top\";}i:1;a:8:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:4:\"Type\";}s:1:\"B\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Status\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Subject\";}s:1:\"D\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Startdate Enddate\";}s:1:\"E\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Owner Responsible\";}s:1:\"F\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:12:\"last changed\";}s:1:\"G\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:3:\"Sub\";}s:1:\"H\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Action\";}}i:2;a:8:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:20:\"$row_cont[info_type]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:17:\"${row}[info_type]\";}s:1:\"B\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:22:\"$row_cont[info_status]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:19:\"${row}[info_status]\";}s:1:\"C\";a:5:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"3,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"html\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:15:\"${row}[subject]\";}i:2;a:3:{s:4:\"type\";s:5:\"label\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:16:\"${row}[info_des]\";}i:3;a:2:{s:4:\"type\";s:4:\"html\";s:4:\"name\";s:17:\"${row}[filelinks]\";}}s:1:\"D\";a:4:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:22:\"${row}[info_startdate]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:3:{s:4:\"type\";s:4:\"html\";s:4:\"name\";s:15:\"${row}[enddate]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"E\";a:5:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";s:4:\"span\";s:23:\",$row_cont[info_access]\";i:1;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:18:\"${row}[info_owner]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:24:\"${row}[info_responsible]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"F\";a:4:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:25:\"${row}[info_datemodified]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:21:\"${row}[info_modifier]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"G\";a:6:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"3,0,0\";s:5:\"align\";s:6:\"center\";i:1;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:7:\"new.gif\";s:5:\"label\";s:7:\"Add sub\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:22:\"sp[$row_cont[info_id]]\";s:4:\"help\";s:46:\"Add a new sub-task, -note, -call to this entry\";}i:2;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:8:\"view.gif\";s:5:\"label\";s:9:\"View subs\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:24:\"view[$row_cont[info_id]]\";s:4:\"help\";s:27:\"View all subs of this entry\";}i:3;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:10:\"parent.gif\";s:5:\"label\";s:11:\"View parent\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:31:\"view[$row_cont[info_id_parent]]\";s:4:\"help\";s:46:\"View the parent of this entry and all his subs\";}}s:1:\"H\";a:5:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:1:\"3\";i:1;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:8:\"edit.gif\";s:5:\"label\";s:4:\"Edit\";s:4:\"name\";s:24:\"edit[$row_cont[info_id]]\";s:4:\"help\";s:15:\"Edit this entry\";}i:2;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:10:\"delete.gif\";s:5:\"label\";s:6:\"Delete\";s:4:\"name\";s:26:\"delete[$row_cont[info_id]]\";s:4:\"help\";s:17:\"Delete this entry\";}i:3;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:11:\"addfile.gif\";s:5:\"label\";s:8:\"Add file\";s:4:\"name\";s:24:\"file[$row_cont[info_id]]\";s:8:\"disabled\";s:1:\"1\";s:4:\"help\";s:13:\"Attach a file\";}}}}','size' => '','style' => '.low,.pri_low_done { color:#606060; }
.normal,.pri_normal_done { color:black }
.high { color:#cc0000; } .high_done { color:#800000; }
.urgent { color:#ff00ff; } .urgent_done { color:#800080; }
.overdue { color:#cc0000; font-weight:bold; }
.private { font-style:italic; }
.note { color:#808080; font-style:italic; }
','modified' => '1034805106',);

$templ_data[] = array('name' => 'infolog.index.rows','template' => '','lang' => '','group' => '0','version' => '0.9.15.003','data' => 'a:3:{i:0;a:9:{s:1:\"A\";s:2:\"2%\";s:1:\"B\";s:2:\"4%\";s:1:\"D\";s:2:\"8%\";s:1:\"E\";s:2:\"8%\";s:1:\"F\";s:2:\"8%\";s:1:\"G\";s:14:\"3%,@no_actions\";s:1:\"H\";s:14:\"3%,@no_actions\";s:2:\"c1\";s:2:\"th\";s:2:\"c2\";s:7:\"row,top\";}i:1;a:8:{s:1:\"A\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:4:\"Type\";}s:1:\"B\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Status\";}s:1:\"C\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:7:\"Subject\";}s:1:\"D\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Startdate Enddate\";}s:1:\"E\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:17:\"Owner Responsible\";}s:1:\"F\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:12:\"last changed\";}s:1:\"G\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:3:\"Sub\";}s:1:\"H\";a:2:{s:4:\"type\";s:5:\"label\";s:5:\"label\";s:6:\"Action\";}}i:2;a:8:{s:1:\"A\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:20:\"$row_cont[info_type]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:17:\"${row}[info_type]\";}s:1:\"B\";a:4:{s:4:\"type\";s:5:\"image\";s:5:\"label\";s:22:\"$row_cont[info_status]\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:19:\"${row}[info_status]\";}s:1:\"C\";a:6:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"4,0,0\";i:1;a:6:{s:4:\"type\";s:5:\"label\";s:4:\"size\";s:25:\"b,@${row}[info_link_view]\";s:5:\"label\";s:23:\"%s $row_cont[info_addr]\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:17:\"${row}[info_from]\";s:4:\"help\";s:41:\"view this linked entry in its application\";}i:2;a:4:{s:4:\"type\";s:5:\"label\";s:4:\"span\";s:21:\",$row_cont[sub_class]\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:20:\"${row}[info_subject]\";}i:3;a:3:{s:4:\"type\";s:5:\"label\";s:7:\"no_lang\";s:1:\"1\";s:4:\"name\";s:16:\"${row}[info_des]\";}i:4;a:2:{s:4:\"type\";s:4:\"html\";s:4:\"name\";s:17:\"${row}[filelinks]\";}}s:1:\"D\";a:4:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:22:\"${row}[info_startdate]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:4:{s:4:\"type\";s:4:\"date\";s:4:\"span\";s:21:\",$row_cont[end_class]\";s:4:\"name\";s:20:\"${row}[info_enddate]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"E\";a:4:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:18:\"${row}[info_owner]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:24:\"${row}[info_responsible]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"F\";a:4:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"2,0,0\";i:1;a:3:{s:4:\"type\";s:4:\"date\";s:4:\"name\";s:25:\"${row}[info_datemodified]\";s:8:\"readonly\";s:1:\"1\";}i:2;a:4:{s:4:\"type\";s:14:\"select-account\";s:4:\"size\";s:3:\",,0\";s:4:\"name\";s:21:\"${row}[info_modifier]\";s:8:\"readonly\";s:1:\"1\";}}s:1:\"G\";a:6:{s:4:\"type\";s:4:\"vbox\";s:4:\"size\";s:5:\"3,0,0\";s:5:\"align\";s:6:\"center\";i:1;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:7:\"new.gif\";s:5:\"label\";s:7:\"Add sub\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:22:\"sp[$row_cont[info_id]]\";s:4:\"help\";s:46:\"Add a new sub-task, -note, -call to this entry\";}i:2;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:8:\"view.gif\";s:5:\"label\";s:9:\"View subs\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:24:\"view[$row_cont[info_id]]\";s:4:\"help\";s:27:\"View all subs of this entry\";}i:3;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:10:\"parent.gif\";s:5:\"label\";s:11:\"View parent\";s:5:\"align\";s:6:\"center\";s:4:\"name\";s:31:\"view[$row_cont[info_id_parent]]\";s:4:\"help\";s:46:\"View the parent of this entry and all his subs\";}}s:1:\"H\";a:5:{s:4:\"type\";s:4:\"hbox\";s:4:\"size\";s:1:\"3\";i:1;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:8:\"edit.gif\";s:5:\"label\";s:4:\"Edit\";s:4:\"name\";s:24:\"edit[$row_cont[info_id]]\";s:4:\"help\";s:15:\"Edit this entry\";}i:2;a:5:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:10:\"delete.gif\";s:5:\"label\";s:6:\"Delete\";s:4:\"name\";s:26:\"delete[$row_cont[info_id]]\";s:4:\"help\";s:17:\"Delete this entry\";}i:3;a:6:{s:4:\"type\";s:6:\"button\";s:4:\"size\";s:11:\"addfile.gif\";s:5:\"label\";s:8:\"Add file\";s:4:\"name\";s:24:\"file[$row_cont[info_id]]\";s:8:\"disabled\";s:1:\"1\";s:4:\"help\";s:13:\"Attach a file\";}}}}','size' => '','style' => '.low,.low_done,.normal,.normal_done,.high,.high_done,.urgent,.urgent_done { font-weight: bold; }
.low,.low_done { color:#606060; }
.normal,.normal_done { color:black }
.high { color:#cc0000; } 
.high_done { color:#800000; }
.urgent { color:#ff00ff; } 
.urgent_done { color:#800080; }
.overdue { color:#cc0000; font-weight:bold; }
.private { font-style:italic; }
','modified' => '1034890607',);

