<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "admin";
  $phpgw_info["flags"]["parent_page"] = "admin.php";
  include("../header.inc.php");
  if (! $con)
     Header("Location: " . $phpgw->link("/headlines/admin.php"));

  if ((! $submit) && ($con)) {

     $phpgw->db->query("select * from news_site where con=$con");
     $phpgw->db->next_record();

     ?>

     <form method="POST" action="<?php echo $phpgw->link("/headlines/editheadline.php"); ?>">
      <input type="hidden" name="o_con" value="<?php echo $con; ?>">

      <center>
       <table border=0 width=65%>
        <tr>
         <td><?php echo lang("Display"); ?></td>
         <td><input name="n_display" value="<?php echo $phpgw->db->f("display"); ?>"></td>
        </tr>
        <tr>
         <td><?php echo lang("Base URL"); ?></td>
         <td><input name="n_base_url" value="<?php echo $phpgw->db->f("base_url"); ?>"></td>
        </tr>
        <tr>
         <td><?php echo lang("News File"); ?></td>
         <td><input name="n_newsfile" value="<?php echo $phpgw->db->f("newsfile"); ?>"></td>
        </tr>
        <tr>
         <td><?php echo lang("Minutes between Reloads"); ?></td>
         <td><input name="n_cachetime" value="<?php echo $phpgw->db->f("cachetime"); ?>"></td>
        </tr>
        <tr>
         <td><?php echo lang("Listings Displayed"); ?></td>
         <td><input name="n_listings" value="<?php echo $phpgw->db->f("listings"); ?>"></td>
        </tr>
        <tr>
         <td><?php echo lang("News Type"); ?></td>
         <td>
<?php
	 $news_type = array('rdf','fm','lt','sf','rdf-chan');
         for ($i=0;$i<count($news_type);$i++) {
           echo "<input type=\"radio\" name=\"n_newstype\" value=\"" . $news_type[$i] . "\"";
           if($phpgw->db->f("newstype") == $news_type[$i]) echo " checked";
           echo ">&nbsp;$news_type[$i]<br>";
         }
?>
         </td>
        </tr>
        <tr>
         <td colspan=2><input type="submit" name="submit" value="<?php echo lang("submit"); ?>"></td>
        </tr>
       </table>
      </center>
     </form>
     <?php
    $phpgw->common->phpgw_footer();
  } else {
     $phpgw->db->lock("news_site");

     $phpgw->db->query("UPDATE news_site SET display='" . addslashes($n_display) . "', " 
	  	    . "base_url='" . addslashes($n_base_url) . "', "
	  		. "newsfile='" . addslashes($n_newsfile) . "', "
	  		. "lastread=0, newstype='" . $n_newstype . "', "
	  		. "cachetime=$n_cachetime, listings=$n_listings "
	  		. "WHERE con=$o_con");

     $phpgw->db->unlock();

     Header("Location: " . $phpgw->link("/headlines/admin.php", "cd=27"));
  }
?>
