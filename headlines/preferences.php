<?php
  /**************************************************************************\
  * phpGroupWare - headlines preferences                                     *
  * http://www.phpgroupware.org                                              *
  * Stephen Brown <steve@dataclarity.net>                                    *
  *                                                                          *
  * phpGroupWare by Joseph Engo <jengo@phpgroupware.org>                     *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);

  $phpgw_info["flags"]["currentapp"] = "preferences";
  include("../header.inc.php");
  if ($phpgw_info["user"]["permissions"]["anonymous"]) {
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/"));
     exit;
  }

// Are we working on an actual user or the default?
if (isset($editDefault) && $editDefault) {
    $con = 0;
} else {
    $con = $phpgw_info["user"]["con"];
}

if (! $submit) {
   $phpgw->common->header();
   $phpgw->common->navbar();

    ?>
<table align="center">
<tr><td> <?php  
   if ($con) {
      echo lang("Select the sites from which you would like to see headlines.")."<br>";
      echo lang("If you don not select any the default sites, shown in bold, will be displayed.");
   } else {
      echo lang("Select the sites you would like users to see by default.");
   }
?>
<br>
   <form method="POST" action="<?php echo $phpgw->link($PHP_SELF,($con)?"":"editDefault=1"); ?>">
    <table border="0">
<?php
 	// If MySQL only had sub-queries, this would be much cleaner..

        // grab the default sites
        $qstr = "select site from users_headlines where owner=0";
        $phpgw->db->query($qstr);
 
	while ($phpgw->db->next_record()) {
	  $default_sites[$phpgw->db->f(0)] = true;
        }

	// The sites this users has
        $qstr = "select site from users_headlines where owner=".$con;
        $phpgw->db->query($qstr);
 
	while ($phpgw->db->next_record()) {
	  $my_sites[$phpgw->db->f(0)] = true;
        }

        // Now all the sites available
	$qstr = "select con,display,base_url";
	$qstr .=" from news_site";
	$phpgw->db->query($qstr);
	if ($phpgw->db->num_rows() == 0) {
          $error .= "<br>" . lang("The headlines database has not been installed");
        }
	while ($phpgw->db->next_record()) {
	  $site = $phpgw->db->f(0);
	  $name = $phpgw->db->f(1);
	  $url = $phpgw->db->f(2);
          // If it is a defualt site, show it in bold.
          if ($default_sites[$site]) {
            echo "<tr><td><strong><a href='$url'>$name</a></strong></td>\t";
          } else {
            echo "<tr><td><a href='$url'>$name</a></td>\t";
          }
	  echo "  <td><input type='checkbox'";
	  if ($my_sites[$site]) {
		echo " checked ";
	  }
	  echo " name='sites[]' value='".$site."'";
	  echo "></td></tr>\n";
	}

?>
     <tr>
       <td colspan="2">
        <input type="submit" name="submit" value="<?php echo lang("change"); ?>">
       </td>
     </tr>
    </table>
   </form>
</td></tr>
</table>
<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
} else {
   // update DB
   $phpgw->db->query("DELETE from users_headlines WHERE owner=".$con);
   while (sizeof($sites) && list(,$site) = each($sites)) {
            $qstr =  "insert into users_headlines(owner,site) values(";
            $qstr .= $con.",";
            $qstr .= $site.")";
            $phpgw->db->query($qstr);
   }
   if ($error) {
      $phpgw->common->header();
      $phpgw->common->navbar();
      echo "<p><br>$error</p>";
      include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
      exit;
   }

   if ($con) {
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"]
	. "/preferences/","cd=30"));
   } else {
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"]
	. "/admin/","cd=30"));
   }
}
?>
