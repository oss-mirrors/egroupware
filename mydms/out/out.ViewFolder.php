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
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");

include("../inc/inc.Authentication.php");

$folderid = (isset($_GET['folderid'])) ? (int) $_GET['folderid'] : 1;
if(!$folder = getFolder($folderid)) {
	die ("Access to folder denied!");
}

if ($folder->getAccessMode($user) < M_READ) {
	die ("Access to folder denied!");
}

$prefs = $GLOBALS['egw']->preferences->data['mydms']; //tim

printHTMLHead( getMLText("folder_title", array("foldername" => $folder->getName()) ) );
printTitleBar($folder);
printFolderPageStart($folder);

printStartBox(getMLText("document_list"));
?>
	<div id="rightClickMenu" style="border:1px solid grey; position:absolute; display:none; background-color:silver; z-index:40;">the next big thing...</div>
	<table cellspacing="5" cellpadding="0" border="0">
	<?php
		$documents =& $folder->getDocuments();
		$documents =& filterAccess($documents, $user, M_READ);
		if (count($documents) > 0)
		{
			$rownum = (count($documents)*2)+1;
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
				//tim  обрезка длинны коментария
				if (($prefs['сrоpComment']) && (strlen($comment) > 25)) $comment = substr($comment, 0, 22) . "...";
				//if ((strlen($comment) > 25)) $comment = substr($comment, 0, 22) . "...";

				$linkData = array
				(
					'documentid'	=> $document->getID(),
					'menuaction'	=> 'mydms.uimydms.viewDocument'
				);
				$editURL = $GLOBALS['egw']->link('/index.php',$linkData);

				// the old code
				#print "<tr>";
				#print "<td><img src=\"images/file.gif\" width=18 height=18 border=0></td>";
				#print "<td class=\"filelist\"><a class=\"filelist\" href=\"out.ViewDocument.php?documentid=".$document->getID()."\">" . $document->getName() . "</a></td>\n";
				#print "<td class=\"filelist\">" . $comment . "</td>";
				#print "<td class=\"filelist\">".$owner->getFullName()."</td>";
				#print "</tr>";

				// the new code
				// onclick="window.open(this.href,this.target,'dependent=yes,width=750,height=400,scrollbars=yes,status=yes'); return false;"
				//tim добавлено выравнивание по верху style=\"vertical-align: top;\"
				print "<tr>";
				print "<td style=\"vertical-align: top;\"><img src=\"images/file.gif\" width=18 height=18 border=0></td>";
				print "<td style=\"vertical-align: top;\" class=\"filelist\"><a class=\"filelist\" href=\"#\" onclick=\"javascript:egw_openWindowCentered('$editURL','editDocument','680','630');\">" . $document->getName() . "</a></td>\n";
				print "<td style=\"vertical-align: top;\" class=\"filelist\">".$comment."</td>";
				print "<td style=\"vertical-align: top;\" class=\"filelist\">".$owner->getFullName()."</td>";
				print "</tr>";
			}
		} else {
			print "<tr><td class=\"filelist\">".getMLText("no_documents")."</td></tr>";
		}
	?>
	</table>

<?php

printEndBox();

printFolderPageEnd($folder);

?>