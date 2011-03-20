<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Block
 */

/**
 * Pubeditlist Block.
 */
class Clip_Block_Pubeditlist extends Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Clip:block:pubeditlist', 'Block Id:Pubtype Id:');
    }

    /**
     * Get information on block.
     */
    public function info()
    {
        return array (
            'module'         => 'Clip',
            'text_type'      => $this->__('Clip edit list'),
            'text_type_long' => $this->__('Clip dynamic edit tree/list'),
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
        // get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // security check
        if (!SecurityUtil::checkPermission('Clip:block:pubeditlist', "$blockinfo[title]:$blockinfo[bid]:$vars[tid]", ACCESS_READ)) {
            return;
        }

        $args['orderby'] = (isset($vars['orderBy'])) ? $vars['orderBy'] : FormUtil::getPassedValue('orderby');
        //$cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

        $tid        = FormUtil::getPassedValue('tid');
        $pid        = FormUtil::getPassedValue('pid');
        $returntype = FormUtil::getPassedValue('returntype', 'user');
        $source     = 'block';

        $pubData = ModUtil::apiFunc('Clip', 'user', 'editlist', $args);

        $this->view->assign('allTypes',   $pubData['allTypes'])
                   ->assign('publist',    $pubData['pubList'])
                   ->assign('tid',        $tid)
                   ->assign('pid',        $pid)
                   ->assign('returntype', $returntype)
                   ->assign('source',     $source);

        $blockinfo['content'] = $this->view->fetch('clip_block_pubeditlist.tpl');

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
        return '';
    }

    /**
     * Update block settings.
     */
    public function update($blockinfo)
    {
        $vars = array (
            'cachelifetime' => FormUtil::getPassedValue('cachelifetime'),
            'orderBy'       => FormUtil::getPassedValue('orderBy')
        );

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        $this->view->clear_cache('clip_generic_pubeditlist.tpl');

        return $blockinfo;
    }
}
