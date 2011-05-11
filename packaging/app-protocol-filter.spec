
Name: app-protocol-filter
Group: ClearOS/Apps
Version: 5.9.9.0
Release: 1%{dist}
Summary: Translation missing (protocol_filter_app_summary)
License: GPLv3
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = %{version}-%{release}
Requires: app-base

%description
Translation missing (protocol_filter_app_long_description)

%package core
Summary: Translation missing (protocol_filter_app_summary) - APIs and install
Group: ClearOS/Libraries
License: LGPLv3
Requires: app-base-core

%description core
Translation missing (protocol_filter_app_long_description)

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/protocol_filter
cp -r * %{buildroot}/usr/clearos/apps/protocol_filter/


%post
logger -p local6.notice -t installer 'app-protocol-filter - installing'

%post core
logger -p local6.notice -t installer 'app-protocol-filter-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/protocol_filter/deploy/install ] && /usr/clearos/apps/protocol_filter/deploy/install
fi

[ -x /usr/clearos/apps/protocol_filter/deploy/upgrade ] && /usr/clearos/apps/protocol_filter/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-protocol-filter - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-protocol-filter-core - uninstalling'
    [ -x /usr/clearos/apps/protocol_filter/deploy/uninstall ] && /usr/clearos/apps/protocol_filter/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/protocol_filter/controllers
/usr/clearos/apps/protocol_filter/htdocs
/usr/clearos/apps/protocol_filter/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/protocol_filter/packaging
%exclude /usr/clearos/apps/protocol_filter/tests
%dir /usr/clearos/apps/protocol_filter
/usr/clearos/apps/protocol_filter/deploy
/usr/clearos/apps/protocol_filter/language
/usr/clearos/apps/protocol_filter/libraries
