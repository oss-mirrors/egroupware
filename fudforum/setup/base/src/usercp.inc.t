<?php
/***************************************************************************
* copyright            : (C) 2001-2003 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it 
* under the terms of the GNU General Public License as published by the 
* Free Software Foundation; either version 2 of the License, or 
* (at your option) any later version.
***************************************************************************/

if ($GLOBALS['fudh_uopt'] & 524288 || $GLOBALS['fudh_uopt'] & 1048576) {
	if ($GLOBALS['fudh_uopt'] & 1048576) {
		$GLOBALS['adm_file']['{TEMPLATE: admin_control_panel}'] = 'adm/admglobal.php?'._rsid;
		if ($GLOBALS['FUD_OPT_1'] & 32 && ($avatar_count = q_singleval("SELECT count(*) FROM {SQL_TABLE_PREFIX}users WHERE users_opt>=16777216 AND (users_opt & 16777216) > 0"))) {
			$GLOBALS['adm_file']['{TEMPLATE: custom_avatar_queue}'] = 'adm/admapprove_avatar.php?'._rsid;
		}
		if ($report_count = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}msg_report')) {
			$GLOBALS['adm_file']['{TEMPLATE: reported_msgs}'] = '{TEMPLATE: reported_msgs_lnk}';
		}
		if ($thr_exchc = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}thr_exchange')) {
			$GLOBALS['adm_file']['{TEMPLATE: thr_exch}'] = '{TEMPLATE: thr_exch_lnk}';
		}
		$q_limit = '';
	} else {
		if ($report_count = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}msg_report mr INNER JOIN {SQL_TABLE_PREFIX}msg m ON mr.msg_id=m.id INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id INNER JOIN {SQL_TABLE_PREFIX}mod mm ON t.forum_id=mm.forum_id AND mm.user_id='.(int)$GLOBALS['phpgw_info']['user']['account_id'])) {
			$GLOBALS['adm_file']['{TEMPLATE: reported_msgs}'] = '{TEMPLATE: reported_msgs_lnk}';
		}
		if ($thr_exchc = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}thr_exchange te INNER JOIN {SQL_TABLE_PREFIX}mod m ON m.user_id='.(int)$GLOBALS['phpgw_info']['user']['account_id'].' AND te.frm=m.forum_id')) {
			$GLOBALS['adm_file']['{TEMPLATE: thr_exch}'] = '{TEMPLATE: thr_exch_lnk}';
		}
		$q_limit = ' INNER JOIN {SQL_TABLE_PREFIX}mod mm ON f.id=mm.forum_id AND mm.user_id='.(int)$GLOBALS['phpgw_info']['user']['account_id'];
	}
	if ($approve_count = q_singleval("SELECT count(*) FROM {SQL_TABLE_PREFIX}msg m INNER JOIN {SQL_TABLE_PREFIX}thread t ON m.thread_id=t.id INNER JOIN {SQL_TABLE_PREFIX}forum f ON t.forum_id=f.id ".$q_limit." WHERE m.apr=0 AND (f.forum_opt>=2 AND (f.forum_opt & 2) > 0)")) {
		$GLOBALS['adm_file']['{TEMPLATE: mod_que}'] = '{TEMPLATE: mod_que}';
	}
}
if ($GLOBALS['fudh_uopt'] & 1048576 || $usr->group_leader_list) {
	$GLOBALS['adm_file']['{TEMPLATE: group_mgr}'] = '{TEMPLATE: group_mgr_lnk}';
}

$GLOBALS['usr_file']['{TEMPLATE: profile}'] = '{TEMPLATE: register_lnk}';
if ($GLOBALS['FUD_OPT_1'] & 1024) {
	$c = q_singleval('SELECT count(*) FROM {SQL_TABLE_PREFIX}pmsg WHERE duser_id='.(int)$GLOBALS['phpgw_info']['user']['account_id'].' AND fldr=1 AND read_stamp=0');
	$GLOBALS['usr_file'][($c ? '{TEMPLATE: private_msg_empty}' : '{TEMPLATE: private_msg_unread}')] = '{TEMPLATE: private_msg_lnk}';
}
if ($GLOBALS['FUD_OPT_1'] & 4194304 || $GLOBALS['fudh_uopt'] & 1048576) {
	$GLOBALS['usr_file']['{TEMPLATE: member_search}'] = '{TEMPLATE: member_search_lnk}';
}
if ($GLOBALS['FUD_OPT_1'] & 16777216) {
	$GLOBALS['usr_file']['{TEMPLATE: uc_search}'] = '{TEMPLATE: usercp_lnk}';
}
$GLOBALS['usr_file']['{TEMPLATE: uc_faq}'] = '{TEMPLATE: usercp_lnk2}';
$GLOBALS['usr_file']['{TEMPLATE: uc_home}'] = '{TEMPLATE: usercp_lnk3}';
?>