###############################################################################
# This is the readme for eGWOSync, a plugin for synchronizing eGroupWare with 
# Microsoft Outlook.
#
# This readme created on August 5th, 2004 by Ian Smith-Heisters
# Unless otherwise noted all the included files are distributed under the GPL.
###############################################################################

eGWOSync is a plugin that allows users to access eGroupWare from within 
Outlook. At this point it is only tested under Windows with Outlook 2000 and 
later, though it may be possible to compile the source for a Mac version.

Please send feedback to someone on the contact list below.

Features:
	- Download and upload contacts to/from the eGW server
	- Filter and search contacts
	- Automatically add contacts to eGW on creation
	- Overwrite Protection avoids duplicates and accidental overwrites

TODO:
	- Test
	- Fix bugs
	- Repeat 1 & 2 until its perfect
	- Figure out some sort of digital signature for security
	- Improve automatic uploading / downloading
	- Add an interface more like Exchange, where all contacts are kept
	  on the server
	- Calendar Synchronization
	- Note, todo, and email sharing synchronization

Requirements:
	- A post August 2nd, 2004 version of eGroupWare
	- Outlook 2000 or later

Installation:
	At this point eGWOSync does not have a digital signature certificate,
	so you must set Outlook's Macro security to "Low" or "Medium". Do this
	in Outlook under Tools->Macro->Security. Then restart Outlook.
	
	eGWOSync should be pretty easy to install, just double click the 
	installer .exe, select a target directory, and click next. Then 
	startup Outlook and go to town.

Settings:
	Username: your eGroupWare login
	Password: your eGroupWare password
	Port: almost always port 80.
	Host: the server that hosts eGroupWare. If you were connecting to 
	      the egroupware.org server, where you access the egroupware login
	      at "http://www.egroupware.org/egroupware" the host would be
	      "egroupware.org". Note: NO "http" or "www" or "/egroupware".
	URI: almost always egroupware/xmlrpc.php. This is the path to 
	     eGroupWare's xmlrpc.php on the server, relative to the htdocs root

Usage:
	Click on the "eGroupWare Sync Main" button on your Standard Menu Bar.

	Press "Get Contacts" in Import and Export to list all the local and 
	remote contacts. Select the contacts you want to upload and download
	with your mouse. Use Shift and Control to select multiple contacts.
	Click "Synchronize Selected Contacts" to upload the selected local
	contacts and download the selected remote contacts.
	
	When you add a new contact in Outlook, it should automatically ask
	you whether you also want to add it to the eGW server.

Developers:
	Ian Smith-Heisters
	Esben Laursen
	Chris Carter

Contacts:
	egroupware-users[at]lists.sourceforge.net
	egroupware-developers[at]lists.sourceforge.net
	Ian Smith-Heisters heisters[at]0x09.com
	Esben Laursen hyber[at]hyber.dk

Thanks:
	Ralf Becker, Chris Carter, Reiner Jung, Lars Kneschke, Carsten Wolff 
	and the rest of	the eGroupWare development team. Cunningham Dance 
	Foundation and Marlboro College for sponsoring the intitial 
	developement.