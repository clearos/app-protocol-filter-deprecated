<?php

/**
 * Protocol filter (l7-filter) firewall class.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2009-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\protocol_filter;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('protocol_filter');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\firewall\Firewall as Firewall;
use \clearos\apps\firewall\Rule as Rule;

clearos_load_library('firewall/Firewall');
clearos_load_library('firewall/Rule');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Protocol filter (l7-filter) firewall class.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2009-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

class L7_Firewall extends Firewall
{
    ///////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////

    /**
     * L7_Firewall constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct();
    }

    /**
     * Adds a protocol filter host exception.
     *
     * @param string $name exception nickname
     * @param string $ip   IP address
     *
     * @return void
     * @throws Engine_Exception
     */

    public function add_exception($name, $ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($name));
        Validation_Exception::is_valid($this->validate_ip($ip));

        $rule = new Rule();

        $rule->set_name($name);
        $rule->set_address($ip);
        $rule->set_flags(Rule::L7FILTER_BYPASS | Rule::ENABLED);

        $this->add_rule($rule);
    }

    /**
     * Remove a protocol filter exception rule.
     *
     * @param string $ip IP address
     *
     * @return void
     * @throws Engine_Exception
     */

    public function delete_exception($ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_ip($ip));

        $rule = new Rule();

        $rule->set_address($ip);
        $rule->set_flags(Rule::L7FILTER_BYPASS | $network);
        $this->delete_rule($rule);
    }

    /**
     * Sets the status of a protocol exception rule.
     *
     * @param boolean $status status
     * @param string  $ip     IP address
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_exception_status($status, $ip)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_ip($ip));

        $rule = new Rule();

        $rule->set_address($ip);
        $rule->set_flags(Rule::L7FILTER_BYPASS);

        if (!($rule = $this->find_rule($rule)))
            return;

        $this->delete_rule($rule);

        if ($status)
            $rule->enable();
        else
            $rule->disable();

        $this->add_rule($rule);
    }

    /**
     * Returns an array of protocol filter exceptions.
     *
     *  info[name]
     *  info[ip]
     *  info[enabled]
     *
     * @return array array list containing protocol filter exceptions 
     * @throws Engine_Exception
     */

    public function get_exceptions()
    {
        clearos_profile(__METHOD__, __LINE__);

        $exceptions = array();

        $rules = $this->get_rules();

        foreach ($rules as $rule) {
            if (!($rule->get_flags() & Rule::L7FILTER_BYPASS))
                continue;

            $info = array();
            $info['name'] = $rule->get_name();
            $info['ip'] = $rule->get_address();
            $info['enabled'] = $rule->is_enabled();

            $exceptions[] = $info;
        }

        return $exceptions;
    }

    /**
     * Returns state of the protocol filter.
     *
     * @return boolean TRUE if protocol filter is enabled
     * @throws Engine_Exception
     */

    public function get_protocol_filter_state()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->get_state('PROTOCOL_FILTERING');
    }

    /**
     * Sets state of the protocol filter.
     *
     * @param boolean $state state of protocol filter 
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_protocol_filter_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->set_state($state, 'PROTOCOL_FILTERING');
    }
}
