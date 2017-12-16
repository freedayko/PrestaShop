<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_TOOL_DIR_.'smarty/Smarty.class.php');

class Smarty_CacheResource_Apc extends Smarty_CacheResource_Custom
{

    function __construct()
    {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            throw new Exception('APC Template Caching Error: APC is not installed');
        }
    }

    /**
    * Returns the filepath of the cached template output
    *
    * @param object $_template current template
    * @return string the cache filepath
    */
    public function getCachedFilepath($_template)
    {
        return md5($_template->getTemplateFilepath().$_template->cache_id.$template->compile_id);
    }

    /**
    * Returns the timpestamp of the cached template output
    *
    * @param object $_template current template
    * @return integer |booelan the template timestamp or false if the file does not exist
    */
    public function getCachedTimestamp($_template)
    {
        apc_fetch($this->getCachedFilepath($_template), $success);
        return $success ? time() : false;
    } 

    /**
     * fetch cached content and its modification time from data source
     *
     * @param string $id unique cache content identifier
     * @param string $name template name
     * @param string $cache_id cache id
     * @param string $compile_id compile id
     * @param string $content cached content
     * @param int $mtime cache modification timestamp (epoch)
     * @return void
     */
    protected function fetch($id, $name, $cache_id, $compile_id, &$content, &$mtime)
    {
        $row = apc_fetch($id);
        if ($row) {
            $content = $row['content'];
            $mtime = $row['modified'];
        } else {
            $content = null;
            $mtime = null;
        }
    }

    /**
     * Fetch cached content's modification timestamp from data source
     *
     * @note implementing this method is optional. Only implement it if modification times can be accessed faster than loading the complete cached content.
     * @param string $id unique cache content identifier
     * @param string $name template name
     * @param string $cache_id cache id
     * @param string $compile_id compile id
     * @return int|boolean timestamp (epoch) the template was modified, or false if not found
     *
    
    protected function fetchTimestamp($id, $name, $cache_id, $compile_id)
    {
        $value = Db::getInstance()->getValue('SELECT modified FROM '._DB_PREFIX_.'smarty_cache WHERE id_smarty_cache = "'.pSQL($id, true).'"');
        $mtime = strtotime($value);
        return $mtime;
    }*/

    /**
     * Save content to cache
     *
     * @param string $id unique cache content identifier
     * @param string $name template name
     * @param string $cache_id cache id
     * @param string $compile_id compile id
     * @param int|null $exp_time seconds till expiration time in seconds or null
     * @param string $content content to cache
     * @return bool success
     */
    protected function save($id, $name, $cache_id, $compile_id, $exp_time, $content)
    {
        return apc_store($id, array('content' => $content, 'modified' => time()), $exp_time);
    }

    /**
     * Delete content from cache
     *
     * @param string $name template name
     * @param string $cache_id cache id
     * @param string $compile_id compile id
     * @param int|null $exp_time seconds till expiration or null
     * @return int number of deleted caches
     */
    protected function delete($name, $cache_id, $compile_id, $exp_time)
    {
        // delete the whole cache
        if ($name === null && $cache_id === null && $compile_id === null && $exp_time === null) {
            apc_clear_cache('user');
            return -1;
        }

        return apc_delete(md5($name.$cache_id.$compile_id));
    }
}
