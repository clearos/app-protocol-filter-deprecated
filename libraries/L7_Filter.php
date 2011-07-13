<?php

/**
 * Protocol filter (l7-filter) class.
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

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Product as Product;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\Software as Software;
use \clearos\apps\firewall\Firewall as Firewall;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Product');
clearos_load_library('base/Shell');
clearos_load_library('base/Software');
clearos_load_library('firewall/Firewall');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Protocol filter (l7-filter) class.
 *
 * @category   Apps
 * @package    Protocol_Filter
 * @subpackage Libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2009-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/protocol_filter/
 */

class L7_Filter extends Daemon
{
    //////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/l7-filter/l7-filter.conf';
    const FILE_CACHE = '/var/clearos/protocol_filter/l7-protocols.cache';
    const PATH_PROTOCOLS = '/etc/l7-filter/protocols';
    const COMMAND_IPTABLES = '/sbin/iptables';

    //////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $protocols = array();
    protected $categories = array();

    protected $patterns = array();
    protected $groups = array();
    protected $supported_groups = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * L7_Filter constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('l7-filter');

        include clearos_app_base('protocol_filter') . '/deploy/protocols.php';

        $this->protocols = $protocols;

        $this->categories = array(
            'chat' => lang('protocol_filter_category_chat'),
            'document_retrieval' => lang('protocol_filter_category_document_retrieval'),
            'file' => lang('protocol_filter_category_file'),
            'game' => lang('protocol_filter_category_game'),
            'all' => lang('protocol_filter_category_all'),
            'mail' => lang('protocol_filter_category_mail'),
            'monitoring' => lang('protocol_filter_category_monitoring'),
            'networking' => lang('protocol_filter_category_networking'),
            'p2p' => lang('protocol_filter_category_p2p'),
            'printer' => lang('protocol_filter_category_printer'),
            'remote_access' => lang('protocol_filter_category_remote_access'),
            'streaming_media' => lang('protocol_filter_category_streaming_media'),
            'utiltity' => lang('protocol_filter_category_utility'),
            'voip' => lang('protocol_filter_category_voip'),
            'worm' => lang('protocol_filter_category_worm'),
        );

