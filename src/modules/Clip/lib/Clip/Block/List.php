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
 * List Block.
 */
class Clip_Block_List extends Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Clip:block:list', 'Block Id:Pubtype Id:');
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
        $alert = $this->getVar('devmode', false) && Clip_Access::toClip(ACCESS_ADMIN);

        // get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // validation of required parameters
        if (!isset($vars['tid']) || empty($vars['tid'])) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'tid') : null;
        }
        if (!Clip_Util::validateTid($vars['tid'])) {
            return $alert ? LogUtil::registerError($this->__f('Error! Invalid publication type ID passed [%s].', DataUtil::formatForDisplay($vars['tid']))) : null;
        }

        // security check
        // FIXME SECURITY centralize on Clip_Access
        if (!SecurityUtil::checkPermission('Clip:block:list', "$blockinfo[bid]:$vars[tid]:", ACCESS_OVERVIEW)) {
            return;
        }

        // default values
        $template = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : '';
        $orderdir = (isset($vars['orderDir']) && !empty($vars['orderDir'])) ? ':asc' : ':desc';
        $orderstr = (isset($vars['orderBy']) && !empty($vars['orderBy'])) ? $vars['orderBy'].$orderdir : '';

        $args = array(
            'tid'           => $vars['tid'],
            'orderby'       => $orderstr,
            'filter'        => (isset($vars['listfilter']) && !empty($vars['listfilter'])) ? $vars['listfilter'] : '()',
            'itemsperpage'  => (isset($vars['listCount']) && (int)$vars['listCount'] > 0) ? $vars['listCount'] : 5,
            'startnum'      => (isset($vars['listOffset'])) ? $vars['listOffset'] : 0,
            'template'      => $template ? 'block_'.$template : 'block',
            'cachelifetime' => (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null
        );

        $blockinfo['content'] = ModUtil::func('Clip', 'user', 'list', $args);

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
            $vars['tid'] = '';
        }
        if (!isset($vars['orderBy'])) {
            $vars['orderBy'] = '';
        }
        if (!isset($vars['orderDir'])) {
            $vars['orderDir'] = 0;
        }
        if (!isset($vars['listfilter'])) {
            $vars['listfilter'] = '';
        }
        if (!isset($vars['listCount'])) {
            $vars['listCount'] = 5;
        }
        if (!isset($vars['listOffset'])) {
            $vars['listOffset'] = 0;
        }
        if (!isset($vars['template'])) {
            $vars['template'] = '';
        }
        if (!isset($vars['cachelifetime'])) {
            $vars['cachelifetime'] = 0;
        }

        // builds the pubtypes selector
        $pubtypes = Clip_Util::getPubType(-1)->toKeyValueArray('tid', 'title');

        $fields = Clip_Util_Selectors::fields($vars['tid']);

        $pubfields = array();
        foreach (array_keys($fields) as $k) {
            $pubfields[$fields[$k]['value']] = $fields[$k]['text'];
        }

        // builds and return the output
        return $this->view->assign('vars', $vars)
                          ->assign('pubtypes', $pubtypes)
                          ->assign('pubfields', $pubfields)
                          ->fetch('clip_block_list_modify.tpl');
    }

    /**
     * Update block settings.
     */
    public function update($blockinfo)
    {
        $vars = array (
            'tid'           => FormUtil::getPassedValue('tid'),
            'orderBy'       => FormUtil::getPassedValue('orderBy'),
            'orderDir'      => (int)FormUtil::getPassedValue('orderDir'),
            'listfilter'    => FormUtil::getPassedValue('listfilter'),
            'listCount'     => FormUtil::getPassedValue('listCount'),
            'listOffset'    => FormUtil::getPassedValue('listOffset'),
            'template'      => FormUtil::getPassedValue('template'),
            'cachelifetime' => FormUtil::getPassedValue('cachelifetime')
        );

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return $blockinfo;
    }
}
