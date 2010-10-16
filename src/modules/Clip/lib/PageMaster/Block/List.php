<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * List Block.
 */
class Clip_Block_List extends Zikula_Block
{
    /**
     * Initialise block.
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('pagemaster:block:list', 'Block Id:Pubtype Id:');
    }

    /**
     * Get information on block.
     */
    public function info()
    {
        return array (
            'module'         => 'Clip',
            'text_type'      => $this->__('Clip List'),
            'text_type_long' => $this->__('Clip list of publications'),
            'allow_multiple' => true,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true
        );
    }

    /**
     * Display the block according its configuration.
     */
    public function display($blockinfo)
    {
        $alert = SecurityUtil::checkPermission('pagemaster::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

        // get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // validation of required parameters
        if (!isset($vars['tid']) || empty($vars['tid'])) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'tid') : null;
        }

        // security check
        if (!SecurityUtil::checkPermission('pagemaster:block:list', "$blockinfo[bid]:$vars[tid]:", ACCESS_READ)) {
            return;
        }

        $pubtype = Clip_Util::getPubType((int)$vars['tid']);
        if (!$pubtype) {
            return;
        }

        // default values
        $template      = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : $pubtype['outputset'];
        $listCount     = (isset($vars['listCount']) && (int)$vars['listCount'] > 0) ? $vars['listCount'] : 5;
        $listOffset    = (isset($vars['listOffset'])) ? $vars['listOffset'] : 0;
        $filterStr     = (isset($vars['filters'])) ? $vars['filters'] : '';
        $orderBy       = (isset($vars['orderBy'])) ? $vars['orderBy'] : '';
        $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

        $blockinfo['content'] = ModUtil::func('Clip', 'user', 'view',
                                              array('tid'                => $vars['tid'],
                                                    'template'           => 'block_list_'.$template,
                                                    'filter'             => $filterStr,
                                                    'orderby'            => $orderBy,
                                                    'itemsperpage'       => $listCount,
                                                    'startnum'           => $listOffset,
                                                    'checkPerm'          => true,
                                                    'handlePluginFields' => true,
                                                    'cachelifetime'      => $cachelifetime));

        if (empty($blockinfo['content'])) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * Modify block settings.
     */
    public function modify($blockinfo)
    {
        // get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // defaults
        if (!isset($vars['tid'])) {
            $vars['tid'] = 0;
        }
        if (!isset($vars['listCount'])) {
            $vars['listCount'] = 5;
        }
        if (!isset($vars['listOffset'])) {
            $vars['listOffset'] = 0;
        }
        if (!isset($vars['cachelifetime'])) {
            $vars['cachelifetime'] = 0;
        }
        if (!isset($vars['filters'])) {
            $vars['filters'] = '';
        }
        if (!isset($vars['orderBy'])) {
            $vars['orderBy'] = '';
        }
        if (!isset($vars['template'])) {
            $vars['template'] = '';
        }

        // builds the pubtypes selector
        $pubtypes = Clip_Util::getPubType(-1)->toArray();

        foreach (array_keys($pubtypes) as $tid) {
            $pubtypes[$tid] = $pubtypes[$tid]['title'];
        }

        $pubfields = array();
        if (!empty($vars['tid'])) {
            $fields = Clip_Util::getPubFields($vars['tid']);

            $arraysort = array(
                'core_empty' => array(),
                'core_title' => array(),
                'core_cr_date' => array(),
                'core_pu_date' => array(),
                'core_hitcount' => array()
            );

            $pubarr = array(
                'core_empty' => array(
                    'text'  => '',
                    'value' => ''
                ),
                'core_cr_date' => array(
                    'text'  => $this->__('Creation date'),
                    'value' => 'cr_date'
                ),
                'core_lu_date' => array(
                    'text'  => $this->__('Update date'),
                    'value' => 'lu_date'
                ),
                'core_cr_uid' => array(
                    'text'  => $this->__('Creator'),
                    'value' => 'core_author'
                ),
                'core_lu_uid' => array(
                    'text'  => $this->__('Updater'),
                    'value' => 'lu_uid'
                ),
                'core_pu_date' => array(
                    'text'  => $this->__('Publish date'),
                    'value' => 'pm_publishdate'
                ),
                'core_ex_date' => array(
                    'text'  => $this->__('Expire date'),
                    'value' => 'pm_expiredate'
                ),
                'core_language' => array(
                    'text'  => $this->__('Language'),
                    'value' => 'pm_language'
                ),
                'core_hitcount' => array(
                    'text'  => $this->__('Number of reads'),
                    'value' => 'pm_hitcount'
                )
            );

            foreach ($pubfields as $fieldname => $pubfield) {
                $index = ($pubfield['istitle'] == 1) ? 'core_title' : $fieldname;
                $pubarr[$index] = array(
                    'text'  => $this->__($pubfield['title']),
                    'value' => $fieldname
                );
            }

            $pubarr = array_values(array_filter(array_merge($arraysort, $pubarr)));

            foreach (array_keys($pubarr) as $k) {
                $pubfields[$pubarr[$k]['value']] = $pubarr[$k]['text'];
            }
        }

        // builds and return the output
        return $this->view->assign('vars', $vars)
                          ->assign('pubtypes', $pubtypes)
                          ->assign('pubfields', $pubfields)
                          ->fetch('pagemaster_block_list_modify.tpl');
    }

    /**
     * Update block settings.
     */
    public function update($blockinfo)
    {
        $vars = array (
            'tid'           => FormUtil::getPassedValue('tid'),
            'orderBy'       => FormUtil::getPassedValue('orderBy'),
            'filters'       => FormUtil::getPassedValue('filters'),
            'listCount'     => FormUtil::getPassedValue('listCount'),
            'listOffset'    => FormUtil::getPassedValue('listOffset'),
            'template'      => FormUtil::getPassedValue('template'),
            'cachelifetime' => FormUtil::getPassedValue('cachelifetime')
        );

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return $blockinfo;
    }
}
