<?php
  /**************************************************************************\
  * phpGroupWare - Email (Addressbook list)                                  *
  * http://www.phpgroupware.org                                              *
  * This file written by Brian King <bking@affcu.com>                        *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True, "currentapp" => "email");
  include("../header.inc.php");
//   if (! $phpgw_info["user"]["permissions"]["addressbook"] || ! $phpgw_info["user"]["permissions"]["email"])
//      badsession();

   if ($order)
      $ordermethod = "order by $order $sort";
   else
      $ordermethod = "order by lastname,firstname,email asc";

   $filtermethod = " or access='public' " . $phpgw->accounts->sql_search("access");

   $sql = "select count(*) from addressbook where ( owner='"
	   . $phpgw_info["user"]["userid"] . "' $filtermethod ) AND email != ''"; 

   $phpgw->db->query($sql);
   $phpgw->db->next_record();

   if ($phpgw->db->f(0) == 0) {
      echo "<body bgcolor=\"" . $phpgw_info["theme"]["bg_color"] . "\">"
	 . "<center>" . lang("There are no email address's in your addressbook")
	 . "</center>";
      exit;
   }

 ?>

<head>
<title><?php echo $phpgw_info["site_title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
  <!--
   A:link{text-decoration:none}
   A:visted{text-decoration:none}
   A:active{text-decoration:none}
  -->
</style>

<script>
   function ExchangeTo(thisform) 
   { 
     if (opener.document.doit.to.value=='') {
        opener.document.doit.to.value=thisform.elements[0].value;
     } else {
        opener.document.doit.to.value+=", "+thisform.elements[0].value;
     }
   } 

   function ExchangeCc(thisform) 
   {
     if (opener.document.doit.cc.value=='') {
        opener.document.doit.cc.value=thisform.elements[0].value;
     } else {
        opener.document.doit.cc.value+=", "+thisform.elements[0].value;
     } 
   }

</script>
</head>

<body bgcolor="<?php echo $phpgw_info["theme"]["bg_color"]; ?>" vlink="<?php echo $phpgw_info["theme"]["vlink"]; ?>" link="<?php echo $phpgw_info["theme"]["link"]; ?>" alink="<?php echo $phpgw_info["theme"]["alink"]; ?>">
   <center>
   <table width="75%" border="0" cellspacing="1" cellpadding="3">
     <tr bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
       <td width="25%" height="21">
        <font size="-1" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
         <?php echo $phpgw->nextmatchs->show_sort_order($sort,"lastname",$order,"addressbook.php",
                               "Last Name"); ?>
        </font>
       </td>
       <td width="25%" height="21" bgcolor="<? echo $phpgw_info["theme"]["th_bg"]; ?>">
        <font size="-1" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
         <?php echo $phpgw->nextmatchs->show_sort_order($sort,"firstname",$order,"addressbook.php",
                               "First Name"); ?>
        </font>
       </td>
       <td width="25%" height="21">
        <font size="-1" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          Email Address:
        </font>
       </td>
       <td width="12%" height="21">
        <font size="-1" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          To:
        </font>
       </td>
       <td width="12%" height="21">
        <font size="-1" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          Cc:
        </font>
       </td>
     </tr>

 <?php

   $phpgw->db->query("SELECT * FROM addressbook WHERE ( owner='"
	              . $phpgw_info["user"]["userid"] . "' $filtermethod ) AND email != '' "
	              . $ordermethod);

   while ($phpgw->db->next_record()) {
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);

     $firstname  = $phpgw->db->f("firstname");
     $lastname   = $phpgw->db->f("lastname");
     $email      = $phpgw->db->f("email");
     $con        = $phpgw->db->f("con");

     /* This for for just showing the company name stored in lastname. */
     if (($lastname) && (! $firstname))
        $t_colspan = " colspan=2";
     else {
        $t_colspan = "";
        if ($firstname == "") $firstname = "&nbsp;";
        if ($lastname  == "") $lastname  = "&nbsp;";
     }

     ?>
       <tr bgcolor="<?php echo $tr_color; ?>">
        <td valign="top" width="25%"<?php echo $t_colspan; ?>>
         <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          <?php echo $lastname; ?> 
         </font> 
        </td>
<?php
         if (! $t_colspan)
	 {
?>
        <td valign="top" width="25%">
         <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          <?php echo $firstname; ?> 
         </font> 
        </td>
<?php
         }
?>
       <form>
        <td valign="top" width="25%">
         <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          <input type="text" size="25" name="EX1" value="<?php echo $email; ?>">
         </font>
        </td>

        <td valign="top" width="12%">
         <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
         <input type="BUTTON" value="To" onClick="ExchangeTo(this.form);" name="BUTTON">
         </font>
        </td>
        <td valign="top" width="12%">
         <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
          <input type="BUTTON" value="Cc" onClick="ExchangeCc(this.form);" name="BUTTON">
         </font>
        </td>
       </tr>
      </form>
      <?
   }

 ?>
   <form>
   </table>
   <br><input type="button" value="done" onClick="window.close()">
   </form>
 </center>

