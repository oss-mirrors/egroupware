<!-- $Id$ -->
<form method="post" action="{FORM_ACTION}">
 <table border=0 bgcolor="#EEEEEE" align="center">
 <tr>
  <td><a href="{URL}">URL</a>&nbsp;
    <a href="{MAIL_THIS_LINK_URL}"><img align=top border=0 src="{IMAGE_URL_PREFIX}mail.{IMAGE_EXT}"></a>
  </td>
  <td><input type="text" name="url"  size=60 maxlength=255 value="{URL}"></td>
 </tr>
 <tr>
  <td>Name</td>
  <td><input type="text" name="name" size=60 maxlength=255 value="{NAME}"></td>
 </tr>
 <tr>
  <td>Desc</td>
  <td><textarea name="ldesc" rows=3 cols=60 wrap="virtual">{LDESC}</textarea></td>
 </tr>
  <tr>
   <td>Keywords</td>
   <td><input type="text" name="keywords" size=60 maxlength=255 value="{KEYWORDS}"></td>
 </tr>
 <tr>
  <td><a href="{CATEGORY_URL}">Category</a></td>
  <td>{CATEGORY}</td>
  </td>
 </tr>
 <tr>
  <td><a href="{SUBCATEGORY_URL}">Sub Category</a></td>
  <td>{SUBCATEGORY}</td>
 </tr>
 <tr>
  <td><a href="{RATINGS_URL}">Rating</a></td>
  <td>{RATING}</td>
 </tr>
 <tr>
  <td>Public<br>
  </td>
  <td><input type="checkbox" name="public" {PUBLIC_SELECTED}>
      <small>Check to allow others to see this bookmark</small></td>
 </tr>
 <tr>
   <td>Date Added</td>
   <td><strong>{ADDED}&nbsp;</strong>
       <input type="hidden" name="added" value="{ADDED_VALUE}"></td>
 </tr>
 
 <tr>
   <td>Date Last visted</td>
   <td><strong>{VISTED}&nbsp;</strong>
       <input type="hidden" name="visted" value="{VISTED_VALUE}"></td>
 </tr>

 <tr>
   <td>Date Last updated</td>
   <td><strong>{UPDATED}&nbsp;</strong>
 </tr>

 <tr>
  <td colspan=2 align=right>
   <input type="image" name="bk_delete" title="Delete Bookmark" src="{IMAGE_URL_PREFIX}delete.{IMAGE_EXT}" border=0 width=17 height=16>
   &nbsp;&nbsp;&nbsp;
   {CANCEL_BUTTON}
   <input type="image" name="bk_edit" title="Change Bookmark" src="{IMAGE_URL_PREFIX}save.{IMAGE_EXT}" border=0 width=24 height=24>
  </td>
 </tr>
</table>
</form>