        $this->supported_groups = array(
            'chat' => lang('protocol_filter_category_chat'),
            'document_retrieval' => lang('protocol_filter_category_document_retrieval'),
            'file' => lang('protocol_filter_category_file'),
            'game' => lang('protocol_filter_category_game'),
            'all' => lang('protocol_filter_category_all'),
            'mail' => lang('protocol_filter_category_mail'),
            'monitoring' => lang('protocol_filter_category_monitoring'),
            'networking' => lang('protocol_filter_category_networking'),
            'p2p' => lang('protocol_filter_category_p2p'),
            'printer' => lang('protocol_filter_category_printer'),
            'remote_access' => lang('protocol_filter_category_remote_access'),
            'secure' => lang('protocol_filter_category_secure'),
            'streaming_audio' => lang('protocol_filter_category_streaming_audio'),
            'streaming_video' => lang('protocol_filter_category_streaming_video'),
            'time_synchronization' => lang('protocol_filter_category_time_synchronization'),
            'version_control' => lang('protocol_filter_category_version_control'),
            'voip' => lang('protocol_filter_category_voip'),
            'worm' => lang('protocol_filter_category_worm'),
        );
    }

    /**
     * Return associative array of l7-filter protocols.
     *
     * @return array protocol filter patterns
     * @throws Engine_Exception
     */

    public function get_protocols()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_protocol_data();

        return $this->protocols;
    }

    /**
     * Returns associative array of l7-filter groups.
     *
     * @return array protocol filter groups
     * @throws Engine_Exception
     */

    public function get_protocol_categories()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->categories;
    }

    /**
     * Returns blocked packet/bytes iptables status.
     *
     * @return array block status
     * @throws Engine_Exception
     */

    public function get_blocked_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_protocol_data();

        $enabled = 0;

        foreach ($this->protocols as $pattern) {
            if ($pattern['enabled'])
                $enabled++;
        }

        if ($enabled == 0)
            return;

        $shell = new Shell();
        $exitcode = $shell->execute(self::COMMAND_IPTABLES, '-t mangle -L l7-filter-drop -v -n', TRUE);

        if ($exitcode != 0) {
            // The command will fail in standalone mode.  Could certainly handle this better.
            return;
        }

        $contents = $shell->get_output();

        foreach ($contents as $mark) {
            // 0     0 DROP       all  --  *      *       0.0.0.0/0            0.0.0.0/0           MARK match 0x1c
            if (!preg_match('/^\s*(\dMG]+)\s+(\dMG]+)\s+DROP.*match\s+0x([[:xdigit:]]+)$/', chop($mark), $matches)) continue;
            foreach ($this->protocols as $key => $pattern) {
                if ($pattern['mark'] != hexdec($matches[3])) continue;
                $this->protocols[$key]['packets'] = $matches[1];
                $this->protocols[$key]['bytes'] = $matches[2];
                break;
            }
        }
    }

    /**
     * Enables an l7-filter protocol pattern.
     *
     * @param string $protocol protocol name
     *
     * @return void
     * @throws Engine_Exception
     */

    public function enable_protocol($protocol)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_protocol_data();

        foreach ($this->protocols as $key => $pattern) {
            if (strcasecmp($pattern['name'], $protocol))
                continue;

            $this->protocols[$key]['enabled'] = TRUE;
            $this->_save_configuration();
            return;
        }

        throw new Validation_Exception(lang('protocol_filter_protocol_invalid'));
    }

    /**
     * Disables an l7-filter protocol pattern.
     *
     * @param string $protocol protocol name
     *
     * @return void
     * @throws Engine_Exception
     */

    public function disable_protocol($protocol)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_load_protocol_data();

        foreach ($this->protocols as $key => $pattern) {
            if (strcasecmp($pattern['name'], $protocol))
                continue;

            $this->protocols[$key]['enabled'] = FALSE;
            $this->_save_configuration();
            return;
        }

        throw new Validation_Exception(lang('protocol_filter_protocol_invalid'));
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Saves l7-filter configuration
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _save_configuration()
    {
        clearos_profile(__METHOD__, __LINE__);

        $protocols = array();

        foreach ($this->protocols as $pattern) {
            if ($pattern['enabled'] !== TRUE)
                continue;
            $protocols[] = $pattern['name'];
        }

        $mark = 3;
        $contents = array();

        sort($protocols, SORT_STRING);

        foreach ($protocols as $name)
            $contents[] = sprintf('%-40s %-3d', $name, $mark++);

        $config_file = new File(self::FILE_CONFIG, TRUE);
        $config_file->dump_contents_from_array($contents);

        /*
        FIXME: move to controller
        if ($this->GetBootState()) $this->Restart();
        $fw = new Firewall();
        $fw->Restart();
        */
    }

    /**
     * Translates l7-filter groups to localized strings.
     *
     * @param array $groups raw group list
     *
     * @return array localized group information
     * @throws Engine_Exception
     */

    protected function _localize_groups($groups)
    {
        clearos_profile(__METHOD__, __LINE__);

        $translated_groups = array();

        foreach ($groups as $group) {
            if (array_key_exists($group, $this->supported_groups))
                $translated_groups[$group] = $this->supported_groups[$group];
        }

        return $translated_groups;
    }

    /**
     * Loads l7-filter configuration.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_configuration()
    {
        clearos_profile(__METHOD__, __LINE__);

        $config = array();
        $config_file = new File(self::FILE_CONFIG);
        $contents = $config_file->get_contents_as_array();

        foreach ($contents as $line) {
            $buffer = chop($line);

            if (!preg_match('/^[[:alnum:]]/', $buffer))
                continue;

            $config[] = explode(' ', preg_replace('/\s+/', ' ', $buffer));
        }

        foreach ($this->protocols as $key => $details) {
            $this->protocols[$key]['enabled'] = FALSE;

            foreach ($config as $entry) {
                if (strcmp($key, $entry[0]))
                    continue;

                $this->protocols[$key]['enabled'] = TRUE;
                $this->protocols[$key]['mark'] = $entry[1];
                break;
            }
        }
    }

    /**
     * Loads protocol data
     *
     * Return associative array of l7-filter protocol patterns.
     * Attempts to load this data from a cache if present and newer
     * than the last installed/updated l7-protocols RPM.  Generating
     * this pattern meta data is a slow process so the results
     * are cached to dramatically improve response time.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_protocol_data()
    {
        clearos_profile(__METHOD__, __LINE__);

        $rpm = new Software('l7-protocols');

        $cache = new File(self::FILE_CACHE);

        // Load from cache if available
        //-----------------------------

        // FIXME
        // if ($cache->exists() && ($cache->last_modified() > $rpm->get_install_time())) {
/*
        if ($cache->exists()) {
            $contents = $cache->get_contents();

            if (($data = unserialize($contents)) !== FALSE) {
                $this->protocols = $data['patterns'];
                $this->_load_configuration($this->protocols);
                return;
            }
        }
*/

        // FIXME
        $product = new Product();
        $redirect_url = $product->get_redirect_url();

        // Find pattern file subdirectories
        //---------------------------------

        $protocol_path = new Folder(self::PATH_PROTOCOLS);
        $protocol_path_listing = $protocol_path->get_listing();

        $subdirs = array();

        foreach ($protocol_path_listing as $listing) {
            $try_listing = new Folder(self::PATH_PROTOCOLS . '/' . $listing);

            if ($try_listing->is_directory())
                $subdirs[] = $listing;
        }

        // Load up pattern file information
        //---------------------------------

        foreach ($subdirs as $dir) {
            $protocol_path = new Folder(self::PATH_PROTOCOLS . "/$dir");
            $raw_files = $protocol_path->get_listing();

            foreach ($raw_files as $pattern_filename) {

                // Bail if not .pat
                if (! preg_match('/^.*\.pat$/', $pattern_filename))
                    continue;

                $pattern = array();

                $pattern_file = new File(self::PATH_PROTOCOLS . "/$dir/$pattern_filename", FALSE);
                $contents = $pattern_file->get_contents_as_array();

                $key = NULL;
                $lines = count($contents);

                for ($i = 4 ; $i < $lines; $i++) {
                    $buffer = chop($contents[$i]);

                    if (!preg_match('/^\w/', $buffer))
                        continue;

                    $key = $buffer;
                    break;
                }

                // Bail if we can't find the protocol name
                if (($key === NULL) || (! array_key_exists($key, $this->protocols)))
                    continue;

                // Populate protocols data
                //------------------------

                $this->protocols[$key]['dir'] = $dir;
                $this->protocols[$key]['file'] = $pattern_filename;
                $this->protocols[$key]['path'] = self::PATH_PROTOCOLS . "/$dir/$pattern_filename";
                $this->protocols[$key]['l7_description'] = preg_replace('/^#[[:space:]]*/', '', chop($contents[0]));
                $this->protocols[$key]['attributes'] = preg_replace('/^#\s*pattern attributes:\s*/i', '', chop($contents[1]));
                $this->protocols[$key]['url'] = $redirect_url . '/protocol_filter/protocol/' . $key;
                $this->protocols[$key]['wiki'] = preg_replace('/^#\s*wiki:\s*/i', '', chop($contents[3]));

                if (!preg_match('/^http/i', $this->protocols[$key]['wiki']))
                    $this->protocols[$key]['wiki'] = NULL;
            }
        }

        ksort($this->protocols);

        $cache = new File(self::FILE_CACHE);

        if ($cache->exists())
            $cache->delete();

        $cache->create('webconfig', 'webconfig', 0644);
        $cache->add_lines(serialize($this->protocols));
        $this->_load_configuration($this->protocols);
    }
}
