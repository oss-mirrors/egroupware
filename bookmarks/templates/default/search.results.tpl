<!-- $Id$ -->
<script type="text/javascript">
<!-- start Javascript
// Browsers seem to like the popup window open commands
// in functions rather than directly inside the anchor tag.
// This way the window pops as disconnected from the parent.
  function pop_tree_search() {
    window.open("{TREE_SEARCH_URL}", "treesearch", "scrollbars,resizable,height=640,width=250");
  }
// end Javascript -->
</script>

<hr>
<small>{LANG_QUERY_CONDITION} = {QUERY_CONDITION}
&nbsp;&nbsp;&nbsp;
<!-- <a href="javascript:pop_tree_search()">Open Results in Tree View</a> -->
</small><br>

{BOOKMARK_LIST}
