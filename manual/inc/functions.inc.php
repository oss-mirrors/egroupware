<?php

	function display_manual_section($appname,$file)
	{
		global $phpgw, $phpgw_info, $treemenu;
		$font = $phpgw_info['theme']['font'];
		$navbar = $phpgw_info['user']['preferences']['common']['navbar_format'];
		$lang = strtoupper($phpgw_info['user']['preferences']['common']['lang']);
		$treemenu[] = '..'.($navbar != 'text'?'<img src="'.$phpgw->common->image($appname,'navbar.gif').'" border="0" alt="'.ucwords($appname).'">':'').($navbar != 'icons'?'<font face="'.$font.'">'.lang($appname).'</font>':'').'|'.$phpgw->link('/'.$appname.'/help/index.php');

		$help_file = check_help_file($appname,$lang,$appname.'.php');
		if($help_file != '')
		{
			$treemenu[] = '...<font face="'.$font.'">'.lang('Overview').'</font>|'.$phpgw->link($help_file);
		}
		while(list($title,$filename) = each($file))
		{
			$help_file = check_help_file($appname,$lang,$filename);
			if($help_file != '')
			{
				$treemenu[] = '...<font face="'.$font.'">'.lang($title).'</font>|'.$phpgw->link($help_file);
			}
		}
	}

  function show_menu($expandlevels) {
    global $phpgw, $treemenu;

    $menutree = CreateObject('phpgwapi.menutree','F');
    $menutree->last_column_size = 300;

    $str = '<table cellpadding="10" width="20%"><td>';
    $str .= '<font face="'.$phpgw_info['theme']['font'].'" size="2">';
    $str .= 'Note: Some of this information is out of date<br>';

	$treemenu = Array();

    $phpgw->common->hook('',array('manual','preferences'));

    $str .= $menutree->showtree($treemenu,$expandlevels);

    $str .= '</td></table>';

    return $str;
  }

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
		if($help_file == '')
		{
			$help_file = check_file(PHPGW_SERVER_ROOT.'/'.$appname.'/help/EN/'.$file);
		}
		return $help_file;
	}
?>
