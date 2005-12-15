%define packagename eGroupWare
%define egwdirname egroupware
%define version 1.2RC5
%define packaging 1
%define epoch 0
%define httpdroot  %(if test -f /etc/SuSE-release; then echo /srv/www/htdocs; else echo /var/www/html; fi)

Name: %{packagename}
Version: %{version}
Release: %{packaging}
Epoch: %{epoch}
Summary: eGroupWare is a web-based groupware suite written in php.
                                                                                                                             
Group: Web/Database
License: GPL/LGPL
URL: http://www.egroupware.org/
Source0:  http://download.sourceforge.net/egroupware/eGroupWare-%{version}-%{packaging}.tar.bz2
BuildRoot: /tmp/%{packagename}-buildroot
Requires: php >= 4.3
                                                                                                                             
Prefix: %{httpdroot}
Buildarch: noarch
AutoReqProv: no
                                                                                                                             
Vendor: eGroupWare
Packager: eGroupWare <RalfBecker@outdoor-training.de>

%description
eGroupWare is a web-based groupware suite written in PHP. 

This package provides the eGroupWare default applications:

egroupware core with: admin, api, docs, etemplate, prefereces and setup, 
addressbook, bookmarks, calendar, translation-tools, emailadmin, felamimail, 
filemanager, infolog, jinn, manual, mydms, news admin, knowledgebase, polls, 
projectmanager, resources, sambaadmin, sitemgr, syncml, wiki workflow

It also provides an API for developing additional applications. 

Further contributed applications are avalible in single packages.

%prep
%setup -n %{egwdirname}

%build

%install
[ "%{buildroot}" != "/" ] && rm -rf %{buildroot}
mkdir -p $RPM_BUILD_ROOT%{prefix}/egroupware
cp -aRf * $RPM_BUILD_ROOT%{prefix}/egroupware
rm -f $RPM_BUILD_ROOT%{prefix}/%{egwdirname}/.htaccess
#cp .htaccess $RPM_BUILD_ROOT%{prefix}/%{egwdirname}

%clean
[ "%{buildroot}" != "/" ] && rm -rf %{buildroot}

%post
                                                                                                                             
    echo "***************************************************"
    echo "* Attention: You must create the FILES directory  *"
    echo "* manually outside the document root of your      *"
    echo "* webserver:                                      *"
    echo "* eg. docroot: /var/www/html                      *"
    echo "*     FILES:   /var/www/egwfiles/                 *"
    echo "* Give the webserver the rights to read and write *"
    echo "* and no anonymous access to this folders         *"
    echo "* *************************************************"                                                
    echo "* Please secure you apache and add                *"
    echo "* the follow lines to you httpd.conf              *"
    echo "*                                                 *"
    echo "* <Directory /var/www/html/egroupware>            *"
    echo "*   <Files ~ "\.inc\.php$">                       *"
    echo "*      Order allow,deny                           *"
    echo "*      Deny from all                              *"
    echo "*    </Files>                                     *"
    echo "* </Directory>                                    *"
    echo "***************************************************"

%postun

