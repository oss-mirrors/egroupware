%define packagename skel
%define phpgwdirname skel
%define version 0.0.1.000

# This is for Mandrake RPMS 
# (move these below the RedHat ones for Mandrake RPMs)
%define httpdroot  /var/www/html/phpgroupware
%define packaging 1mdk

# This is for RedHat RPMS
# (move these below the Mandrake ones for RedHat RPMs)
%define httpdroot  /home/httpd/html/phpgroupware
%define packaging 1

Summary: Skeleton App for phpGroupWare. 
Name: %{packagename}
Version: %{version}
Release: %{packaging}
Copyright: GPL
Group: Web/Database
URL: http://www.phpgroupware.org/apps/skel/
Source0: ftp://ftp.sourceforge.net/pub/sourceforge/phpgroupware/%{packagename}-%{version}.tar.bz2
BuildRoot: %{_tmppath}/%{packagename}-buildroot
Prefix: %{httpdroot}
Buildarch: noarch
requires: phpgroupware >= 0.9.10
AutoReq: 0

%description
This is a small skeleton application, which can be used as a basic for starting a new application
or for making sure that an existing one follows the examples and guidelines shown in this app.

%prep
%setup -n %{phpgwdirname}

%build
# no build required

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT%{prefix}/%{phpgwdirname}
cp -aRf * $RPM_BUILD_ROOT%{prefix}/%{phpgwdirname}

%clean
rm -rf $RPM_BUILD_ROOT

%post

%postun

%files
%{prefix}/%{phpgwdirname}

%changelog
* Sat May 19 2001 Dan Kuykendall <seek3r@phpgroupware.org> 0.0.1.00
- Created skel app

# end of file