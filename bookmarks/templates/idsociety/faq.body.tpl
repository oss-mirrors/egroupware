<!-- $Id$ -->
<strong>1. <a href="#faq1">How do I login?</a></strong><br>
<strong>2. <a href="#faq2">How do I add new bookmarks?</a></strong><br>
<strong>3. <a href="#faq3">How do I use quik-mark?</a></strong><br>
<strong>4. <a href="#faq4">How do I update a bookmark?</a></strong><br>
<strong>5. <a href="#faq5">How do I remove bookmarks?</a></strong><br>
<strong>6. <a href="#faq6">How to I view my bookmarks?</a></strong><br>
<strong>7. <a href="#faq7">How do I search my bookmarks?</a></strong><br>
<strong>8. <a href="#faq8">How do I add/update/remove categories/sub-categories/ratings?</a></strong><br>
<strong>9. <a href="#faq9">Where do I send my comments and suggestions on bookmarker?</a> </strong><br>
<strong>10. <a href="#faq10">How do I report a bug?</a></strong><br>
<strong>11. <a href="#faq11">Who created bookmarker?</a></strong><br>
<strong>12. <a href="#faq12">How do I use mail-this-link?</a></strong><br>

<hr>
<p class=faq><a name="faq1">1. How do I login?</a>
<p>Every time you attempt an action (search, create...) bookmarker checks to make sure you are a valid user. If you are not, it will ask you to login. So go ahead and try to do something, if bookmarker wants to know who you are it will ask.

<hr>
<p class=faq><a name="faq2">2. How do I add new bookmarks?</a>
<p>Well, obviously the <a href="create.php">create</a> page lets you create bookmarks, but I <strong>highly</strong> recommend you take a few moments and setup <a href="#faq3">quik-mark</a> in your browser. Quik-mark provides a quick way to add bookmarks while you are surfing.
<p>Additionally, you can import your Netscape bookmark/Favorites into bookmarker using the &quot;Import Bookmarks&quot; page under <a href="user.php">User Preferences</a>.
<br><strong>No</strong>, bookmarker cannot (and will never b/c of the format of this data) import Microsoft Internet Explorer bookmarks. There are however <a href="http://help.netscape.com/kb/consumer/19980914-23.html">tools</a> that can convert MSIE Favorites into Netscape bookmarks which you can then import into bookmarker.

<hr>
<p class=faq><a name="faq3">3. How do I use quik-mark?</a>
<p><strong>quik-mark</strong> allows to you quickly bookmark the site you are browsing - right from your browser! To setup quik-mark in your browser
(which appears to be <em>{USER_AGENT}</em>):
  <ul>

{QUIK_MARK_LINK}

    <li>Use the features of your browser to update the bookmark you just added in order to give it a meaningful name (like &quot;quik-mark&quot;).
    <li>Depending on your browser, you may also want to add your new quik-mark bookmark to your browser's toolbar so that it is easily accessible.
  </ul>

<p>Then to use quik-mark:
  <ul>
    <li>Browse around the web and when you find a site you want to add to <strong class=bk>bookmarker</strong>, click the &quot;quik-mark&quot; link in your browser's bookmark list
    <li>This will open a new browser window with the <strong class=bk>bookmarker</strong> create page open and the values defaulted to the URL and Title of the page you were just looking at.
    <li>Update the information to your liking (just like you normally do on the create page) and click CREATE
  </ul>
<p>Note: If you are not yet logged in to <strong class=bk>bookmarker</strong>, you will have to login the first time you use quik-mark.
<p>If you want to change the quik-mark popup window size, you must edit the bookmark stored in your browser. Look for and change the &quot;width=620,height=500&quot; settings.
<hr>
<p class=faq><a name="faq4">4. How do I update a bookmark?</a>
<p>There are a number of ways to get to the update page for a specific bookmark:
<ol>
  <li>From the <a href="list.php">plain list</a> page click the number to the left of the bookmark name. The number represents the unique identifier for that bookmark in the database - this is the key to opening the maintain page with the bookmark loaded
        <li>From the <a href="search.php">search</a>, after you have searched and found the page, click the number to the left of the bookmark name
        <li>From the tree view, click the small document graphic (<IMG SRC="{IMAGE_URL_PREFIX}document.{IMAGE_EXT}" WIDTH=16 HEIGHT=16 BORDER=0>) to the left of the of the bookmark name
</ol>
<p>While on the update page, update any information you wish to change about the bookmark and click the &quot;Change Bookmark&quot; button. Click the underlined &quot;URL&quot; to open the URL of the bookmark.

<hr>
<p class=faq><a name="faq5">5. How do I remove bookmarks?</a>
<p>Follow the instructions above for <a href="#faq4">updating</a> a bookmark. When you get to the update page, just click the &quot;Delete Bookmark&quot; button.


<hr>
<p class=faq><a name="faq6">6. How to I view my bookmarks?</a>
<p>There are a number of ways to see your bookmarks:
<ol>
  <li>Use the <a href="list.php">plain list</a> page to list your bookmarks in alphabetical order sorted by category and sub-category displaying
