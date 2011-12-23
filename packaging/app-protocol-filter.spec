
Name: app-protocol-filter
Version: 6.2.0.beta3
Release: 1%{dist}
Summary: Protocol Filter
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = %{version}-%{release}
Requires: app-base
Requires: app-network

%description
The Protocol Filter is an application layer packet classifier.  The service attempts to identify an application as data packets pass through the gateway and classify them according to known protocols.  If successfully identified, user sessions can be blocked based on an administrator's preference and/or policy.

%package core
Summary: Protocol Filter - APIs and install
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network-core
Requires: app-firewall-core
Requires: l7-filter-userspace >= 0.12
Requires: l7-protocols >= 0.12

%description core
The Protocol Filter is an application layer packet classifier.  The service attempts to identify an application as data packets pass through the gateway and classify them according to known protocols.  If successfully identified, user sessions can be blocked based on an administrator's preference and/or policy.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/protocol_filter
cp -r * %{buildroot}/usr/clearos/apps/protocol_filter/

install -d -m 0755 %{buildroot}/var/clearos/protocol_filter
install -d -m 0755 %{buildroot}/var/clearos/protocol_filter/backup/
install -D -m 0644 packaging/l7-filter.php %{buildroot}/var/clearos/base/daemon/l7-filter.php
install -D -m 0644 packaging/protocol_filter.conf %{buildroot}/etc/clearos/protocol_filter.conf

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
%dir /var/clearos/protocol_filter
%dir /var/clearos/protocol_filter/backup/
/usr/clearos/apps/protocol_filter/deploy
/usr/clearos/apps/protocol_filter/language
/usr/clearos/apps/protocol_filter/libraries
/var/clearos/base/daemon/l7-filter.php
/etc/clearos/protocol_filter.conf
