<!-- $Id$ -->
<style>
<!--
 html,body { background-color: white;  
             font-family: "Arial, Helvetica"; }

 a:LINK    { color: blue; }
 a:VISITED { color: #000066; }
 a:ACTIVE  { color: red; }
 a:HOVER   { color: black; }

 table.hdr { background-color: #FFFFFF;
             color: black; }
 big.hdr    { color: white; }
 small.hdr  { color: #FFFFFF; }

 a.hdr:LINK    { color: #CCCCCC; }
 a.hdr:VISITED { color: #CCCCCC; }
 a.hdr:ACTIVE  { color: #CCCCCC; }
 a.hdr:HOVER   { color: #000000; }

-->
</style>

<script type="text/javascript">
<!-- start Javascript
  function pop_tree() {
    window.open("{TREE_URL}", "treeview", "scrollbars,resizable,height=640,width=250");
  }
{MSIE_JS}
// end Javascript -->
</script>
</head>

<body bgcolor="#FFFFFF">

<table width=95% border=0 cellspacing=0 class=hdr>
 <tr>
  <td valign="top"    align="left"><strong>{TITLE}</strong></td>
  <td valign="bottom" align="right">
   <a class=hdr href="javascript:pop_tree()"><img width=24 height=24 src="{IMAGE_URL_PREFIX}/tree.gif" border=0 alt="Tree View"></a>
   <a class=hdr href="{LIST_URL}"><img width=24 height=24 src="{IMAGE_URL_PREFIX}/list.gif" border=0 alt="List View"></a>
   <a class=hdr href="{CREATE_URL}"><img width=24 height=24 src="{IMAGE_URL_PREFIX}/create.gif" border=0 alt="New"></a>
   <a class=hdr href="{SEARCH_URL}"><img width=24 height=24 src="{IMAGE_URL_PREFIX}/search.gif" border=0 alt="Search"></a>
  </td>
 </tr>
</table>

<table width=95% border=0 cellspacing=0>
{MESSAGES}
 <tr>
  <td>    

{BODY}

  </td>
 </tr>
</table>
</body>
</html>
