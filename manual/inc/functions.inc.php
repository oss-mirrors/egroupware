<?php
	function check_file($file)
	{
		if(is_file($file))
		{
			$file = str_replace(PHPGW_SERVER_ROOT,'',$file);
		}
		else
		{
			$file = '';
		}
		return $file;
	}

	function check_help_file($appname,$lang,$file)
	{
		$lang = strtoupper($lang);
		$help_file = check_file(PHPGW_SERVER_ROOT.'/'.$appname.'/help/'.$lang.'/'.$file);
		if($help_file == '' && strtoupper($lang) != 'EN')
		{
			$help_file = check_file(PHPGW_SERVER_ROOT.'/'.$appname.'/help/EN/'.$file);
		}
		return $help_file;
	}

	function display_manual_section($appname,$file)
	{
		$font = $GLOBALS['phpgw_info']['theme']['font'];
		$navbar = $GLOBALS['phpgw_info']['user']['preferences']['common']['navbar_format'];
		$lang = strtoupper($GLOBALS['phpgw_info']['user']['preferences']['common']['lang']);
		$GLOBALS['treemenu'][] = '..'.($navbar != 'text'?'<img src="'.$GLOBALS['phpgw']->common->image($appname,'navbar.gif').'" border="0" alt="'.ucwords($appname).'">':'').($navbar != 'icons'?'<font face="'.$font.'">'.lang($appname).'</font>':'').'|'.$GLOBALS['phpgw']->link('/'.$appname.'/help/index.php');

		$help_file = check_help_file($appname,$lang,$appname.'.php');
		if($help_file != '')
		{
			$GLOBALS['treemenu'][] = '...<font face="'.$font.'">'.lang('Overview').'</font>|'.$GLOBALS['phpgw']->link($help_file);
		}
		while(list($title,$filename) = each($file))
		{
			$help_file = check_help_file($appname,$lang,$filename);
			if($help_file != '')
			{
				$GLOBALS['treemenu'][] = '...<font face="'.$font.'">'.lang($title).'</font>|'.$GLOBALS['phpgw']->link($help_file);
			}
		}
	}

	function show_menu($expandlevels)
	{
		$menutree = CreateObject('phpgwapi.menutree','text');
		$menutree->set_lcs(300);

		$str  = '<table cellpadding="10" width="20%"><td>';
		$str .= '<font face="'.$GLOBALS['phpgw_info']['theme']['font'].'" size="2">';
		$str .= 'Note: Some of this information is out of date<br>';

		$GLOBALS['treemenu'] = Array();

		$GLOBALS['phpgw']->common->hook('manual',array('manual','preferences'));

		reset($GLOBALS['treemenu']);

		$str .= $menutree->showtree($GLOBALS['treemenu'],$expandlevels).'</td></table>';

		return $str;
	}
?>
