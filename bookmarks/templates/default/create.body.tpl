<!-- $Id$ -->
{POSSIBLE_DUP}
<form method="post" action="{FORM_ACTION}">
 <table border=0 bgcolor="#EEEEEE" align="center">
 <tr>
  <td><a href="{URL}">URL</a></td>
  <td><input type="text" name="url"  size=60 maxlength=255 value="{DEFAULT_URL}"></td>
 </tr>
 <tr>
  <td>Name</td>
  <td><input type="text" name="name" size=60 maxlength=255 value="{DEFAULT_NAME}"></td>
 </tr>
 <tr>
  <td>Desc</td>
  <td><textarea name="desc" rows=3 cols=60 wrap="virtual">{DEFAULT_DESC}</textarea></td>
 </tr>
  <tr>
   <td>Keywords</td>
   <td><input type="text" name="keyw" size=60 maxlength=255 value="{DEFAULT_KEYW}"></td>
 </tr>
 <tr>
  <td><a href="{CATEGORY_URL}">Category</a></td>
  <td>{CATEGORY_SELECT}</td>
  </td>
 </tr>
 <tr>
  <td><a href="{SUBCATEGORY_URL}">Sub Category</a></td>
  <td>{SUBCATEGORY_SELECT}</td>
 </tr>
 <tr>
  <td><a href="{RATINGS_URL}">Rating</a></td>
  <td>{RATINGS_SELECT}</td>
 </tr>
 <tr>
  <td>Public<br>
  </td>
  <td><input type="checkbox" name="public" {DEFAULT_PUBLIC}>
      <small>Check to allow others to see this bookmark</small></td>
 </tr>
 <tr>
  <td colspan=2 align=right>
    <input type="image" name="bk_create" title="Create Bookmark" 
      src="images/save.gif" border=0 width=24 height=24>
  </td>
 </tr>
</table>
</form>
