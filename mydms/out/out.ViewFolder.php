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


$folderid = (isset($folderid)) ? $folderid : 1;
$folder = getFolder($folderid);

if ($folder->getAccessMode($user) < M_READ)
	die ("Access denied");


printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printFolderPageStart($folder);

printPageHeader(getMLText("folder_overview") . ": " . $folder->getName());

printStartBox(getMLText("folder_infos"));

?>
	
	<table cellpadding="0" cellspacing="10">
		<tr>
			<td class="infos" valign="top"><?printMLText("owner");?>:</td>
			<td style="border-left: 1pt solid #000080;" rowspan="2">&nbsp;</td>
			<td class="infos">
				<?
					$owner = $folder->getOwner();
					print "<a class=\"infos\" href=\"mailto:".$owner->getEmail()."\">".$owner->getFullName()."</a>";
				?>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?printMLText("comment");?>:</td>
			<td class="infos"><?print $folder->getComment();?></td>
		</tr>
	</table>
	
<?
printNextBox(getMLText("subfolder_list"));
?>
				
	<table cellspacing="5" cellpadding="0" border="0">
	<?
		$subFolders = $folder->getSubFolders();
		$subFolders = filterAccess($subFolders, $user, M_READ);
		if (count($subFolders) > 0)
		{
			$rownum = count($subFolders)+1;
			print "<tr>\n";
			print "<td></td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("name")."</i></td>\n";
			print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("comment")."</i></td>\n";
			print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("owner")."</i></td>\n";
			print "</tr>\n";
			foreach($subFolders as $subFolder)
			{
				$owner = $subFolder->getOwner();
				$comment = $subFolder->getComment();
				if (strlen($comment) > 25) $comment = substr($comment, 0, 22) . "...";
				print "<tr>";
				print "<td><img src=\"images/folder_closed.gif\" width=18 height=18 border=0></td>";
				print "<td class=\"filelist\"><a class=\"filelist\" href=\"out.ViewFolder.php?folderid=".$subFolder->getID()."\">" . $subFolder->getName() . "</a></td>\n";
				print "<td class=\"filelist\">" . $comment . "</td>";
				print "<td class=\"filelist\">".$owner->getFullName()."</td>";
				print "</tr>";
			}
		}
		else
			print "<tr><td class=\"filelist\">".getMLText("no_subfolders")."</td></tr>";
	?>
	</table>

<?
	printNextBox(getMLText("document_list"));
?>

	<table cellspacing="5" cellpadding="0" border="0">
	<?
		$documents = $folder->getDocuments();
		$documents = filterAccess($documents, $user, M_READ);
		if (count($documents) > 0)
		{
			$rownum = count($documents)+1;
			print "<tr>\n";
			print "<td></td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("name")."</i></td>\n";
			print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("comment")."</i></td>\n";
			print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
			print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("owner")."</i></td>\n";
			print "</tr>\n";
			foreach($documents as $document)
			{
				$owner = $document->getOwner();
				$comment = $document->getComment();
				if (strlen($comment) > 25) $comment = substr($comment, 0, 22) . "...";
				print "<tr>";
				print "<td><img src=\"images/file.gif\" width=18 height=18 border=0></td>";
				print "<td class=\"filelist\"><a class=\"filelist\" href=\"out.ViewDocument.php?documentid=".$document->getID()."\">" . $document->getName() . "</a></td>\n";
				print "<td class=\"filelist\">" . $comment . "</td>";
				print "<td class=\"filelist\">".$owner->getFullName()."</td>";
				print "</tr>";
			}
		}
		else
			print "<tr><td class=\"filelist\">".getMLText("no_documents")."</td></tr>";
	?>
	</table>
		
<?

printEndBox();

printFolderPageEnd($folder);



?>