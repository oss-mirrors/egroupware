<!-- $Id$ -->
<li>Right-Click on this 
<A HREF="javascript:bk2='{MAIL_THIS_LINK_URL}?murl='+escape(location.href)+'&mtitle='+escape(document.title);window.open(bk2,'bkml','width=620,height=500,scrollbars=1,resizable=1');window.focus();" OnClick = "alert('You must right-click this link, not left-click.'); return false;" >mail-this-link</A>
link and select &quot;Add to Favorites&quot;. Please note that there is a bug in Microsoft's implementation of Javascript (big shocker!) which means I had to spend extra time investigating and implementing a workaround so that the mail-this-link popup window will come to the front when it is opened.
