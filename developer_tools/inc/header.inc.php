<?php

	$t = $phpgw->template;
	$t->set_file(array(
		'header' => 'header.tpl'
	));

	$t->set_var('lang_developer_tools',lang('Developer tools'));
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('link_diary',lang('Diary'));
	$t->set_var('link_sourceforge_project','<a href="' . $phpgw->link('/index.php','menuaction=developer_tools.uisf_project_tracker.display_tracker') . '">' . lang('SF Project tracker') . '</a>');
	$t->set_var('link_changelog',lang('Changelogs'));
	$t->set_var('link_language_management',lang('Language management'));
	$t->set_var('link_preferences','<a href="' . $phpgw->link('/preferences/index.php#developer_tools') . '">' . lang('Preferences') . '</a>');

	$t->pfp('out','header');
	unset($t);
