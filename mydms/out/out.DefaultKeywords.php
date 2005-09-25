<?
include("../inc/inc.Settings.php");
include("../inc/inc.AccessUtils.php");
include("../inc/inc.ClassAccess.php");
include("../inc/inc.ClassDocument.php");
include("../inc/inc.ClassFolder.php");
include("../inc/inc.ClassGroup.php");
include("../inc/inc.ClassUser.php");
include("../inc/inc.ClassKeywords.php");
include("../inc/inc.DBAccess.php");
include("../inc/inc.FileUtils.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");
include("../inc/inc.Authentication.php");


$categories = getAllKeywordCategories($user->getID());


if ($user->isAdmin())
	printHTMLHead( getMLText("global_default_keywords") );
else
	printHTMLHead( getMLText("personal_default_keywords"));
?>

<script language="JavaScript">
obj = -1;
function showKeywords(selectObj) {
	if (obj != -1)
		obj.style.display = "none";
	
	id = selectObj.options[selectObj.selectedIndex].value;
	if (id == -1)
		return;
	
	obj = document.getElementById("keywords" + id);
	obj.style.display = "";
}
</script>
<?
printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();


printStartBox(getMLText("new_default_keyword_category"));
?>
	<form action="../op/op.DefaultKeywords.php" method="post">
	<input type="Hidden" name="action" value="addcategory">
	<table>
		<tr>
			<td class="inputDescription" valign="top"><?printMLText("name");?>:</td>
			<td><input name="name"></td>
		</tr>
		<tr>
			<td colspan="2"><br><input type="Submit"></td>
		</tr>
	</table>
	</form>

<?
printNextBox(getMLText("edit_default_keyword_category"));
?>
	<table>
	<tr>
		<td class="inputDescription"><?=getMLText("default_keyword_category")?>:</td>
		<td>
			<select onchange="showKeywords(this)">
				<option value="-1"><?=getMLText("choose_category")?>
				<?
				foreach ($categories as $category) {
					$owner = $category->getOwner();
					if ((!$user->isAdmin()) && ($owner->getID() != $user->getID()))
						continue;
					
					print "<option value=\"".$category->getID()."\">" . $category->getName();
				}
				?>
			</select>
		</td>
	</tr>
	<?
	foreach ($categories as $category) {
		$owner = $category->getOwner();
		if ((!$user->isAdmin()) && ($owner->getID() != $user->getID()))
			continue;
	?>
		<tr id="keywords<?=$category->getID()?>" style="display : none;">
		<td colspan="2">
			<table cellpadding="5" cellspacing="0">
				<tr>
					<td colspan="2"><hr size="1" width="100%" color="#000080" noshade></td>
				</tr>
				<tr>
					<td class="inputDescription"><?=getMLText("name")?>:</td>
					<td>
						<form action="../op/op.DefaultKeywords.php" method="post">
							<input type="Hidden" name="action" value="editcategory">
							<input type="Hidden" name="categoryid" value="<?=$category->getID()?>">
							<input name="name" value="<?=$category->getName()?>">&nbsp; 
							<input type="Image" src="images/save.gif" title="<?=getMLText("save")?>" border="0">
						</form>
					</td>
				</tr>
				<tr>
					<td class="inputDescription" valign="top"><?=getMLText("default_keywords")?>:</td>
					<td>
						<table cellpadding="0" cellspacing="0">
						<?
							$lists = $category->getKeywordLists();
							if (count($lists) == 0)
								print "<tr><td class=\"standardText\">" . getMLText("no_default_keywords") . "</td></tr>";
							else
								foreach ($lists as $list) {
						?>
									<tr>
										<form action="../op/op.DefaultKeywords.php" method="post">
										<input type="Hidden" name="categoryid" value="<?=$category->getID()?>">
										<input type="Hidden" name="keywordsid" value="<?=$list["id"]?>">
										<input type="Hidden" name="action" value="editkeywords">
										<td>
											<input name="keywords" value="<?=$list["keywords"]?>">
										</td>
										<td>&nbsp;
											 <input name="action" value="editkeywords" type="Image" src="images/save.gif" title="<?=getMLText("save")?>" border="0"> &nbsp;
										<!--	 <input name="action" value="removekeywords" type="Image" src="images/del.gif" title="<?=getMLText("delete")?>" border="0"> &nbsp; -->
											<a href="../op/op.DefaultKeywords.php?categoryid=<?=$category->getID()?>&keywordsid=<?=$list["id"]?>&action=removekeywords"><img src="images/del.gif" title="<?=getMLText("delete")?>" border=0></a>
										</td>
										</form>
									</tr>
						<?		}  ?>
						</table>
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="inputDescription"><?=getMLText("new_default_keywords")?>:</td>
					<td>
						<form action="../op/op.DefaultKeywords.php" method="post">
							<input type="Hidden" name="action" value="newkeywords">
							<input type="Hidden" name="categoryid" value="<?=$category->getID()?>">
							<input name="keywords">&nbsp;
							<input type="Image" src="images/save.gif" title="<?=getMLText("save")?>" border="0">
						</form>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<br>
						<a class="standardText" href="../op/op.DefaultKeywords.php?categoryid=<?print $category->getID();?>&action=removecategory"><img src="images/del.gif" width="15" height="15" border="0" align="absmiddle" alt=""> <?printMLText("rm_default_keyword_category");?></a>
					</td>
				</tr>
			</table>
		</td>
		</tr>
<?	} ?>
	</table>
	
<?
printEndBox();


printCenterEnd();
printHTMLFoot();
?>