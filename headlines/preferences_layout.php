<?php
	$phpgw_info['flags'] = array(
		'currentapp' => 'headlines',
		'noheader'   => True,
		'nonavbar'   => True
	);
	include('../header.inc.php');

	if ($done)
	{
		$phpgw->redirect($phpgw->link('/headlines/index.php'));
	}
	else
	{
		$phpgw->common->phpgw_header();
		echo parse_navbar();
	}

	if ($submit)
	{
		$phpgw->preferences->change('headlines','headlines_layout');
		$phpgw->preferences->commit(True);
	}

	$phpgw->template->set_file(array(
		'layout1' => 'basic_sample.tpl',
		'layout2' => 'color_sample.tpl',
		'body'    => 'preferences_layout.tpl'
	));

	$phpgw->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
	$phpgw->template->set_var('action_url',$phpgw->link('/headlines/preferences_layout.php'));
	$phpgw->template->set_var('title',lang('Headlines layout'));
	$phpgw->template->set_var('action_label',lang('Submit'));
	$phpgw->template->set_var('done_label',lang('Done'));
	$phpgw->template->set_var('reset_label',lang('Reset'));

	$phpgw->template->set_var('template_label',lang('Choose layout'));

	if ($submit)
	{
		$selected[$headlines_layout] = ' selected';
	}
	else
	{
		$selected[$phpgw_info['user']['preferences']['headlines']['headlines_layout']] = ' selected';	
	}

	$s  = '<option value="basic"' . $selected['basic'] . '>' . lang('Basic') . '</option>';
	$s .= '<option value="color"' . $selected['color'] . '>' . lang('Color') . '</option>';
	$phpgw->template->set_var('template_options',$s);

	$phpgw->template->parse('layout_1','layout1');
	$phpgw->template->parse('layout_2','layout2');

	$phpgw->template->pfp('out','body');
	$phpgw->common->phpgw_footer();
