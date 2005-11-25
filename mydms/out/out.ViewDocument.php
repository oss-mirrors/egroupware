<?php
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Utils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");

include("../inc/inc.Authentication.php");

$documentid	= (int)$_GET['documentid'];

$document = getDocument($documentid);
$folder = $document->getFolder();

if ($document->getAccessMode($user) < M_READ)
	die ("Access denied");


$latestContent = $document->getLatestContent();


printHTMLHead( getMLText("document_title", array("documentname" => $document->getName()) ) );
printTitleBar($folder);
printDocumentPageStart($document);

printPageHeader(getMLText("document_overview") . ": " . $document->getName());

?>
	<p class="standardText">
	<a href="../op/op.Download.php?documentid=<?php print $documentid;?>&version=<?php print $latestContent->getVersion();?>"><img src="images/download.gif" width=22 height=22 border=0 align="absmiddle"><?php printMLText("download"); ?></a>
	<?php
		if ($latestContent->viewOnline())
			print "&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"../op/viewonline" . $latestContent->getURL()."\"><img src=\"images/view.gif\" width=18 height=18 border=0 align=\"absmiddle\">" . getMLText("view_online") . "</a>";
	print "</p>";


printStartBox(getMLText("document_infos"));
?>
	<table cellpadding="0" cellspacing="10">
		<tr>
			<td class="infos" valign="top"><?php printMLText("owner");?>:</td>
			<td style="border-left: 1pt solid #000080;" rowspan="13">&nbsp;</td>
			<td class="infos">
				<?php
					$owner = $document->getOwner();
					print "<a class=\"infos\" href=\"mailto:".$owner->getEmail()."\">".$owner->getFullName()."</a>";
				?>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("comment");?>:</td>
			<td class="infos"><?php print $document->getComment();?></td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("creation_date");?>:</td>
			<td class="infos"><?php print getLongReadableDate($document->getDate()); ?></td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("keywords");?>:</td>
			<td class="infos"><?php print $document->getKeywords();?></td>
		</tr>
		<?php
			if ($document->isLocked())
			{
				$lockingUser = $document->getLockingUser();
				?>
					<tr>
						<td class="infos" valign="top"><?php printMLText("lock_status");?>:</td>
						<td class="infos"><?php printMLText("lock_message", array("email" => $lockingUser->getEmail(), "username" => $lockingUser->getFullName()));?></td>
					</tr>
				<?php
			}
		?>
		<tr>
			<td style="border-bottom: 1pt solid #000080;">&nbsp;</td>
			<td style="border-bottom: 1pt solid #000080;">&nbsp;</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("last_update");?></td>
			<td class="infos"><?php print getLongReadableDate($latestContent->getDate());?></td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("current_version");?>:</td>
			<td class="infos"><?php print $latestContent->getVersion();?></td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("comment_for_current_version");?>:</td>
			<td class="infos" valign="top"><?php print $latestContent->getComment();?></td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("uploaded_by");?>:</td>
			<td class="infos">
				<?php
					$updatingUser = $latestContent->getUser();
					print "<a class=\"infos\" href=\"mailto:".$updatingUser->getEmail()."\">".$updatingUser->getFullName()."</a>";
				?>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("file_size");?>:</td>
			<td class="infos"><?php print filesize($settings->_contentDir . $latestContent->getPath());?> bytes</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("mime_type");?>:</td>
			<td class="infos">
				<img align="absmiddle" src="images/icons/<?php print getMimeIcon($latestContent->getFileType());?>"> 
				<?php print $latestContent->getMimeType();?>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top"><?php printMLText("expires");?>:</td>
			<td class="infos" valign="top">
			<?php
				if (!$document->getExpires())
					printMLText("does_not_expire");
				else
					print getReadableDate($document->getExpires());
			?>
			</td>
		</tr>
	</table>

