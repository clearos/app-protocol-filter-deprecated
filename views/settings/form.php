<?php

/**
 * Protocol filter pattern view.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('protocol_filter');

///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons = array(form_submit_update('submit', 'high'));

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('protocol_filter_protocol'),
    lang('base_description'),
    lang('base_category'),
    lang('base_information')
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($protocols as $name => $details) {
    $item['title'] = $name;
    $item['name'] = 'protocols[' . $name . ']';
    $item['state'] = $details['enabled'];
    $item['details'] = array(
        $details['name'],
        $details['description'],
        $details['category'],
        "<a href='" . $details['url'] . "'>ext. link</a>" // FIXME
    );

    $items[] = $item;
}

///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo form_open('protocol_filter/settings');

echo list_table(
    lang('protocol_filter_protocols'),
    $buttons,
    $headers,
    $items
);

echo form_close();
