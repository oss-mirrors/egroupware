<?php

  function show_menu($expandlevels) {
    global $phpgw, $treemenu;

    $treemenu = '';

    $menutree = CreateObject('phpgwapi.menutree','F');

    $str = '<table cellpadding="10" width="20%"><td>';
    $str .= '<font face="'.$phpgw_info['theme']['font'].'" size="2">';
    $str .= 'Note: Some of this information is out of date<br>';

    $phpgw->common->hook('',array('manual'));

    $str .= $menutree->showtree($treemenu,$expandlevels);

    $str .= '</td></table>';

    return $str;
  }
?>
