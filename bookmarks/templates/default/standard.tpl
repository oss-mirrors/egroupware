<html>
<head>
<link title="bookmarker styles" rel="stylesheet" type="text/css" href="bk.css">
<title>bookmarker: {TITLE}</title>
<!-- $Id$ -->

<script language="Javascript">
// <!-- start Javascript
  function pop_tree() {
    window.open('{TREE_URL}', 'treeview', 'scrollbars,resizable,height=640,width=250');
  }
// end Javascript -->
</script>
</head>

<body bgcolor="#FFFFFF">

<table width=95% border=0 cellspacing=0 bgcolor=#5B68A6>
 <tr class=std>
  <td valign="top"><h3 class=std>start</h3></td>
  <td valign="bottom" align="right">
    <a href="{START_URL}"    class=std>start</a>       &nbsp;&nbsp;
    <a href="javascript:pop_tree()" class=std>tree view</a>&nbsp;&nbsp;
    <a href="{LIST_URL}"     class=std>plain list</a>  &nbsp;&nbsp;
    <a href="{CREATE_URL}"   class=std>create</a>      &nbsp;&nbsp;
    <a href="{MAINTAIN_URL}" class=std>maintain</a>    &nbsp;&nbsp;
    <a href="{SEARCH_URL}"   class=std>search</a>      &nbsp;&nbsp;
    <a href="{FAQ_URL}"      class=std>faq</a>         &nbsp;&nbsp;
  </td>
 </tr>
</table>

{BODY}

<table width=95% border=0 cellspacing=0 bgcolor=#5B68A6>
 <tr class=std>
  <td valign="bottom">
   <h3 class=std>bookmarker at renaghan.com</h3>
  </td>
  <td valign="top" align="right">
    <a href="{CATEGORY_URL}"    class=std>category</a>    &nbsp;&nbsp;
    <a href="{SUBCATEGORY_URL}" class=std>sub-category</a>&nbsp;&nbsp;
    <a href="{RATINGS_URL}"     class=std>ratings</a>     &nbsp;&nbsp;
    <a href="{USER_URL}"        class=std>user</a>        &nbsp;&nbsp;
    <a href="{LOGOUT_URL}"      class=std>logout</a>      &nbsp;&nbsp;

    <br><small class=ver>version {VERSION}</small>
 </tr>
</table>
</body>
</html>
