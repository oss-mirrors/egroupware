<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>bookmarker: tree</title>
<!-- $Id$ -->
<style>
<!--
 html,body { background-color: white;
             font-family: "Arial, Helvetica"; }

 a:LINK    { color: blue; }
 a:VISITED { color: #000066; }
 a:ACTIVE  { color: red; }
 a:HOVER   { color: black; }

 table.hdr { background-color: #5B68A6;
             color: white; }
 big.hdr    { color: white; }
 small.hdr  { color: #CCCCCC; }

 a.hdr:LINK    { color: #CCCCCC; }
 a.hdr:VISITED { color: #CCCCCC; }
 a.hdr:ACTIVE  { color: #CCCCCC; }
 a.hdr:HOVER   { color: black; }

-->
</style>

<script type="text/javascript">
<!-- start Javascript
// This program uses a modified version of the
// JavaScript Tree originally developed by
// Matt Kruse at http://mkruse.netexpress.net/scripts/tree/

// Reload current page.
function reload_page() {
  location.reload(true);
}

// Get current cookie setting
// cookie is stored as "currState=nnn;" where n
// is either 1 representing expanded or 0 
// representing collapsed. A bit is stored for each
// entry in the tree.
var current=getCurrState()
function getCurrState() {
  var label = "currState="
  var labelLen = label.length
  var cLen = document.cookie.length
  var i = 0
  while (i < cLen) {
    var j = i + labelLen
    if (document.cookie.substring(i,j) == label) {
      var cEnd = document.cookie.indexOf(";",j)
      if (cEnd == -1) { cEnd = document.cookie.length }
      return unescape(document.cookie.substring(j,cEnd))
    }
    i++
  }
  return ""
}

// Add an entry to the database
function dbAdd(mother,display,URL,indent,top,newitem,id) {
  db[total] = new Object;
  db[total].mother = mother
  db[total].display = display
  db[total].URL = URL
  db[total].indent = indent
  db[total].top = top
  db[total].newitem = newitem
  db[total].id = id
  total++
  }

// Record current settings in cookie
function setCurrState(setting) {
  var expire = new Date();
  expire.setTime(expire.getTime() + ( 7*24*60*60*1000 ) ); // expire in 1 week
  document.cookie = "currState=" + escape(setting) + "; expires=" + expire.toGMTString();
  }

// toggles an outline mother entry, storing new value in the cookie
function toggle(n) {
  if (n != 0) {
    var newString = ""
    var expanded = current.substring(n-1,n) // of clicked item
    newString += current.substring(0,n-1)
    newString += expanded ^ 1 // Bitwise XOR clicked item
    newString += current.substring(n,current.length)
    setCurrState(newString) // write new state back to cookie
  }
}

// returns padded spaces (in mulTIPles of 2) for indenting
function pad(n) {
  var result = ""
  for (var i = 1; i <= n; i++) { result += "&nbsp;&nbsp;&nbsp;&nbsp;" }
  return result
}

// Expand everything
function explode() {
  current = "";
  initState="";
  for (var i = 1; i < db.length; i++) { 
    initState += "1"
    current += "1"
    }
  setCurrState(initState);
  window.history.go(0);
}

// Collapse everything
function contract() {
  current = "";
  initState="";
  for (var i = 1; i < db.length; i++) { 
    initState += "0"
    current += "0"
    }
  setCurrState(initState);
  window.history.go(0);
//window.location.reload(false);  
}

function tree_close() {
	window.close();
}

var total=1;
var db = new Array();

{BOOKMARK_JS}
// end Javascript -->
</script>
</head>
<BODY bgcolor="#FFFFFF">

<!-- header for tree page -->
<table width=100% cellspacing=0 cellpadding=3 border=0 bgcolor=#CCCCCC class=hdr>
<tr>
  <td align=left width=50%>
    <strong class=hdr>tree</strong>
  </td>
  <td align=right width=50% valign=top>
    <small><a class=hdr href="javascript:reload_page()" class=std>reload</a></small>
    <a class=hdr href="javascript:contract();" title="minimize"><IMG SRC="{IMAGE_URL_PREFIX}minimize.{IMAGE_EXT}" WIDTH=16 HEIGHT=14 BORDER=0 ALT="Minimize"></a><a class=hdr href="javascript:explode();" title="maximize"><IMG SRC="{IMAGE_URL_PREFIX}maximize.{IMAGE_EXT}" WIDTH=16 HEIGHT=14 BORDER=0 ALT="Maximize"></a><a class=hdr href="javascript:tree_close();" title="close"><IMG SRC="{IMAGE_URL_PREFIX}x.{IMAGE_EXT}" WIDTH=16 HEIGHT=14 BORDER=0 ALT="Close"></a></td>
</tr>
<tr>
  <td colspan=2>
  <form action="{FORM_ACTION}" method="GET">
  <table width="100%" border=0 cellspacing=0 cellspacing=0 bgcolor="#DDDDDD">
  <tr>
    <td width="50%" valign="bottom"><small><strong>Saved Searches:</strong></small></td>
    <td width="50%" valign="bottom" align=right><small>Group By<input type=checkbox name=groupby {GROUPBY_DEFAULT} value=1></small></td>
  </tr>
  <tr>
    <td valign="top" colspan=2><small>{SEARCH_SELECT}
    <input type=submit name=bks_load value="Load"><br>
    <font color="green">{FILTER_MSG}</font></small>
    </td>
  </tr>
  </table>
  </form>
  </td>
</tr>
</table>
{MESSAGES}

<!-- tree -->
<table border=0 width=500>
  <tr>
  <td>
   <font size="-1">

<script type="text/javascript">
<!-- start Javascript
// Set the initial state if no current state or length changed
if (current == "" || current.length != (db.length-1)) {
  current = ""
  initState = ""
  for (i = 1; i < db.length; i++) { 
    initState += "0"
    current += "0"
    }
  setCurrState(initState)
  }
var prevIndentDisplayed = 0
var showMyDaughter = 0

// the Outline variable will hold the HTML that we generate 
var Outline=""
var doc_image=""

// cycle through each entry in the db array
for (var i = 1; i < db.length; i++) {
  var currIndent = db[i].indent           // get the indent level
  var expanded = current.substring(i-1,i) // current state
  var top = db[i].top
  if (top == "") { top="content" }
  // display entry only if it meets one of three criteria
  if ((currIndent == 0 || currIndent <= prevIndentDisplayed || (showMyDaughter == 1 && (currIndent - prevIndentDisplayed == 1)))) {
  Outline += pad(currIndent)

  // Insert the appropriate IMAGE and HREF
  newitem = "";
  if (db[i].newitem) { newitem="_new"; }
  
  // if lowest level, show document image
  if (!(db[i].mother)) {  
    doc_image = "<IMG SRC=\"{IMAGE_URL_PREFIX}blank.{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\" \"><IMG SRC=\"{IMAGE_URL_PREFIX}document" + newitem + ".{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\"Document\">"
	
	  if (db[i].id > 0) {
	  // add the link to open this bookmark in the bookmark maintain page
        Outline += "<A HREF=\"maintain.php3?id=" + db[i].id + "\" onMouseOver=\"window.parent.status=\'Click to maintain\';return true;\" TARGET=\"bk_app\" TITLE=\"Maintain this bookmark\">" + doc_image + "</A>"
	  } else {
	    Outline += doc_image
	  }
	
    } 
  else { 
  // if expanded show minus image
      if (current.substring(i-1,i) == 1) {
        Outline += "<A HREF=\"javascript:history.go(0)\" onMouseOver=\"window.parent.status=\'Click to collapse\';return true;\" onClick=\"toggle(" + i + ")\">"
        Outline += "<IMG SRC=\"{IMAGE_URL_PREFIX}minus.{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\"Collapse\"><IMG SRC=\"{IMAGE_URL_PREFIX}open" + newitem + ".{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\"Open\">"
        Outline += "</A>"
        }
  // if collapsed show plus image
      else {
	    Outline += "<A HREF=\"javascript:history.go(0)\" onMouseOver=\"window.parent.status=\'Click to expand\';return true;\" onClick=\"toggle(" + i + ")\">"
        Outline += "<IMG SRC=\"{IMAGE_URL_PREFIX}plus.{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\"Expand\"><IMG SRC=\"{IMAGE_URL_PREFIX}closed" + newitem + ".{IMAGE_EXT}\" WIDTH=16 HEIGHT=16 BORDER=0 ALT=\"Closed\">"
        Outline += "</A>"
        }
      }
    Outline += "&nbsp;";
     
    if (db[i].URL == "" || db[i].URL == null) {
      Outline += " " + db[i].display      // no link, just a listed item  
      }
    else {

	// add the link to open the actual bookmark URL in another window
	  Outline += "<A HREF=\"" + db[i].URL + "\" TITLE=\"" + db[i].URL + "\" TARGET=\"" + top + "\" >" + db[i].display + "</A>"
      }

	// Bold if at level 0
    if (currIndent == 0) { 
      Outline = "<strong><font color=#5B68A6>" + Outline + "</font></strong>"
      }
    Outline += "<BR>"
    prevIndentDisplayed = currIndent
    showMyDaughter = expanded
    // if (i == 1) { Outline = ""}
//    if (db.length > 25) {
      document.write(Outline)
      Outline = ""
//      }
    }
  }
document.write(Outline)
// end Javascript -->
</script>

   </font>
  </td>
  </tr>
</table>

<!-- footer for tree page -->
<table width=100% cellspacing=0 cellpadding=3 border=0>
  <tr>
   <td align=left  bgcolor=#5B68A6 width=100%>
    <!-- <strong  class=std>bookmarker at renaghan.com</strong> -->
   </td>
  </tr>
</table>

</body>  
</html>
