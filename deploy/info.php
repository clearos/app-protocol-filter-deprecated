<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'protocol_filter';
$app['version'] = '5.9.9.5';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('protocol_filter_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('protocol_filter_app_name');
$app['category'] = lang('base_category_gateway');
$app['subcategory'] = lang('base_subcategory_protocol_filter');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['protocol_filter']['title'] = lang('protocol_filter_app_name');
$app['controllers']['exceptions']['title'] = lang('protocol_filter_exceptions');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-network',
);

$app['core_requires'] = array(
    'app-network-core',
    'app-firewall-core',
    'l7-filter-userspace >= 0.12',
    'l7-protocols- >= 0.12',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/protocol_filter' => array(),
    '/var/clearos/protocol_filter/backup/' => array(),
);

$app['core_file_manifest'] = array(
    'l7-filter.php'=> array('target' => '/var/clearos/base/daemon/l7-filter.php'),
);

