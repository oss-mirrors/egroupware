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

$users = getAllUsers();


printHTMLHead( getMLText("user_list") );
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

for ($i = 0; $i < count($users); $i++)
{
	$currUser = $users[$i];
	if ($currUser->getID() == $settings->_guestID)
		continue;
		
	if ($i == 0)
	 	printStartBox(getMLText("user") . ": \"" . $currUser->getFullName() . "\"");
	else
	 	printNextBox(getMLText("user") . ": \"" . $currUser->getFullName() . "\"");
?>
	<table border="0">
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_login");?>:</td>
			<td class="standardText"><?print $currUser->getLogin();?></td>
		</tr>
	<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_name");?>:</td>
			<td class="standardText"><?print $currUser->getFullName();?></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("email");?>:</td>
			<td class="standardText"><a href="mailto:<?print $currUser->getEmail();?>"><?print $currUser->getEmail();?></a></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("comment");?>:</td>
			<td class="standardText"><?print $currUser->getComment();?></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("groups");?>:</td>
			<td class="standardText">
				<?
					$groups = $currUser->getGroups();
					if (count($groups) == 0)
						printMLText("no_groups");
					else
					{
						for ($j = 0; $j < count($groups); $j++)
						{
							print $groups[$j]->getName();
							if ($j +1 < count($groups))
								print ", ";
						}
					}
				?>
			</td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("user_image");?>:</td>
			<td class="standardText">
				<?
					if ($currUser->hasImage())
						print "<img src=\"".$currUser->getImageURL()."\">";
					else
						printMLText("no_user_image");
				?>
			</td>
		</tr>
	</table>
<?
}
printEndBox();
printCenterEnd();
printHTMLFoot();
?>