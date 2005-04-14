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


function printTree($path, $level = 0)
{
	GLOBAL $user, $form;
	
	$folder = $path[$level];
	$subFolders = filterAccess($folder->getSubFolders(), $user, M_READ);
	$documents  = filterAccess($folder->getDocuments(), $user, M_READ);
	
	if ($level+1 < count($path))
		$nextFolderID = $path[$level+1]->getID();
	else
		$nextFolderID = -1;
	
	print "<table cellpadding=0 cellspacing=0>\n";
	print "  <tr>\n";
	print "    <td valign=\"top\"";
	if (count($subFolders) + count($documents) > 0)
		print " background=\"".getImgPath("down.gif")."\"";
	print "><img src=\"";
	if ($level == 0) printImgPath("to_down.gif");
	else if (count($subFolders) + count($documents) > 0) printImgPath("right_in_to_down.gif");
	else printImgPath("right_in.gif");
	print "\" border=0></td>\n";
	print "    <td class=\"foldertree\">";
	print "<img src=\"".getImgPath("folder_opened.gif")."\" width=18 height=18 border=0>".$folder->getName()."</td>\n";
	print "  </tr>\n";
	
	for ($i = 0; $i < count($subFolders); $i++) {
		print "<tr>";
		if ($i +1 < count($subFolders) + count($documents))
			print "<td background=\"".getImgPath("down.gif")."\" valign=\"top\"><img src=\"".getImgPath("right.gif")."\" border=0></td>";
		else
			print "<td valign=\"top\"><img src=\"".getImgPath("right_last.gif")."\" border=0></td>";
		print "<td>";
		if ($subFolders[$i]->getID() == $nextFolderID)
			printTree($path, $level+1);
		else {
			$subFolders_ = filterAccess($subFolders[$i]->getSubFolders(), $user, M_READ);
			$documents_  = filterAccess($subFolders[$i]->getDocuments(), $user, M_READ);
			
			print "<table cellpadding=0 cellspacing=0><tr>";
			if (count($subFolders_) + count($documents_) > 0)
				print "  <td valign=\"top\"><a href=\"out.DocumentChooser.php?form=$form&folderid=".$subFolders[$i]->getID()."\"><img src=\"".getImgPath("right_in_plus.gif")."\" border=0></a></td>";
			else
				print "  <td valign=\"top\"><img src=\"".getImgPath("right_in.gif")."\"></td>";
			print "  <td class=\"foldertree\" valign=\"top\">";
			print "<img src=\"".getImgPath("folder_closed.gif")."\" border=0>".$subFolders[$i]->getName()."</td>";
			print "</tr></table>";
		}
		print "</td>";
		print "</tr>";
	}
	for ($i = 0; $i < count($documents); $i++)
	{
		print "<tr>";
		if ($i +1 < count($documents))
			print "<td background=\"images/down.gif\" valign=\"top\"><img src=\"images/right.gif\" border=0></td>";
		else
			print "<td valign=\"top\"><img src=\"images/right_last.gif\" border=0></td>";
		print "<td>";
		print "  <table cellpadding=0 cellspacing=0><tr>";
		print "    <td valign=\"top\"><img src=\"images/right_in.gif\"></td>";
		print "    <td><a  class=\"foldertree_selectable\" href=\"javascript:documentSelected(".$documents[$i]->getID().",'".addslashes($documents[$i]->getName())."');\"><img src=\"images/file.gif\" border=0>".$documents[$i]->getName()."</a></td>";
		print "  </tr></table>";
		print "</td>";
		print "</tr>";
	}
	
	print "</table>\n";
}



?>


<html>
<head>
<link rel="STYLESHEET" type="text/css" href="styles.css">
<title><?=getMLText("choose_target_document")?></title>

<script language="JavaScript">
var targetName;
var targetID;

function documentSelected(id, name) {
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
	printTree($folder->getPath());
?>

<script language="JavaScript">
targetName = opener.document.<?=$form?>.docname;
targetID   = opener.document.<?=$form?>.docid;
</script>

</body>
</html>