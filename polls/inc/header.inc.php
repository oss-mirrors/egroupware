<?php

  if ($phpgw_info["flags"]["admin_header"]) {
     echo '<table border="0"><tr><td><a href="' . $phpgw->link("admin_addanswer.php")
        . '">Add answers</a></td><td><a href="' . $phpgw->link("admin_addquestion.php")
        . '">Add questions</a></td></tr></table><p>';
  }

