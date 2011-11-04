<?php

/**
 * Protocol filter daemon controller.
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

require clearos_app_base('base') . '/controllers/daemon.php';

use \clearos\apps\firewall\Firewall as Firewall;

clearos_load_library('firewall/Firewall');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Protocol filter daemon controller.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

class Server extends Daemon
{
    function __construct()
    {
        parent::__construct('l7-filter', 'protocol_filter');
    }

    /**
     * Restarts firewall after daemon start.
     *
     * The firewall must be restarted after l7-filter is started.
     *
     * @return void
     */

    public function start()
    {
        parent::start();

        $firewall = new Firewall();
        $firewall->Restart();
    }

    /**
     * Restarts firewall after daemon stop.
     *
     * The firewall must be restarted after l7-filter is stopped.
     *
     * @return void
     */

    public function stop()
    {
        parent::stop();

        $firewall = new Firewall();
        $firewall->Restart();
    }
}
