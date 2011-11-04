<?php

/**
 * Protocol filter exceptions controller.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Protocol filter exceptions controller.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

class Exceptions extends ClearOS_Controller
{
    /**
     * Protocol filter exceptions overview.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('protocol_filter/L7_Firewall');
        $this->lang->load('protocol_filter');

        // Load view data
        //---------------

        try {
            $data['exceptions'] = $this->l7_firewall->get_exceptions();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('protocol_filter/exceptions/summary', $data, lang('protocol_filter_exception_list'));
    }

    /**
     * Add exceptions rule.
     *
     * @return view
     */

    function add()
    {
        // Load libraries
        //---------------

        $this->load->library('protocol_filter/L7_Firewall');
        $this->lang->load('base');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('name', 'protocol_filter/L7_Firewall', 'validate_name');
        $this->form_validation->set_policy('ip', 'protocol_filter/L7_Firewall', 'validate_ip', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok)) {
            try {
                $this->l7_firewall->add_exception(
                    $this->input->post('name'),
                    $this->input->post('ip')
                );

                $this->l7_firewall->reset(TRUE);

                $this->page->set_status_added();
                redirect('/protocol_filter/exceptions');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the views
        //---------------

        $this->page->view_form('protocol_filter/exceptions/item', $data, lang('base_add'));
    }

    /**
     * Delete exception view.
     *
     * @param string  $ip IP address
     *
     * @return view
     */

    function delete($ip)
    {
        $confirm_uri = '/app/protocol_filter/exceptions/destroy/' . $ip;
        $cancel_uri = '/app/protocol_filter/exceptions';
        $items = array($ip);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys exception rule.
     *
     * @param string  $ip IP address
     *
     * @return view
     */

    function destroy($ip)
    {
        // Load libraries
        //---------------

        $this->load->library('protocol_filter/L7_Firewall');

        // Handle form submit
        //-------------------

        try {
            $this->l7_firewall->delete_exception($ip);
            $this->l7_firewall->reset(TRUE);

            $this->page->set_status_deleted();
            redirect('/protocol_filter/exceptions');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Disables exception rule.
     *
     * @param string $ip IP address
     *
     * @return view
     */

    function disable($ip)
    {
        try {
            $this->load->library('protocol_filter/L7_Firewall');

            $this->l7_firewall->set_exception_state(FALSE, $ip);
            $this->l7_firewall->reset(TRUE);

            $this->page->set_status_disabled();
            redirect('/protocol_filter/exceptions');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Enables exception rule.
     *
     * @param string $ip IP address
     *
     * @return view
     */

    function enable($ip)
    {
        try {
            $this->load->library('protocol_filter/L7_Firewall');

            $this->l7_firewall->set_exception_state(TRUE, $ip);
            $this->l7_firewall->reset(TRUE);

            $this->page->set_status_enabled();
            redirect('/protocol_filter/exceptions');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
