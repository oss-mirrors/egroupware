<?
// INIT
require("connection.inc.php");

$self=$_SERVER['PHP_SELF'];

// Post vars below
@$pallet_ID=$_POST['pallet_id']; // Pallet_ID for naming palletes
@$mode=$_POST['mode']; // Mode 1: Pallet, Mode 2: Free, Mode 3: Image
@$curColor=$_POST['form_curColor']; // CurrentColor
@$sz=$_FILES['userfile']['size']; // Upload image

// Get vars below
@$fieldid=$_GET['fieldid']; // Sets the id for giving back results
if($fieldid){
	$self.="?fieldid=".$fieldid;
}

@$dummy=$_GET['current_color']; // without '#' !!!!
if($dummy){
	$curColor="#".$dummy;
}

@$colorA=$_GET['color_a']; // without '#' !!!!
@$colorB=$_GET['color_b'];
@$colorC=$_GET['color_c'];
@$colorD=$_GET['color_d'];
$self.="&color_a=".$colorA;
$self.="&color_b=".$colorB;
$self.="&color_c=".$colorC;
$self.="&color_d=".$colorD;

$refColors="<table border=\"2\" cellspacing=\"5\"><tr>
<td style=\"background-color: #$colorA; width: 12px; height:10px;\"></td>
<td style=\"background-color: #$colorB; width: 12px; height:10px;\"></td>
<td style=\"background-color: #$colorC; width: 12px; height:10px;\"></td>
<td style=\"background-color: #$colorD; width: 12px; height:10px;\"></td>
</tr>
</table>";

// End INIT


if ($sz>0){ // Image function
	$dest="image.jpg";
	move_uploaded_file($_FILES['userfile']['tmp_name'], $dest) or die("<BR /><BR />Fout tijdens uploaden / verplaatsen...");
	include("class.Thumbnail.php");
	$tn_image = new Thumbnail($dest, 200, 200, 0); // MAKE THUMBNAIL FIRST!
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Colorpicker</title>
	<style type="text/css" media="screen">
	<!--
	@import url("colorpicker.css");
	-->
	</style>
	<script type="text/javascript" src="colorpicker.js"></script>
	<script type="text/javascript">
	
	
	function init(){
	if(typeof document.onselectstart != 'undefined') {
		document.onselectstart = function () { return false; }
	}
	<? if(@$curColor){
		print"document.getElementById('curColor').style.backgroundColor=\"$curColor\";";
		print"document.getElementById('form_curColor').value=\"$curColor\";";
		print"document.getElementById('hex').value=\"$curColor\";";
	}?>
	<? 
	if ($fieldid){
		print "document.getElementById('fieldid').value=\"$fieldid\";";
	}
	?>
	
	switchMode("<? print $mode; ?>");
	setTimeout("plot()",200); // Timeout to allow rendering of the rest of the page first!
	setTimeout("slideplot()",250);
	document.body.scroll="no";
	}




</script>
</head>
<body onload="init();">
<a class="tab" href="#" onclick="switchMode('1');" id="tab1">Color Tables</a> <a class="tab" href="#" onclick="switchMode('2');" id="tab2">Free</a> <a href="#" class="tab" onclick="switchMode('3');" id="tab3">From image</a>


<!-- -->
<div id="Free">
	<div class="dialog">
		<table cellspacing="4" cellpadding="4" class="dialog" border="0" width="100%">
			<tr valign="top"><td colspan="2">
				<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td>Hue: </td>
						<td><input style="width:40px;" type="text" tabindex="1" class="textfield" id="H" value="0" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
						<td>&nbsp;Red: </td>
						<td><input style="width:40px;"  type="text" tabindex="4" class="textfield" id="R" value="255" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
						<td>&nbsp;Color Code: </td>
						<td><input type="text" style="width:80px;" maxlength="7" id="hex" value="#ff0000" class="textfield" onkeyup="calcHex();" onchange="calcHex();" onblur="calcHex();" /></td>
					</tr>
		
					<tr>
						<td>Sat:&nbsp;&nbsp;</td>
						<td><input type="text" style="width:40px;"  tabindex="2" class="textfield" id="S" value="100" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
						<td>&nbsp;Green:&nbsp;</td>
						<td><input type="text" style="width:40px;"  tabindex="5" class="textfield" id="G" value="0" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
						<td>&nbsp;</td><td>&nbsp;</td>
					</tr>
				
					<tr>
						<td>Lum:&nbsp;&nbsp;</td>
						<td><input type="text" style="width:40px;"  tabindex="3" class="textfield" id="L" value="100" onkeyup="calcHSL();" onchange="calcHSL();" onblur="calcHSL();" /></td>
						<td>&nbsp;Blue: </td>
						<td><input type="text" style="width:40px;"  tabindex="6" class="textfield" id="B" value="0" onkeyup="calcRGB();" onchange="calcRGB();" onblur="calcRGB();" /></td>
						<td>&nbsp;</td><td>&nbsp;</td>
					</tr>
				</table>
			</td></tr>
		</table>
		<div id="gradient"></div>
		<div id="slideplot"></div>
	</div>
</div>

<!-- -->

<div id="Palette">
 <div class="dialog" style="height:100%;"><br />
 <form method="post" action="<? print $self ?>" id="form_palet">
 	<select name="pallet_id" onchange="submitter('form_palet');">
		<option value="0" style="border-bottom:1px solid #000; color: #888;">Please select:</option> 
		<?	paletMenu(); ?>
	</select>
	<input type="hidden" name="mode" value="1" />
	<input type="hidden" id="ref1" name="ref" value="<? print $fieldid ?>" />
	<input type="hidden" name="form_curColor" id="form_curColor" value="" />
 </form>
 <? paletTable(); ?>
 </div>
</div>

<!-- -->

<div id="Image">
 <div class="dialog" style="height:100%;"><br />(Just JPG at the moment!)
	<form method="post" enctype="multipart/form-data" action="<? print $self; ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		<input type="hidden" name="mode" value="3" />
		<input type="file" name="userfile" title="File" />
		<input type="submit" name="" value="Upload" />
	</form>
	<form method="get" action="image.php" target="console">
		<input id="generatedImage" type="image" src="image.jpg" ismap="ismap" style="cursor:crosshair; border:1px #888 inset" /> 
	</form>
	<iframe name="console" src="" style="visibility:hidden;width:1px;height:1px;"></iframe>
 </div>
</div>

<div id="OkBtn">
	<input type="button" value="Ok" onclick="doeQuit();" />
</div>

<div id="refColors">
	<? print $refColors; ?>
</div>
<div id="mainselector">
	<div id="curColor">
			<div id="mouseover">
			</div>
	</div>
</div>
<input type="hidden" id="fieldid" value="">
</body>
</html>