10 bookmarks per page. Use the &quot;more&quot; link at the bottom of the page to move on to the next set. Click the number to the left of the bookmark name to update that bookmark. Click the bookmark name to open up the URL of the bookmark.
  <li>Use the <a href="search.php">search</a> page to locate bookmarks based on criteria. See <a href="#faq7">below</a>.
        <li>Use the tree view page to view your bookmarks in a tree type structure. The tree is organized by category and sub-category. Click the plus 
(<IMG SRC="{IMAGE_URL_PREFIX}plus.{IMAGE_EXT}" WIDTH=16 HEIGHT=16 BORDER=0>)
graphic to expand (i.e., open) the (sub)category. Click the minus
(<IMG SRC="{IMAGE_URL_PREFIX}minus.{IMAGE_EXT}" WIDTH=16 HEIGHT=16 BORDER=0>)
graphic to collapse (i.e., close) the (sub)category.
Click the small document graphic (<IMG SRC="{IMAGE_URL_PREFIX}document.{IMAGE_EXT}" WIDTH=16 HEIGHT=16 BORDER=0>) to the left of the of the bookmark name to maintain the bookmark. Click the bookmark name to open the URL of the bookmark.
</ol>

<hr>
<p class=faq><a name="faq7">7. How do I search my bookmarks?</a>
<p>Use the <a href="search.php">search</a> page to locate bookmarks based on criteria. You can search using any of the bookmarker fields as part of conditions. You can add more conditions to the search by using the &quot;More&quot; button - use the &quot;Fewer&quot; button to remove conditions.  Click the number to the left of the bookmark name to update that bookmark. Click the bookmark name to open up the URL of the bookmark.
<p>The fields and buttons at the top of the search page (in the colored background) offer you the ability to save searches by name. This lets you keep a list of commonly used searches so that you don't have to re-type them. Use the &quot;Saved Searches&quot; list and nearby action buttons to use and maintain your saved searches. Use the &quot;New Saved Search&quot; field and the &quot;Create&quot; button to give an name to and save the currently displayed search.
<p>If you prefer to see/use the results of your search in a tree style view, simply click the &quot;Open Results in Tree View&quot; link after the your seach has been executed.
<hr>
<p class=faq><a name="faq8">8. How do I add/update/remove categories/sub-categories/ratings?</a>
<p>
Use the <a href="codes.php?codetable=category">category</a>, 
<a href="codes.php?codetable=subcategory">sub-category</a>, and
<a href="codes.php?codetable=rating">ratings</a> links from the <a href="useropt.php">user preferences</a> page to add/update/remove these items.

<hr>
<p class=faq><a name="faq9">9. Where do I send my comments and suggestions on bookmarker?</a>
<p>Use the <a href="http://renaghan.com/pcr/maillist.html">mail form</a> on the <a href="http://renaghan.com/pcr/bookmarker.html">bookmarker</a> home site.

<hr>
<p class=faq><a name="faq10">10. How do I report a bug?</a>
<p>Make sure you read the INSTALL file and this faq. If you still think there is a bug, use the <a href="http://renaghan.com/pcr/bugs.html">bug form</a> on the <a href="http://renaghan.com/pcr/bookmarker.html">bookmarker</a> home site.

<hr>
<p class=faq><a name="faq11">11. Who created bookmarker?</a>
<p><a href="http://renaghan.com/pcr/images/padraic.png">Padraic Renaghan</a> created bookmarker. Visit <a href="http://renaghan.com/pcr/">Padraic's home page</a>, <a href="http://renaghan.com/pcr/mailto.html">send</a> Padraic e-mail, visit the <a href="http://renaghan.com/pcr/bookmarker.html">bookmarker</a> home site.

<hr>
<p class=faq><a name="faq12">12. How do I use mail-this-link?</a>
<p><strong>mail-this-link</strong> allows to you quickly send a bookmark to someone via e-mail. You can send a bookmark you have saved in bookmarker, or the site you are browsing - right from your browser! 

<p>To send a bookmark you have saved in bookmarker, find the bookmark you want to send using the plain list, maintain, or search page. Then click the envelope image (<img align=top border=0 src="{IMAGE_URL_PREFIX}mail.{IMAGE_EXT}">) next to the bookmark you want to send - this will take you to the mail-this-link page where you can send a message to someone with the link.

<p>To setup mail-this-link in your browser
(which appears to be <em>{USER_AGENT}</em>):
  <ul>

{MAIL_THIS_LINK}

    <li>Use the features of your browser to update the bookmark you just added in order to give it a meaningful name (like &quot;mail-this-link&quot;).
    <li>Depending on your browser, you may also want to add your new mail-this-link bookmark to your browser's toolbar so that it is easily accessible.
  </ul>

<p>Then to use mail-this-link:
  <ul>
    <li>Browse around the web and when you have found a site that you want to tell someone about, click &quot;mail-this-link&quot; in your browser's bookmark list
    <li>This will open a new browser window with the <strong class=bk>bookmarker</strong> mail-this-link page open and the values defaulted to the URL and Title of the page you were just looking at.
    <li>Enter the e-mail address of the recipient and update the subject and text of the message. Click the mail-this-link button to send the message.
  </ul>
<p>Note: If you are not yet logged in to <strong class=bk>bookmarker</strong>, you will have to login the first time you use mail-this-link.
<p>If you want to change the mail-this-link popup window size, you must edit the bookmark stored in your browser. Look for and change the &quot;width=620,height=500&quot; settings.
