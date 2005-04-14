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


if ($user->isAdmin())
	printHTMLHead( getMLText("global_default_keywords") );
else
	printHTMLHead( getMLText("personal_default_keywords"));

printTitleBar(getFolder($settings->_rootFolderID));
printCenterStart();


//Neue Kategorie anlegen -----------------------------------------------------------------------------
if ($action == "addcategory")
{
	printStartBox(getMLText("new_default_keyword_category"));
	print "<div class=\"standardText\">";
	printMLText("creating_new_default_keyword_category");
	
	//changed by dawnlinux @ realss.com from variable name "name" to "cat_name"
	//to fix the bug of always producing "sort" as the cat name
	$cat_name = sanitizeString($cat_name);
	
	$newCategory = addKeywordCategory($user->getID(), $cat_name);
	if ($newCategory) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else {
		printMLText("error_occured");
		printGoBack();
	}
}

//Kategorie l�schen ----------------------------------------------------------------------------------
else if ($action == "removecategory")
{
	printStartBox(getMLText("rm_default_keyword_category"));
	print "<div class=\"standardText\">";
	printMLText("removing_default_keyword_category");
	
	$category = getKeywordCategory($categoryid);
	$owner    = $category->getOwner();
	if (!$user->isAdmin() && $owner->getID() != $user->getID())
		die("You're not allowed to delete this category");
	
	if ($category->remove()) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Kategorie bearbeiten: Neuer Name --------------------------------------------------------------------
else if ($action == "editcategory")
{
	printStartBox(getMLText("edit_default_keyword_category"));
	print "<div class=\"standardText\">";
	printMLText("editing_default_keyword_category");
	
	$category = getKeywordCategory($categoryid);
	$owner    = $category->getOwner();
	if (!$user->isAdmin() && $owner->getID() != $user->getID())
		die("You're not allowed to edit this category");
	
	//changed by dawnlinux @ realss.com from variable "name" to "edited_cat_name" 
	//to solve the problem that it is aways producing re-edited name as "sort"
	$edited_cat_name = sanitizeString($edited_cat_name);
	
	if ($category->setName($edited_cat_name)) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Kategorie bearbeiten: Neue Stichwortliste  ----------------------------------------------------------
else if ($action == "newkeywords")
{
	printStartBox(getMLText("new_default_keywords"));
	print "<div class=\"standardText\">";
	printMLText("adding_default_keywords");
	
	$category = getKeywordCategory($categoryid);
	$owner    = $category->getOwner();
	if (!$user->isAdmin() && $owner->getID() != $user->getID())
		die("You're not allowed to add keywords to this category");
	
	$keywords = sanitizeString($keywords);
	
	if ($category->addKeywordList($keywords)) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Kategorie bearbeiten: Stichwortliste bearbeiten ----------------------------------------------------------
else if ($action == "editkeywords")
{
	printStartBox(getMLText("edit_default_keywords"));
	print "<div class=\"standardText\">";
	printMLText("editing_default_keywords");
	
	$category = getKeywordCategory($categoryid);
	$owner    = $category->getOwner();
	if (!$user->isAdmin() && $owner->getID() != $user->getID())
		die("You're not allowed to edit keywords in this category");
	
	$keywords = sanitizeString($keywords);
	if (!is_numeric($keywordsid))
		die ("invalid keywords id");
	
	if ($category->editKeywordList($keywordsid, $keywords)) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

//Kategorie bearbeiten: Neue Stichwortliste l�schen ----------------------------------------------------------
else if ($action == "removekeywords")
{
	printStartBox(getMLText("rm_default_keywords"));
	print "<div class=\"standardText\">";
	printMLText("removing_default_keywords");
	
	$category = getKeywordCategory($categoryid);
	$owner    = $category->getOwner();
	if (!$user->isAdmin() && $owner->getID() != $user->getID())
		die("You're not allowed to remove keywords from this category");
	
	if (!is_numeric($keywordsid))
		die ("invalid keywords id");
	
	if ($category->removeKeywordList($keywordsid)) {
		printMLText("op_finished");
		
		if ($user->isAdmin())
			printGoto(array(array(getMLText("global_default_keywords"), "../out/out.DefaultKeywords.php")));
		else
			printGoto(array(array(getMLText("personal_default_keywords"), "../out/out.DefaultKeywords.php")));
	}
	else
	{
		printMLText("error_occured");
		printGoBack();
	}
}

print "</div>";
printEndBox();
printCenterEnd();
printHTMLFoot();

?>
