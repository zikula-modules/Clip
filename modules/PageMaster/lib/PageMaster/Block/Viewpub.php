<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

class PageMaster_Block_Viewpub extends Zikula_Block
{
    /**
     * Initialise block
     */
    public function init()
    {
        // Security schema
        SecurityUtil::registerPermissionSchema('pagemaster:block:viewpub', 'Block Id:Pubtype Id:');
    }

    /**
     * Get information on block
     */
    public function info()
    {
        return array(
            'module'         => 'PageMaster',
            'text_type'      => $this->__('PageMaster viewpub'),
            'text_type_long' => $this->__('PageMaster View Publication'),
            'allow_multiple' => true,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true
        );
    }

    /**
     * Display the block according its configuration
     */
    public function display($blockinfo)
    {
        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Validation of required parameters
        if (!isset($vars['tid']) || empty($vars['tid'])) {
            return $this->__('Required parameter [%s] not set or empty.', 'tid');
        }
        if (!isset($vars['pid']) || empty($vars['pid'])) {
            return $this->__('Required parameter [%s] not set or empty.', 'pid');
        }

        // Security check
        if (!SecurityUtil::checkPermission('pagemaster:block:viewpub', "$blockinfo[bid]:$vars[tid]:", ACCESS_READ)) {
            return;
        }

        // Default values
        $template      = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : 'block_viewpub';
        $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

        $blockinfo['content'] = ModUtil::func('PageMaster', 'user', 'viewpub',
                                              array('tid'                => $vars['tid'],
                                                    'pid'                => $vars['pid'],
                                                    'checkPerm'          => true,
                                                    'template'           => $template,
                                                    'cachelifetime'      => $cachelifetime));

        if (empty($blockinfo['content'])) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (!isset($vars['tid'])) {
            $vars['tid'] = 0;
        }
        if (!isset($vars['pid'])) {
            $vars['pid'] = 0;
        }
        if (!isset($vars['cachelifetime'])) {
            $vars['cachelifetime'] = 0;
        }
        if (!isset($vars['template'])) {
            $vars['template'] = 'block_viewpub';
        }

        // Builds the pubtypes selector
        $pubtypes = PageMaster_Util::getPubType(-1);

        foreach (array_keys($pubtypes) as $tid) {
            $pubtypes[$tid] = $pubtypes[$tid]['title'];
        }

        // Builds the output
        $this->view->assign('vars', $vars)
                   ->assign('pubtypes', $pubtypes);

        // Return output
        return $this->view->fetch('pagemaster_block_viewpub_modify.tpl');
    }

    /**
     * Update block settings
     */
    public function update($blockinfo)
    {
        $vars = array (
            'tid'           => FormUtil::getPassedValue('tid'),
            'pid'           => FormUtil::getPassedValue('pid'),
            'template'      => FormUtil::getPassedValue('template'),
            'cachelifetime' => FormUtil::getPassedValue('cachelifetime')
        );

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return $blockinfo;
    }
}
