<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");

include("../inc/inc.Authentication.php");



function printTree($path, $accessMode, $exclude, $level = 0)
{
	GLOBAL $user, $form;
	
	$folder = $path[$level];
	$subFolders = $folder->getSubFolders();
	$subFolders = filterAccess($subFolders, $user, M_READ);
	
	if ($level+1 < count($path))
		$nextFolderID = $path[$level+1]->getID();
	else
		$nextFolderID = -1;
	
	print "<table cellpadding=0 cellspacing=0>\n";
	print "  <tr>\n";
	print "    <td valign=\"top\"";
	if (count($subFolders) > 0)
		print " background=\"".getImgPath("down.gif")."\"";
	print "><img src=\"";
		if ($level == 0) printImgPath("to_down.gif");
		else if (count($subFolders) > 0) printImgPath("right_in_to_down.gif");
		else printImgPath("right_in.gif");
	print "\" border=0></td>\n";
	print "    <td class=\"foldertree\">";
	if ($folder->getAccessMode($user) >= $accessMode) {
		print "<a class=\"foldertree_selectable\" href=\"javascript:folderSelected(" . $folder->getID() . ", '" . addslashes($folder->getName()) . "')\">";
		print "<img src=\"".getImgPath("folder_opened.gif")."\" border=0>".$folder->getName()."</a></td>\n";
	}
	else
		print "<img src=\"".getImgPath("folder_opened.gif")."\" width=18 height=18 border=0>".$folder->getName()."</td>\n";
	print "  </tr>\n";
	
	for ($i = 0; $i < count($subFolders); $i++) {
		if ($subFolders[$i]->getID() == $exclude)
			continue;
		
		print "<tr>";
		if ($i +1 < count($subFolders))
			print "<td background=\"".getImgPath("down.gif")."\" valign=\"top\"><img src=\"".getImgPath("right.gif")."\" border=0></td>";
		else
			print "<td valign=\"top\"><img src=\"".getImgPath("right_last.gif")."\" border=0></td>";
		print "<td>";
		if ($subFolders[$i]->getID() == $nextFolderID)
			printTree($path, $accessMode, $exclude, $level+1);
		else {
			$subFolders_ = $subFolders[$i]->getSubFolders();
			$subFolders_ = filterAccess($subFolders_, $user, M_READ);
			
			print "<table cellpadding=0 cellspacing=0><tr>";
			if (count($subFolders_) > 0)
				print "  <td valign=\"top\"><a href=\"out.FolderChooser.php?form=$form&mode=$accessMode&exclude=$exclude&folderid=".$subFolders[$i]->getID()."\"><img src=\"".getImgPath("right_in_plus.gif")."\" border=0></a></td>";
			else
				print "  <td valign=\"top\"><img src=\"".getImgPath("right_in.gif")."\"></td>";
			print "  <td class=\"foldertree\" valign=\"top\">";
			if ($subFolders[$i]->getAccessMode($user) >= $accessMode) {
				print "<a class=\"foldertree_selectable\" href=\"javascript:folderSelected(" . $subFolders[$i]->getID() . ", '" . addslashes($subFolders[$i]->getName()) . "')\">";
				print "<img src=\"".getImgPath("folder_closed.gif")."\" border=0>".$subFolders[$i]->getName()."</a></td>\n";
			}
			else
				print "<img src=\"".getImgPath("folder_closed.gif")."\" border=0>".$subFolders[$i]->getName()."</td>";
			print "</tr></table>";
		}
		print "</td>";
		print "</tr>";
	}
	
	print "</table>\n";
}

?>


<html>
<head>
<link rel="STYLESHEET" type="text/css" href="styles.css">
<title><?=getMLText("choose_target_folder")?></title>

<script language="JavaScript">
var targetName;
var targetID;

function folderSelected(id, name) {
	targetName.value = name;
	targetID.value = id;
	window.close();
	return true;
}
</script>

</head>
<body>

<?
	$folder = getFolder($folderid);
	printTree($folder->getPath(), $mode, $exclude);
?>


<script language="JavaScript">
targetName = opener.document.<?=$form?>.targetname;
targetID   = opener.document.<?=$form?>.targetid;
</script>


</body>
</html>