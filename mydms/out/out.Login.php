<?
include("../inc/inc.Settings.php");
include("../inc/inc.Language.php");
include("../inc/inc.OutUtils.php");


printHTMLHead( getMLText("login") );
?>

<script language="JavaScript">
function checkForm()
{
	msg = "";
	if (document.form1.login.value == "") msg += "<?printMLText("js_no_login");?>\n";
	if (document.form1.pwd.value == "") msg += "<?printMLText("js_no_pwd");?>\n";
	if (msg != "")
	{
		alert(msg);
		return false;
	}
	else
		return true;
}


function guestLogin()
{
	url = "../op/op.Login.php?login=guest" + 
		"&sesstheme=" + document.form1.sesstheme.options[document.form1.sesstheme.options.selectedIndex].value +
		"&lang=" + document.form1.lang.options[document.form1.lang.options.selectedIndex].value;
	document.location.href = url;
}

</script>

<?

printCenterStart();
printStartBox(getMLText("login"));
?>

<form action="../op/op.Login.php" method="post" name="form1" onsubmit="return checkForm();">
<table>
	<tr>
		<td class="inputDescription"><?printMLText("user_login");?></td>
		<td><input name="login"></td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("password");?></td>
		<td><input name="pwd" type="Password"></td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("language");?></td>
		<td>
			<?
				print "<select name=\"lang\">";
				$languages = getLanguages();
				foreach ($languages as $currLang)
				{
					print "<option value=\"$currLang\"";
					if ($currLang == $settings->_language)
						print " selected";
					print ">$currLang";
				}
				print "</select>";
			?>
		</td>
	</tr>
	<tr>
		<td class="inputDescription"><?printMLText("theme");?></td>
		<td>
			<?
				print "<select name=\"sesstheme\">";
				$themes = getThemes();
				foreach ($themes as $currTheme)
				{
					print "<option value=\"$currTheme\"";
					if ($currTheme == $settings->_theme)
						print " selected";
					print ">$currTheme";
				}
				print "</select>";
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="standardText">
			<br><input type="Submit">
			<?
				if ($settings->_enableGuestLogin)
					print "<p><a href=\"javascript:guestLogin()\">" . getMLText("guest_login") . "</a>";
			?>
		</td>
	</tr>
</table>
</form>

<script language="JavaScript">
document.form1.login.focus();
</script>

<?
printEndBox();
printCenterEnd();
printHTMLFoot();
?>