%files
%defattr(0744,root,root)
%dir %{prefix}/%{egwdirname}
%{prefix}/%{egwdirname}/about.php
%{prefix}/%{egwdirname}/anon_wrapper.php
%{prefix}/%{egwdirname}/header.inc.php.template
%{prefix}/%{egwdirname}/.htaccess
%{prefix}/%{egwdirname}/index.php
%{prefix}/%{egwdirname}/login.php
%{prefix}/%{egwdirname}/logout.php
%{prefix}/%{egwdirname}/notify.php
%{prefix}/%{egwdirname}/notify_simple.php
%{prefix}/%{egwdirname}/notifyxml.php
%{prefix}/%{egwdirname}/redirect.php
%{prefix}/%{egwdirname}/rpc.php
%{prefix}/%{egwdirname}/set_box.php
%{prefix}/%{egwdirname}/soap.php
%{prefix}/%{egwdirname}/xajax.php
%{prefix}/%{egwdirname}/xmlrpc.php
%{prefix}/%{egwdirname}/CVS
%{prefix}/%{egwdirname}/admin
%{prefix}/%{egwdirname}/doc
%{prefix}/%{egwdirname}/etemplate
%{prefix}/%{egwdirname}/home
%{prefix}/%{egwdirname}/phpgwapi
%{prefix}/%{egwdirname}/preferences
%{prefix}/%{egwdirname}/setup
%{prefix}/%{egwdirname}/addressbook
%{prefix}/%{egwdirname}/bookmarks
%{prefix}/%{egwdirname}/browser
%{prefix}/%{egwdirname}/calendar
%{prefix}/%{egwdirname}/chatty
%{prefix}/%{egwdirname}/comic
%{prefix}/%{egwdirname}/developer_tools
%{prefix}/%{egwdirname}/emailadmin
%{prefix}/%{egwdirname}/felamimail
%{prefix}/%{egwdirname}/filemanager
%{prefix}/%{egwdirname}/filescenter
%{prefix}/%{egwdirname}/infolog
%{prefix}/%{egwdirname}/jinn
%{prefix}/%{egwdirname}/manual
%{prefix}/%{egwdirname}/mydms
%{prefix}/%{egwdirname}/news_admin
%{prefix}/%{egwdirname}/phpbrain
%{prefix}/%{egwdirname}/polls
%{prefix}/%{egwdirname}/projectmanager
%{prefix}/%{egwdirname}/registration
%{prefix}/%{egwdirname}/resources
%{prefix}/%{egwdirname}/sambaadmin
%{prefix}/%{egwdirname}/sitemgr
%{prefix}/%{egwdirname}/syncml
%{prefix}/%{egwdirname}/wiki
%{prefix}/%{egwdirname}/workflow

%changelog
* Thu Dez 15 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC5-1
- creation of new groups in LDAP working again
- no more negative id's in the account-table (auto column) itself, 
  as not all DBMS can deal with it (mapping is done in the class now)
- infolog list shows (optional) the times and can switch details on and off 
- projectmanager records and shows now the resources and details of the elements
- wiki is include in the linkage system now
- new instant messenger application chatty in contrib
- other bugfixes and translation updates

* Fri Dez 02 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC4-1
- Bugfixes in Kalendar: Freetimesearch, disabled not working stuff under IE
- MyDMS install: boolean columns are now created correct under mysql4+5
- registration with email approval working again
- workflow and vfs/filemanager fixed to deal with negative group-ids
- setup: charset-conversation now via backup, deinstall & reinstall backup
- xmlrpc: fixes in calendar and infolog
- fixed several other bugs

* Mon Nov 28 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC3-1
- fixed registration app, is not longer in contrib now
- fixed egroupware zip, which wrongly included the contrib stuff
- fixed several other bugs

* Fri Nov 25 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC2-3
- fixed not working account creation
- fixed not working category creation in sitemgr

* Fri Nov 25 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC2-2
- fixed bug which prefented installation under php4 of RC2.
- some minor bug-fixes happening this morning

* Thu Nov 24 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC2-1
- calendar now fully supports groups as participatns and xmlrpc is working again
- group-id's are now negative to improve ldap support
- modified logo and look for the 1.2 idots template
- bugfixes in many areas

* Mon Nov 14 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.2RC1-1
- first release candidate of the upcomming 1.2 release:
- complete rewrite of the calendar, plus new resource booking system
- new projectmanager applications using infolog and calendar data
- syncml to synchronise cell-phones, PDA's and outlook
- workflow application
- and many more ...

* Tue Sep 20 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.0.0.009-3
- disabled the xmlrpc log again by default
- fixed addressbook bug introduced by a backported bugfix from HEAD

* Mon Sep 12 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.0.0.009-2
- further xmlrpc security fixes (already included in the tgz from mid Aug)
- xmlrpc and soap subsystem is now deactivated by default, it can be enabled
  via Admin >> site configuration if needed

* Fri Jul 16 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.0.0.008-2
- Fixed projects problem (editing of project not working, dates are allways 
  set to ~ 1970-01-01) introduced by security fix between 007 and 008

* Fri Jul 08 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.0.0.008-1
- Fixed xmlrpc security problems

* Sat Apr 15 2005 Ralf Becker <RalfBecker@outdoor-training.de> 1.0.0.007-2
- Fixed security problems reported by James from GulfTech Security Research
- new croation translations, significant enhancements in other languages
- many Bugfixes, see http://egroupware.org/cvschangelog-1.0/

