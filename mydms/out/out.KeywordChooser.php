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

printHTMLHead( getMLText("use_default_keywords") );
?>


<script language="JavaScript">
var targetObj = opener.document.form1.keywords;
var myTA;


function insertKeywords(keywords) {

	if (navigator.appName == "Microsoft Internet Explorer") {
		myTA.value += " " + keywords;
	}
	//assuming Mozilla
	else {
		selStart = myTA.selectionStart;
		
		myTA.value = myTA.value.substring(0,myTA.selectionStart)
	    	          + keywords
					  + myTA.value.substring(myTA.selectionStart,myTA.value.length);
		
		myTA.selectionStart = selStart + keywords.length;
		myTA.selectionEnd = selStart + keywords.length;
	}				  
	myTA.focus();
}

function cancel() {
	window.close();
	return true;
}

function acceptKeywords() {
	targetObj.value = myTA.value;
	window.close();
	return true;
}



obj = new Array();
obj[0] = -1;
obj[1] = -1;
function showKeywords(which) {
	if (obj[which] != -1)
		obj[which].style.display = "none";
	
	list = document.getElementById("categories" + which);
	
	id = list.options[list.selectedIndex].value;
	if (id == -1)
		return;
	
	obj[which] = document.getElementById("keywords" + id);
	obj[which].style.display = "";
}
</script>

<div style="margin-left: 10pt; margin-top: 10pt">
<?
printStartBox(getMLText("use_default_keywords"));
?>
<table>
	<tr>
		<td class="inputDescription"><?=getMLText("global_default_keywords")?>:</td>
		<td>
			<select onchange="showKeywords(0)" id="categories0">
				<option value="-1"><?=getMLText("choose_category")?>
				<?
				foreach ($categories as $category) {
					$owner = $category->getOwner();
					if ($owner->getID() != $settings->_adminID)
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
		if ($owner->getID() != $settings->_adminID)
			continue;
?>
	<tr id="keywords<?=$category->getID()?>" style="display : none;">
		<td valign="top" class="inputDescription"><?=getMLText("default_keywords")?>:</td>
		<td class="standardText">
			<?
				$lists = $category->getKeywordLists();
				foreach ($lists as $list) {
					print "<li><a href='javascript:insertKeywords(\"$list[keywords]\");'>$list[keywords]</a></li>";
				}
			?>
		</td>
	</tr>
<? } ?>
	<tr>
		<td colspan="2"><hr></td>
	</tr>
	<tr>
		<td class="inputDescription"><?=getMLText("personal_default_keywords")?>:</td>
		<td>
			<select onchange="showKeywords(1)" id="categories1">
				<option value="-1"><?=getMLText("choose_category")?>
				<?
				foreach ($categories as $category) {
					$owner = $category->getOwner();
					if ($owner->getID() == $settings->_adminID)
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
		if ($owner->getID() == $settings->_adminID)
			continue;
?>
	<tr id="keywords<?=$category->getID()?>" style="display : none;">
		<td valign="top" class="inputDescription"><?=getMLText("default_keywords")?>:</td>
		<td class="standardText">
			<?
				$lists = $category->getKeywordLists();
				foreach ($lists as $list) {
					print "<li><a href='javascript:insertKeywords(\"$list[keywords]\");'>$list[keywords]</a></li>";
				}
			?>
		</td>
	</tr>
<? } ?>
	<tr>
		<td colspan="2"><hr></td>
	</tr>
	<tr>
		<td valign="top" class="inputDescription"><?=getMLText("keywords")?>:</td>
		<td><textarea id="keywordta" rows="5" cols="30"></textarea></td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
			<input type="Button" onclick="acceptKeywords();" value="<?=getMLText("accept")?>"> &nbsp;&nbsp;
			<input type="Button" onclick="cancel();" value="<?=getMLText("cancel")?>">
		</td>
	</tr>
</table>

<?
printEndBox();
?>
</div>

<script language="JavaScript">
myTA = document.getElementById("keywordta");
myTA.value = targetObj.value;
myTA.focus();
</script>

</body>
</html>