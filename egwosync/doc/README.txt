|-----------------------------------------------------------------------------|
|This is the official readme for eGWOSync, a module for synchronizing         |
|eGroupWare with Microsoft Outlook.                                           |
|                                                                             |
|This readme created on July 13th, 2004 by Ian Smith-Heisters                 |
|-----------------------------------------------------------------------------|

The current eGWOSync version is just functional and not by any means meant to
be complete or bug free. Email the egroupware-developers list if you are having
trouble using it, have found a bug, want a new feature, want to help develop
it, or just have a question. Or email me: heisters[at]0x09.com

All files in the eGWOSync module are distributed under the GPL unless 
otherwise stated.

INSTALLATION:

1. Obtain vbXML.dll and vbXMLRPC.dll which are available from 
   http://www.enappsys.com/backend.jsp or should be included on the CVS where
   eGWOSync is available. Refer to the installation instruction provided with
   them.

2. Enable Macros in Outlook. Launch Outlook and go to Tools->Macro->Security
   and set it to Medium or Low. This requires you to restart Outlook to take
   effect, even though Outlook doesn't say this.

3. Launch the Outlook VBA Editor. In Outlook go to Tools->Macro->VB Editor
   or just press Alt+F11.

4. Register the vbXML(RPC).dlls for the Outlook VBA project. In the Outlook
   Visual Basic Editor go to Tools->References, find vbXML and vbXMLRPC and 
   enable them by checking the box next to their names.

5. Import the project files. In the Outlook VBA Editor go to File->Import File
   and import the project files. These will need to be done one at a time. For
   the current release these files are:
       BasDebugUtils.bas
       BasEGWOSync.bas
       BasUtilities.bas
       CContactTranslator.cls
       CeGW.cls
       CeGWContacts.cls
       COutlookContacts.cls
       CRegistry.cls
       frmMain.frm
       frmMain.frx

6. Save the project.

7. There is a conflict with the date format that needs to be fixed, but the
   current fix may break other XMLRPC clients trying to access your eGW
   server. The way I'm doing it at the moment is to copy 
	   egwosync/auxillary/class.xmlrpc_server.inc.php
	   egwosync/auxillary/class.xmlrpc_server.php.inc.php
   to egroupware/phpgwapi/inc/. Be sure to back up the originals in 
   phpgwapi/inc/ first. This will make the KDE pim client unable to access
   the XMLRPC server, and possibly other clients as well, but will make
   Outlook able. There will be a more satisfactory fix soon.

8. Go back to Outlook and go to Tools->Macro->Macros, select eGWSynchronize,
   and click run.

USING EGWOSYNC:

1. Fill in fields as follows:
	Username:    your eGW username
	Password:    the password for the eGW account specified by the username
	Port:	     the port eGW listens on. Usually port 80.
	Hostname:    ONLY the hostname of your eGW server. For example:
			  If you access eGW via the web at 
			  http://www.egroupware.org/egroupware
			  the hostname would be ONLY "egroupware.org".
			  If you access eGW via the web at
			  http://192.168.0.101/egroupware the hostname would be
			  ONLY "192.168.0.101".
	URI:	     The location of egroupware's XML-RPC server (xmlrpc.php)
		     on the remote server, realative to the htdoc root. This 
		     will usually be "/egroupware/xmlrpc.php".

2. Press "Get Contacts" to retrieve the remote contacts from the server and
   list the local contacts. Note that contacts from the eGW server will only be
   listed if the account you logged in with has read access to those contacts.
   Local Outlook contacts are currently listed from the default Contact
   directory and all its subdirectories.

3. Select the contacts you want to import and export using Control and Shift to
   select multiple contacts. Press "Synchronize Selected Contacts" to upload
   the selected local contacts and download the selected remote contacts. If a
   contact by the same name already exists in the target repository you will be
   prompted to either overwrite the existing contact or skip the contact.

