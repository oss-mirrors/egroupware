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

  if (isset($submit) && $submit) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }
  $phpgw_info["flags"]["currentapp"] = "admin";
  include("../header.inc.php");
  if (isset($submit) && $submit) {
    $error = "";
    if (! $n_display)
      $error .= "<br>" . lang("You must enter a display");

    if (! $n_base_url)
      $error .= "<br>" . lang("You must enter a base url");

    if (! $n_newsfile)
      $error .= "<br>" . lang("You must enter a news url");

    if (! $n_cachetime)
      $error .= "<br>" . lang("You must enter the number of minutes between reload");

    if (! $n_listings)
      $error .= "<br>" . lang("You must enter the number of listings display");

    if (! $n_newstype)
      $error .= "<br>" . lang("You must select a file type");

    if (!$error) {
      $phpgw->db->query("select display from news_site where base_url='"
		 . addslashes(strtolower($n_base_url)) . "' and newsfile='"
		 . addslashes(strtolower($n_newsfile)) . "'");
      $phpgw->db->next_record();
      if ($phpgw->db->f("display")) {
        $error = "<center>" . lang("That site has already been entered") . "</center>";
      }
    }

    if (!$error) {
      $phpgw->db->lock("news_site");

      $sql = "insert into news_site (display,base_url,newsfile,"
	   . "lastread,newstype,cachetime,listings) "
	   . "values ('" . addslashes($n_display) . "','"
	   . addslashes(strtolower($n_base_url)) . "','" 
	   . addslashes(strtolower($n_newsfile)) . "',0,'"
	   . $n_newstype . "',$n_cachetime,$n_listings)";

      $phpgw->db->query($sql);

      $phpgw->db->unlock();

      Header("Location: " . $phpgw->link("admin.php", "cd=28"));
    }
  }

  if(isset($error) && $error) {
    $phpgw->common->phpgw_header();
    $phpgw->common->navbar();
    echo "<center>".$error."</center>";
  }
     ?>
       <form method="POST" action="<?php echo $phpgw->link("newheadline.php"); ?>">
        <center>
         <table border=0 width=65%>
           <tr>
             <td><?php echo lang("Display"); ?></td>
             <td><input name="n_display" value="<?php echo $n_display; ?>"></td>
           </tr>
           <tr>
             <td><?php echo lang("Base URL"); ?></td>
             <td><input name="n_base_url" value="<?php echo $n_base_url; ?>"></td>
           </tr>
           <tr>
             <td><?php echo lang("News File"); ?></td>
             <td><input name="n_newsfile" value="<?php echo $n_newsfile; ?>"></td>
           </tr>
           <tr>
             <td><?php echo lang("Minutes between Reloads"); ?></td>
             <td><input name="n_cachetime" value="<?php echo $n_cachetime; ?>"></td>
           </tr>
           <tr>
             <td><?php echo lang("Listings Displayed"); ?></td>
             <td><input name="n_listings" value="<?php echo $n_listings; ?>"></td>
           </tr>
           <tr>
             <td><?php echo lang("News Type"); ?></td>
             <td>
<?php
	 $news_type = array('rdf','fm','lt','sf','rdf-chan');
         for($i=0;$i<count($news_type);$i++) {
           echo "<input type=\"radio\" name=\"n_newstype\" value=\""
                . $news_type[$i] . "\"".($n_newstype == $news_type[$i]?" checked=checked":"").">&nbsp;".$news_type[$i]."<br>";
         }
?>
             </td>
           </tr>
           <tr>
             <td colspan=2>
              <input type="submit" name="submit" value="<?php echo lang("submit"); ?>">
             </td>
           </tr>
         </table>
        </center>
       </form>
     <?php
    $phpgw->common->phpgw_footer();
?>
