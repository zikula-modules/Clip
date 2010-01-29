<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * pnForm handler for updating module vars
 *
 * @author kundi
 */
class pagemaster_admin_modifyconfig
{
    /**
     * Initialize function
     */
    function initialize(&$render)
    {
        $modvars = pnModGetVar('pagemaster');

        $render->assign($modvars);

        // check if there are pubtypes already
        $numpubtypes = DBUtil::selectObjectCount('pagemaster_pubtypes');

        $render->assign('alreadyexists', $numpubtypes > 0 ? true : false);

        // upload dir check
        $siteroot = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';

        $render->assign('siteroot', DataUtil::formatForDisplay($siteroot));

        // fills the directory state
        if (file_exists($modvars['uploadpath'].'/')) {
            $render->assign('updirstatus', 1); // exists
            if (is_dir($modvars['uploadpath'].'/')) {
                $render->assign('updirstatus', 2); // is a directory
                if (is_writable($modvars['uploadpath'].'/')) {
                    $render->assign('updirstatus', 3); // is writable
                }
            }
        } else {
            $render->assign('updirstatus', 0); // doesn't exists
        }

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');

        $data = $render->pnFormGetValues();

        // handle the commands
        switch ($args['commandName'])
        {
            // update the modvars
            case 'update':
                // upload path
                // remove the siteroot if was included
                $siteroot = substr(pnServerGetVar('DOCUMENT_ROOT'), 0, -1).pnGetBaseURI().'/';
                $data['uploadpath'] = str_replace($siteroot, '', $data['uploadpath']);
                if (StringUtil::right($data['uploadpath'], 1) == '/') {
                    $data['uploadpath'] = StringUtil::left($data['uploadpath'], strlen($data['uploadpath']) - 1);
                }
                pnModSetVar('pagemaster', 'uploadpath', $data['uploadpath']);

                // development mode
                pnModSetVar('pagemaster', 'devmode', $data['devmode']);

                LogUtil::registerStatus(__('Done! Module configuration updated.', $dom));
    
                return pnRedirect(pnModURL('pagemaster', 'admin', 'modifyconfig'));

            // cancel
            case 'cancel':
                return pnRedirect(pnModURL('pagemaster', 'admin'));
        }

        return true;
    }
}
