<?php

	$t = $phpgw->template;
	$t->set_file(array(
		'header' => 'header.tpl'
	));

	$t->set_var('lang_developer_tools',lang('Developer tools'));
	$t->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$t->set_var('lang_diary',lang('Diary'));
	$t->set_var('lang_sourceforge_project',lang('SF Project tracker'));
	$t->set_var('lang_changelog',lang('Changelogs'));
	$t->set_var('lang_language_management',lang('Language management'));

	$t->pfp('out','header');
	unset($t);
