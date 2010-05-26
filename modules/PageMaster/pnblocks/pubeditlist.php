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

/**
 * initialise block
 */
function PageMaster_pubeditlistblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('pagemaster:PubEditListblock:', 'Block title:Block Id:Pubtype Id');
}

/**
 * get information on block
 */
function PageMaster_pubeditlistblock_info()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    return array (
        'module'         => 'PageMaster',
        'text_type'      => __('PageMaster edit list', $dom),
        'text_type_long' => __('PageMaster dynamic edit tree/list', $dom),
        'allow_multiple' => true,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => true
    );
}

/**
 * display the block according its configuration
 */
function PageMaster_pubeditlistblock_display($blockinfo)
{
    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Security check
    if (!SecurityUtil::checkPermission('pagemaster:PubEditListblock:', "$blockinfo[title]:$blockinfo[bid]:$vars[tid]", ACCESS_READ)) {
        return;
    }

    $orderBy       = (isset($vars['orderBy'])) ? $vars['orderBy'] : '';
    $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

    $dom        = ZLanguage::getModuleDomain('PageMaster');
    $tid        = isset($args['tid']) ? $args['tid'] : FormUtil::getPassedValue('tid');
    $pid        = isset($args['pid']) ? $args['pid'] : FormUtil::getPassedValue('pid');
    $orderby    = isset($args['orderby']) ? $args['orderby'] : FormUtil::getPassedValue('orderby', 'pm_pid');
    $returntype = isset($args['returntype']) ? $args['returntype'] : FormUtil::getPassedValue('returntype', 'user');
    $source     = 'block';

    $pubData = pnModAPIFunc ('PageMaster', 'user', 'pubeditlist', $args);

    $render = pnRender::getInstance('PageMaster');
    $render->assign('allTypes',   $pubData['allTypes']);
    $render->assign('publist',    $pubData['pubList']);
    $render->assign('tid',        $tid);
    $render->assign('pid',        $pid);
    $render->assign('returntype', $returntype);
    $render->assign('source',     $source);
    $blockinfo['content'] = $render->fetch('pagemaster_block_pubeditlist.htm');

    if (empty($blockinfo['content'])) {
        return;
    }

    return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 */
function PageMaster_pubeditlistblock_modify($blockinfo)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    $vars = pnBlockVarsFromContent($blockinfo['content']);

    if (!isset($vars['orderBy'])) {
        $vars['orderBy'] = '';
    }

    return '';
}

/**
 * update block settings
 */
function PageMaster_pubeditlistblock_update($blockinfo)
{
    $filters = pnVarCleanFromInput('filters');

    $vars = array (
        'cachelifetime' => FormUtil::getPassedValue('cachelifetime'),
        'orderBy'       => FormUtil::getPassedValue('orderBy')
    );

    $blockinfo['content'] = pnBlockVarsToContent($vars);

    $pnRender = pnRender::getInstance('PageMaster');
    $pnRender->clear_cache('pagemaster_generic_pubeditlist.htm');

    return $blockinfo;
}
