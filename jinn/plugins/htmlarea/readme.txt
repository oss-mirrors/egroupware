# ----------------------------------------------------------------------------
# htmlArea - Readme File
# Copyright (C) 2002 interactivetools.com, inc., All Rights Reserved
# http://www.interactivetools.com/
# ----------------------------------------------------------------------------
# LICENSE AGREEMENT                                                        
#                                                                            
# Permission is hereby granted, free of charge, to any person obtaining a copy 
# of this software and associated documentation files (the "Software"), to deal 
# in the Software without restriction, including without limitation the rights 
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
# copies of the Software, and to permit persons to whom the Software is 
# furnished to do so, subject to the following conditions:
#
# a) The above copyright notice, this permission notice, and the "About this 
# editor" button that appears as a question mark in the editor interface, shall 
# be included in all copies or substantial portions of the Software. 
#
# b) The "About this editor" button that appears as a question mark ("?") in the 
# editor interface must always be visible in the editor interface and bring up 
# the original "About" dialog window when clicked. 
#
# c) The "About" dialog window and its contents, including the link to 
# interactivetools.com can not be amended. 
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
# THE SOFTWARE.
# ----------------------------------------------------------------------------


Thanks for downloading htmlArea.

This document will explain how to install the program on your web server.  

TABLE OF CONTENTS
--------------------------------------------------
1. About the Script
2. System Requirements
3. Installation
4. About Us
5. Contact



1. About the Script
----------------------------------------------------
htmlArea is a free WYSIWYG editor replacement for <textarea> fields on web forms.

Maybe you're building some kind of weblog software or a feedback forum and you 
want to give users of your software the ability to enter formatted HTML. Instead 
of teaching them the markup tags for bold (<b>), italics (<i>), inserting images 
and building unordered lists, you can replace your standard <textarea> fields 
with htmlArea.

It looks like a familiar WYSIWYG word processing program, so users don't need 
to know any complex HTML code to format their content exactly how they like.



2. System Requirements
----------------------------------------------------
- Internet Explorer 5.5 for Windows or above 


3. Installation
---------------------------------------------------- 
1. Unzip the software files on your local computer.

2. Create a directory on your webserver for htmlArea.
	i.e. /htmlarea/

All our examples are going to assume you're creating a /htmlarea directory off 
your webserver root. If you're having problems, make sure you've got the 
directory correct.
	
3. Using an FTP client, transfer all the htmlArea files from your local 
computer to your webserver into the directory you just created.

4. Open up any HTML document and copy-paste the following text in 
between the <head></head> tags.

You need to customize this for the directory you created on your webserver. 
We're assuming you created a directory off your root called /htmlarea/. 
If you didn't, just modify this block of text in the two locations that 
reference the location of the htmlArea files.

<!-- ---------------------------------------------------------------------- -->
<!-- START : EDITOR HEADER - INCLUDE THIS IN ANY FILES USING EDITOR -->
<script language="Javascript1.2" src="/htmlarea/editor.js"></script>
<script>
// set this to the URL of editor direcory (with trailing forward slash)
// NOTE: _editor_url MUST be on the same domain as this page or the popups
// won't work (due to IE cross frame/cross window security restrictions).
// example: http://www.hostname.com/editor/

_editor_url = "/htmlarea/";
</script>
<style type="text/css"><!--
  .btn   { BORDER-WIDTH: 1; width: 26px; height: 24px; }
  .btnDN { BORDER-WIDTH: 1; width: 26px; height: 24px; BORDER-STYLE: inset; BACKGROUND-COLOR: buttonhighlight; }
  .btnNA { BORDER-WIDTH: 1; width: 26px; height: 24px; filter: alpha(opacity=25); }
--></style>
<!-- END : EDITOR HEADER -->
<!-- ---------------------------------------------------------------------- -->

5. After any <textarea> that you want to convert to an htmlArea, you need 
to put the following code DIRECTLY after the closing </textarea> tag. 

<script language="javascript1.2">
editor_generate('box2'); // field, width, height
</script>

If this is unclear, please see our example.html file included in the zipfile 
you downloaded, which has the proper usage after a <textarea> tag.

6. Upload the modified pages to your web server.

7. Visitor will now be able to create HTML content using a WYSIWYG editor and 
be able to submit it through a form to your software script.


4. About Us
----------------------------------------------------
At interactivetools.com, we make affordable Perl CGI scripts, 
software and tools to enhance your website. 

For more information about our other interactivetools.com products
visit our website at http://www.interactivetools.com/


5. Contact
----------------------------------------------------
We can be contacted at the following email addresses:

info@interactivetools.com		- General Inquiries
sales@interactivetools.com		- Product Inquiries

Please note that we can not offer support for any of our free scripts.  