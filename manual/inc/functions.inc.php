<?php

  function show_menu($expandlevels) {
    global $phpgw, $treemenu;

    $menutree = CreateObject('phpgwapi.menutree','F');
    $menutree->last_column_size = 300;

    $str = '<table cellpadding="10" width="20%"><td>';
    $str .= '<font face="'.$phpgw_info['theme']['font'].'" size="2">';
    $str .= 'Note: Some of this information is out of date<br>';

	$treemenu = Array();

    $phpgw->common->hook('',array('manual'));

    $str .= $menutree->showtree($treemenu,$expandlevels);

    $str .= '</td></table>';

    return $str;
  }
?>
