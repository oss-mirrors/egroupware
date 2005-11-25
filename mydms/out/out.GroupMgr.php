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

$groups = getAllGroups();
$allUsers = getAllUsers();


printHTMLHead( getMLText("group_management") );
?>
<script language="JavaScript">

function checkForm1(num) {
	msg = "";
	eval("var formObj = document.form" + num + "_1;");
	
	if (formObj.name.value == "") msg += "<?php printMLText("js_no_name");?>\n";
	if (formObj.comment.value == "") msg += "<?php printMLText("js_no_comment");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}

function checkForm2(num) {
	msg = "";
	eval("var formObj = document.form" + num + "_2;");
	
	if (formObj.userid.options[formObj.userid.selectedIndex].value == -1) msg += "<?php printMLText("js_select_user");?>\n";

	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}


obj = -1;
function showUser(selectObj) {
	if (obj != -1)
		obj.style.display = "none";
	
	id = selectObj.options[selectObj.selectedIndex].value;
	if (id == -1)
		return;
	
	obj = document.getElementById("keywords" + id);
	obj.style.display = "";
}

</script>
<?php
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();

printStartBox(getMLText("add_group"));
?>
	<form action="../op/op.GroupMgr.php" name="form0_1" onsubmit="return checkForm1('0');">
	<input type="Hidden" name="action" value="addgroup">
	<table>
		<tr>
			<td class="inputDescription" valign="top"><?php printMLText("name");?>:</td>
			<td><input name="name"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?php printMLText("comment");?>:</td>
			<td><textarea name="comment" rows="4" cols="30"></textarea></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
	</form>

<?php
printNextBox(getMLText("edit_group"));
?>
	<table>
	<tr>
		<td class="inputDescription"><?php echo getMLText("name")?>:</td>
		<td>
			<select onchange="showUser(this)">
				<option value="-1"><?php echo getMLText("choose_group")?>
				<?php
				foreach ($groups as $group) {
					print "<option value=\"".$group->getID()."\">" . $group->getName();
				}
				?>
			</select>
		</td>
	</tr>
	<?php
	foreach ($groups as $group) {
	?>
	<tr id="keywords<?php echo $group->getID()?>" style="display : none;">
	<td colspan="2">
	
	<form action="../op/op.GroupMgr.php" name="form<?php print $group->getID();?>_1" onsubmit="return checkForm1('<?php print $group->getID();?>');">
	<input type="Hidden" name="groupid" value="<?php print $group->getID();?>">
	<input type="Hidden" name="action" value="editgroup">
	<table border="0">
		<tr>
			<td colspan="2"><hr size="1" width="100%" color="#000080" noshade></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?php printMLText("name");?>:</td>
			<td class="standardText"><input name="name" value="<?php print $group->getName();?>"></td>
		</tr>
		<tr>
			<td class="inputDescription" valign="top"><?php printMLText("comment");?>:</td>
			<td class="standardText"><textarea name="comment" rows="4" cols="30"><?php print $group->getComment();?></textarea></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
	</form>
	<hr size="1" width="100%" color="#000080" noshade>
		<table border="0" cellpadding="5" cellspacing="0">
		<?php
			$members = $group->getUsers();
			if (count($members) == 0)
				print "<tr><td class=\"groupMembers\">".getMLText("no_group_members")."</td></tr>";
			else
			{
				print "<tr>\n";
				print "	<td style=\"border-bottom: 1pt solid #000080;\">&nbsp;</td>\n";
				print "	<td style=\"border-bottom: 1pt solid #000080;\" class=\"groupMembers\"><i>".getMLText("name")."</i></td>\n";
				print "	<td style=\"border-bottom: 1pt solid #000080;\">&nbsp;</td>\n";
				print "</tr>\n";
				foreach ($members as $member)
				{
					print "<tr>";
					print "<td><img src=\"images/usericon.gif\" width=16 height=16></td>";
					print "<td class=\"groupMembers\">" . $member->getFullName() . "</td>";
					print "<td><a href=\"../op/op.GroupMgr.php?groupid=". $group->getID() . "&userid=".$member->getID()."&action=rmmember\"><img src=\"images/del.gif\" width=15 height=15 border=0></a>";
					print "</tr>";
				}
			}
		?>
		</table>
		<form action="../op/op.GroupMgr.php" name="form<?php print $group->getID();?>_2" onsubmit="return checkForm2('<?php print $group->getID();?>');">
		<input type="Hidden" name="action" value="addmember">
		<input type="Hidden" name="groupid" value="<?php print $group->getID();?>">
		<table>
			<tr>
				<td class="inputDescription"><?php printMLText("add_member");?>:</td>
				<td>
					<select name="userid">
						<option value="-1"><?php printMLText("select_one");?>
						<option value="-1">-------------------------------
						<?php
							foreach ($allUsers as $currUser)
								if (!$group->isMember($currUser))
									print "<option value=\"".$currUser->getID()."\">" . $currUser->getFullName() . "\n";
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><br><input type="Submit"></td>
			</tr>
		</table>
		</form>
	</div>
	<hr size="1" width="100%" color="#000080" noshade>
	<a class="standardText" href="../op/op.GroupMgr.php?groupid=<?php print $group->getID();?>&action=removegroup"><img src="images/del.gif" width="15" height="15" border="0" align="absmiddle" alt=""> <?php printMLText("rm_group");?></a>
	
	</td>
	</tr>
<?php  } ?>
</table>

<?php
printEndBox();

printCenterEnd();
printHTMLFoot();
?>