<?php

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
