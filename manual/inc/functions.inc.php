<?php

  function show_menu($expandlevels) {
    global $phpgw;

    $str = "<table cellpadding=\"10\" width=\"20%\"><td>";
    $str .= "<font face=\"Arial, Helvetica, san-serif\" size=\"2\">";
    $str .= "Note: Some of this information is out of date<br>";

    $str .= $phpgw->utilities->menutree->showtree("mymenu.txt",$expandlevels);

    $str .= "</td></table>";

    return $str;
  }
?>