* Sat Nov 06 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.006-1
- Fix a security problem in JiNN application
- Bugfixes

* Wed Sep 08 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.005-1
- Bugfix release

* Thu Aug 24 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.004-2
- Bugfix for Email after security patch

* Mon Aug 23 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.004-1
- Security release fixes several XSS problems

* Sat Aug 07 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.003-1
- Final 1.0 release from eGroupWare
- some bugs fixed

* Sat Jul 31 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.002-1
- critical bugs fixed
- MS SQL server support is back
- language extensions

* Sun Jul 11 2004 Reiner Jung <r.jung@creativix.net> 1.0.00.001-1
- bug fixing in all applications

* Thu Jun 29 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.026-1
- JiNN extended.
- projects updated
- new knowledge base available
- new language available Catalan
- many languages updated
- bug fixes in all applications
- extend the usage of indexes for DB tables

* Thu Apr 27 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.015-1
- rewrite of projects added.
- Wiki with WYSIWYG editor added
- bugfixes for sitemgr
- email don't need longer php-imap module, many bugfixes for email included
- Traditional Chinese lang updated
- Danish lang updated
- Italien lang files updated
- Russian translation started
- jerryr template updated
- many bugs fixed in all applications

* Wed Mar 03 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.014-1
- add support to spec file for SuSE directory structure.
  When you want build packages for SuSE, please download the source RPM and make
  rpmbuild --rebuild eGroupWare.xxxxx.spec.
- extensions to Danish language
- extensions at sitemgr
- bugfixes for upcomming 1.0 release

* Sat Feb 07 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.013-2
- RC3-4 bugfix for broken calender ACL

* Sat Feb 07 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.013-1
- Release RC3-3 is only a small bugfixing for some installations
- PostgreSQL bug fixed
- Email Bug fixed
- Login problem on some clients fixed

* Wed Jan 28 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.012-2
- We use the download problem at out server buf fix some other problems

* Wed Jan 28 2004 Reiner Jung <r.jung@creativix.net> 0.9.99.012
- remove justweb template
- Skel app added as package
- Messenger back in eGW
- Spanish translation finished
- Ukrain translation 50% finished
- extensions on Italian translation
- backup rewrite
- Poll upp is rewrited
- Knowledge Base rewrite (start from new killer app support center)
- sitemgr fist preview of 1.0 candidate
- extension on idots
- new template set included jerryr (preview to 1.0 version)
- felamimail extension (folders)
- email bugfixes and extensions
- username case sensitive
- encrytion from passwords for header.inc.php and database passwords added
- JiNN CMS updated
- addressbook import extended
- wiki some extensions
- many Bugs fixed
- fudforum available in a updated version

* Mon Dec 22 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.008-2
- Bug fix for PostgreSQL error.

* Mon Dec 22 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.008-1
- Many Bugs fixed.
- Extension in Idots
- fudforum updated
- Registration application working again

* Mon Dec 08 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.008
- Many Bugs fixed.
- First available version from phpldapadmin
- Dutch, Slovenia, Brasilien Portuguese and Chinese translation extended
- mass delete entries in calender
- setup support DB ports

* Mon Nov 03 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.006
- Many Bugs fixed.
- First available version from FUDeGW forum
- pre checking the php and folders
- idots template extended

* Fri Oct 10 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.005
- Many Bugs fixed.
- TTS with Petri Net Support
- CSV import to Calendar, Infolog
- Experimental, internal usage from UTF-8 available
- Projects app extendet and 1st preview from gant charts available
- Simplified Chinese translation added
- New layout for setup

* Wed Sep 25 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.004
- Bugfix release.
                                                                                
* Mon Sep 08 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.001
- update possibility via CVS
- Headlines bugfixes and new gray theme
- Import from new anglemail
- small changes and bugfixes in Infolog
- calendar show now phone calls, notes and todos
- asyncservice problem fixed
- wiki bugfixes
- felamimail
- improved displaying of messages. added some javascript code, to make switching beetwen message, attachments and header lines faster. Updated the layout of the main page and the message display page to look better. Added support for emailadmin. felamimail needs now emailadmin to beinstalled.

* Sat Aug 30 2003 Reiner Jung <r.jung@creativix.net> 0.9.99.000
- initial eGroupWare package anouncement.

