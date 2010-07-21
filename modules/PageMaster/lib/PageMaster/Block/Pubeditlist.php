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

class PageMaster_Block_Pubeditlist extends Zikula_Block
{
    /**
     * Initialise block
     */
    public function init()
    {
        // Security schema
        SecurityUtil::registerPermissionSchema('pagemaster:block:pubeditlist', 'Block Id:Pubtype Id:');
    }

    /**
     * Get information on block
     */
    public function info()
    {
        return array (
            'module'         => 'PageMaster',
            'text_type'      => $this->__('PageMaster edit list'),
            'text_type_long' => $this->__('PageMaster dynamic edit tree/list'),
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

        // Security check
        if (!SecurityUtil::checkPermission('pagemaster:block:pubeditlist', "$blockinfo[title]:$blockinfo[bid]:$vars[tid]", ACCESS_READ)) {
            return;
        }

        $orderBy       = (isset($vars['orderBy'])) ? $vars['orderBy'] : '';
        $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

        $tid        = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
        $pid        = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
        $orderby    = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby');
        $returntype = isset($args['returntype']) ? $args['returntype'] : FormUtil::getPassedValue('returntype', 'user');
        $source     = 'block';

        $pubData = ModUtil::apiFunc('PageMaster', 'user', 'pubeditlist', $args);

        $this->view->assign('allTypes',   $pubData['allTypes'])
                   ->assign('publist',    $pubData['pubList'])
                   ->assign('tid',        $tid)
                   ->assign('pid',        $pid)
                   ->assign('returntype', $returntype)
                   ->assign('source',     $source);

        $blockinfo['content'] = $this->view->fetch('pagemaster_block_pubeditlist.tpl');

        if (empty($blockinfo['content'])) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * Modify block settings
     */
    public function modify($blockinfo)
    {
        return '';
    }

    /**
     * Update block settings
     */
    public function update($blockinfo)
    {
        $filters = FormUtil::getPassedValue('filters');

        $vars = array (
            'cachelifetime' => FormUtil::getPassedValue('cachelifetime'),
            'orderBy'       => FormUtil::getPassedValue('orderBy')
        );

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        $this->view->clear_cache('pagemaster_generic_pubeditlist.tpl');

        return $blockinfo;
    }
}
