<?php
  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);

  $phpgw_info["flags"]["currentapp"] = "preferences";
  include("../header.inc.php");
  if (! $submit) {
    $phpgw->common->header();
    $phpgw->common->navbar();
?>
    <form method="POST" action="<?php echo $phpgw->link($PHP_SELF); ?>">
      <table>
      <tr><td>
<?php
      echo lang("select headline news sites").":</td></tr><tr>";
      echo "<td><select name=\"headlines[]\" multiple size=5>\n";
      $phpgw->db->query("select * from users_headlines where owner='"
  	    . $phpgw_info["user"]["userid"] . "'");
  	  while ($phpgw->db->next_record()){
  	    $users_headlines[$phpgw->db->f("site")] = " selected";
  	  }
  
      $phpgw->db->query("SELECT con,display FROM news_site ORDER BY display asc");
  	  while ($phpgw->db->next_record()) {
        echo "<option value=\"" . $phpgw->db->f("con") . "\""
          . $users_headlines[$phpgw->db->f("con")] . ">"
  			  . $phpgw->db->f("display") . "</option>";
  	  }
      echo "</select></td>\n";
?>
    </tr><tr><td><input type="submit" name="submit" value="<?php echo lang("submit"); ?>"></td></tr></table>
    </form>
<?php
  }else{
    include($phpgw_info["server"]["server_root"] . "/headlines/inc/functions.inc.php");
	  headlines_update($phpgw_info["user"]["userid"],$headlines);
    Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"]."/preferences/"));
	}
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>