<?php
printNextBox(getMLText("document_versions"));
?>
	
	<table cellspacing="5" cellpadding="0" border="0">
	<?php
		$versions = $document->getContent();
		$rownum = count($versions)+1;
		print "<tr>\n";
		print "<td></td>";
		print "<td></td>";
		print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("version")."</i></td>\n";
		print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
		print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("upload_date")."</i></td>\n";
		print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
		print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("comment")."</i></td>\n";
		print "<td rowspan=".$rownum." style=\"border-left: 1pt solid #000080;\">&nbsp;</td>\n";
		print "<td class=\"filelist\" style=\"border-bottom: 1pt solid #000080;\"><i>".getMLText("uploaded_by")."</i></td>\n";
		if (($document->getAccessMode($user) >= M_READWRITE) && (count($versions) > 1))
			print "<td></td>";
		print "</tr>\n";
		for ($i = count($versions)-1; $i >= 0; $i--)
		{
			$version = $versions[$i];
			$uploadingUser = $version->getUser();
			$comment = $version->getComment();
			//if (strlen($comment) > 25) $comment = substr($comment, 0, 22) . "...";
			print "<tr>";
			print "<td>";
			if ($version->viewOnline())
				print "<a target=\"_blank\" href=\"../op/viewonline" . $version->getURL()."\"><img src=\"images/view.gif\" width=18 height=18 border=0 title=\"".getMLText("view_online")."\"></a>";
			print "</td>";
			print "<td><a href=\"../op/op.Download.php?documentid=".$documentid."&version=".$version->getVersion()."\" class=\"oldcontent\"><img src=\"images/download.gif\" width=22 height=22 border=0 title=\"".getMLText("download")."\"></a></td>";
			print "<td class=\"filelist\" align=\"center\">" . $version->getVersion() . "</td>\n";
			print "<td class=\"filelist\">" . getLongReadableDate($version->getDate()) . "</td>";
			print "<td class=\"filelist\">".$comment."</td>";
			print "<td class=\"filelist\">". $uploadingUser->getFullName() . "</td>";
			if (($document->getAccessMode($user) >= M_READWRITE) && (count($versions) > 1))
				print "<td><a href=\"out.RemoveVersion.php?documentid=".$documentid."&version=".$version->getVersion()."\"><img src=\"images/del.gif\" width=15 height=15 border=0 title=\"".getMLText("delete")."\"></a></td>";
			print "</tr>";
		}
	?>
	</table>

<?php
printNextBox(getMLText("linked_documents"));

$links = $document->getDocumentLinks();
$links = filterDocumentLinks($user, $links);

$rownum = count($links)+1;
?>

<table cellspacing="5" cellpadding="0" border="0">
	<?php
	if ($rownum > 1)
	{
		?>
		<tr>
		<td></td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?php printMLText("name");?></i></td>
		<td rowspan="<?php print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?php printMLText("comment");?></i></td>
		<td rowspan="<?php print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?php printMLText("document_link_by");?></i></td>
		<td rowspan="<?php print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?php printMLText("document_link_public");?></i></td>
		<td></td>
		</tr>
		<?php
		foreach($links as $link)
		{
			$responsibleUser = $link->getUser();
			$targetDoc = $link->getTarget();
			
			print "<tr>";
			print "<td><img src=\"images/file.gif\" width=18 height=18 border=0></td>";
			print "<td class=\"linklist\"><a href=\"out.ViewDocument.php?documentid=".$targetDoc->getID()."\" class=\"linklist\">".$targetDoc->getName()."</a></td>";
			print "<td class=\"linklist\">".$targetDoc->getComment()."</td>";
			print "<td class=\"linklist\">".$responsibleUser->getFullName()."</td>";
			print "<td class=\"linklist\">" . (($link->isPublic()) ? getMLText("yes") : getMLText("no")) . "</td>";
			print "<td>";
			if (($user->getID() == $responsibleUser->getID()) || ($user->getID() == $settings->_adminID) || ($link->isPublic() && ($document->getAccessMode($user) >= M_READWRITE )))
				print "<a href=\"../op/op.RemoveDocumentLink.php?documentid=".$documentid."&linkid=".$link->getID()."\"><img src=\"images/del.gif\" border=0></a>";
			print "</td>";
			print "</tr>";
		}
	}
	else
		print "<tr><td class=\"filelist\">".getMLText("no_document_links")."</td></tr>";
	?>
</table>

<?php
if ($user->getID() != $settings->_guestID)
{
?>
	<form action="../op/op.AddDocumentLink.php" name="form1">
	<input type="Hidden" name="documentid" value="<?php print $documentid;?>">
	<table>
		<tr>
			<td class="inputDescription"><?php printMLText("choose_target_document");?>:</td>
			<td><?php printDocumentChooser("form1");?></td>
		</tr>
		<?php
			if ($document->getAccessMode($user) >= M_READWRITE)
			{
				print "<tr><td class=\"inputDescription\">".getMLText("document_link_public")."</td><td class=\"inputDescription\">";
				print "<input type=\"Radio\" name=\"public\" value=\"true\" checked>" . getMLText("yes") . "&nbsp;&nbsp;";
				print "<input type=\"Radio\" name=\"public\" value=\"false\">" . getMLText("no");
				print "</td></tr>";
			}
		?>
		<tr>
			<td colspan="2"><br><input type="Submit" value="<?php printMLText("add_document_link");?>"></td>
		</tr>
	</table>
	</form>
<?php
}

printEndBox();


printDocumentPageEnd($document);

printHTMLFoot();
?>
