<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN
   ---------------------------------------------------------------------
   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   ---------------------------------------------------------------------
   */

   /* $Id$ */

   // INIT
   $phpgw_flags = Array(
	  'currentapp'	=>	'jinn',
	  'noheader'	=>	True,
	  'nonavbar'	=>	True,
	  'noappheader'	=>	True,
	  'noappfooter'	=>	True,
	  'nofooter'	=>	True
   );

   $GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

   //fixme: this file is included twice, by parents on different levels, so the header to be included can be one of two different paths
   @include('../../../../header.inc.php');
   @include('../../../../../header.inc.php');

   $bo = CreateObject('jinn.bouser');
   $field_config = $bo->so->get_field_values($bo->session['site_object_id'],$_GET[strippedname]);
   $config = unserialize(base64_decode($field_config[field_plugins]));
   $config = $config[conf];

   $savant= createobject('phpgwapi.tplsavant2');

   //require("connection.inc.php");

   $self=$_SERVER['PHP_SELF'];

   // Post vars below
   $pallet_ID=$_POST['pallet_id']; // Pallet_ID for naming palletes
   $mode=$_POST['mode']; // Mode 1: Pallet, Mode 2: Free, Mode 3: Image
   $sz=$_FILES['userfile']['size']; // Upload image

   // Get vars below
   $fieldid=$_GET['fieldid']; // Sets the id for giving back results
   if($fieldid){
	  $self.="?fieldid=".$fieldid."&strippedname=".$_GET[strippedname];
   }
   else
   {
	  $self.="?x=x";
   }

   $curColor=($_GET['current_color']?$_GET['current_color']:$_POST['form_curColor']); // CurrentColor

   $curColor=validateColor($curColor);

   $prim_palet_arr=explode(',',$config[primpalet]);

   if(is_array($prim_palet_arr))
   {
	  foreach($prim_palet_arr as $primcolor) 
	  {
		 if($checked_color=validateColor(trim($primcolor)))
		 {
			$savant->prim_palet_arr[]=$checked_color;	
		 }
	  }
   }

   $userimg= $GLOBALS[egw_info][user][account_lid]."_image.png"; 
   if ($sz>0)
   { 
	  // Image function
	  $dest=$userimg;

	  $imgtype[1]='gif';
	  $imgtype[2]='jpg';
	  $imgtype[3]='png';
	  $imgtype[5]='psd';
	  $imgtype[6]='bmp';
	  $imgtype[7]='tif';
	  $imgtype[8]='tif';

	  $tmpimg=$_FILES['userfile']['tmp_name'].'.'.$imgtype[exif_imagetype($_FILES['userfile']['tmp_name'])];
	  move_uploaded_file($_FILES['userfile']['tmp_name'], $tmpimg) or die("<BR /><BR />Fout tijdens uploaden / verplaatsen...");

	  include("class.Thumbnail.php");
	  $tn_image = new Thumbnail($tmpimg, 200, 200, 0); // MAKE THUMBNAIL FIRST!
	  $tn_image->show($dest);
   }

   function paletMenu(){
	  global $pallet_ID;

	  $query="SELECT ID, Naam FROM pallet_namen ORDER BY Naam";
	  $resultid=mysql_query($query) or die("select Query Fout:".mysql_error());
	  while($arg=mysql_fetch_array($resultid))
	  {
		 $naam=$arg["Naam"];	
		 $id=$arg["ID"];	
		 @$pallet_ID==$id ? $selected = "selected=\"selected\"" : $selected = "";
		 print "<option value=\"$id\" $selected>$naam</option>\n";
	  }
   }

   function paletTable(){
	  global $pallet_ID;

	  if(@$pallet_ID){
		 $query="SELECT pallet_namen_ID,Kleur FROM pallet_kleuren WHERE pallet_namen_ID='$pallet_ID'";
		 $resultid=mysql_query($query) or die("select Query Fout:".mysql_error());
		 while($arg=mysql_fetch_array($resultid))
		 {
			$Kleur[]=$arg["Kleur"];	
		 }
		 if(count(@$Kleur)>0){
			print"<table border=\"2\" cellspacing=\"5\"><tr>";
				  $i=0;
				  for($c=0;$c<count($Kleur);$c++){
					 $hxc=$Kleur[$c];
					 print "<td style=\"background-color: $hxc; width: 12px; height:10px;\" onmousedown=\"sel2('$hxc');\" onmouseover=\"mo('$hxc');\">&nbsp;</td>";
					 $i++;
					 if($i>27){
						$i=0;
						print"</tr><tr>";	
					 }
				  }
				  print"</tr></table>";
		 }
	  }
   }

   function validateColor($hex, $asString = true) 
   {
	  // strip off any leading #
	  if (0 === strpos($hex, '#')) { 
		 $hex = substr($hex, 1);
	  } else if (0 === strpos($hex, '&H')) {
		 $hex = substr($hex, 2);
	  }      

	  // break into hex 3-tuple
	  $cutpoint = ceil(strlen($hex) / 2)-1; 
	  $rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);

	  if(isset($rgb[0]) && $rgb[0]<256 && isset($rgb[1]) && $rgb[1]<256 && isset($rgb[2]) && $rgb[2]<256 )
	  {
		 return '#'.$hex;
	  }

   }

   if(is_readable($userimg))
   {
	  $savant->imagefile=$userimg;
   }
   else
   {
	  $savant->imagefile='image.png';
   }


   $savant->config=$config;
   $savant->curColor=$curColor;
   $savant->fieldid=$fieldid;
   $savant->self=$self;
   $savant->display('tpl/colorpicker.popup.tpl.php');

?>
