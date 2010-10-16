<?php
/**
 * Clip
 *
 * @copyright   (c) Clip Team
 * @link        http://code.zikula.org/clip/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  clip
 */

/**
 * Viewpub Block.
 */
class Clip_Block_Viewpub extends Zikula_Block
{
    /**
     * Initialise block.
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('clip:block:viewpub', 'Block Id:Pubtype Id:');
    }

    /**
     * Get information on block.
     */
    public function info()
    {
        return array(
            'module'         => 'Clip',
            'text_type'      => $this->__('Clip viewpub'),
            'text_type_long' => $this->__('Clip View Publication'),
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
        $alert = SecurityUtil::checkPermission('clip::', '::', ACCESS_ADMIN) && ModUtil::getVar('Clip', 'devmode', false);

        // get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // validation of required parameters
        if (!isset($vars['tid']) || empty($vars['tid'])) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'tid') : null;
        }
        if (!isset($vars['pid']) || empty($vars['pid'])) {
            return $alert ? $this->__f('Required parameter [%s] not set or empty.', 'pid') : null;
        }

        // security check
        if (!SecurityUtil::checkPermission('clip:block:viewpub', "$blockinfo[bid]:$vars[tid]:", ACCESS_READ)) {
            return;
        }

        $pubtype = Clip_Util::getPubType((int)$vars['tid']);
        if (!$pubtype) {
            return;
        }

        // default values
        $template      = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : $pubtype['filename'];
        $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

        $blockinfo['content'] = ModUtil::func('Clip', 'user', 'display',
                                              array('tid'                => $vars['tid'],
                                                    'pid'                => $vars['pid'],
                                                    'template'           => 'block_pub_'.$template,
                                                    'checkPerm'          => true,
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
        if (!isset($vars['pid'])) {
            $vars['pid'] = 0;
        }
        if (!isset($vars['cachelifetime'])) {
            $vars['cachelifetime'] = 0;
        }
        if (!isset($vars['template'])) {
            $vars['template'] = '';
        }

        // builds the pubtypes selector
        $pubtypes = Clip_Util::getPubType(-1);

        foreach (array_keys($pubtypes) as $tid) {
            $pubtypes[$tid] = $pubtypes[$tid]['title'];
        }

        // builds the output
        $this->view->assign('vars', $vars)
                   ->assign('pubtypes', $pubtypes);

        // return output
        return $this->view->fetch('clip_block_viewpub_modify.tpl');
    }

    /**
     * Update block settings.
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
