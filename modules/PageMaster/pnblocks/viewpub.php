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
function PageMaster_viewpubblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('pagemaster:Listblock:', 'Block title:Block Id:Pubtype Id');
}

/**
 * get information on block
 */
function PageMaster_viewpubblock_info()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    return array (
        'module'         => 'PageMaster',
        'text_type'      => __('PageMaster viewpub', $dom),
        'text_type_long' => __('PageMaster View Publication', $dom),
        'allow_multiple' => true,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => true
    );
}

/**
 * display the block according its configuration
 */
function PageMaster_viewpubblock_display($blockinfo)
{
    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Validation of required parameters
    if (!isset($vars['tid'])) {
        $vars['tid'] = pnModGetVar('PageMaster', 'frontpagePubType');
    }
    if (!isset($vars['pid'])) {
        return 'Required parameter [pid] not set';
    }

    // Security check
    if (!SecurityUtil::checkPermission('pagemaster:viewpubblock:', "$blockinfo[title]:$blockinfo[bid]:$vars[tid]", ACCESS_READ)) {
        return;
    }

    // Default values
    $template      = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : 'block_viewpub';
    $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

    $blockinfo['content'] = pnModFunc('PageMaster', 'user', 'viewpub',
                                      array('tid'                => $vars['tid'],
                                            'pid'                => $vars['pid'],
                                            'checkPerm'          => true,
                                            'template'           => $template,
                                            'cachelifetime'      => $cachelifetime));

    if (empty($blockinfo['content'])) {
        return;
    }

    return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 */
function PageMaster_viewpubblock_modify($blockinfo)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (!isset($vars['tid'])) {
        $vars['tid'] = pnModGetVar('PageMaster', 'frontpagePubType');
    }
    if (!isset($vars['pid'])) {
        $vars['pid'] = '';
    }
    if (!isset($vars['cachelifetime'])) {
        $vars['cachelifetime'] = 0;
    }
    if (!isset($vars['template'])) {
        $vars['template'] = 'block_viewpub';
    }

    $output = new pnHTML();

    // (no table start/end since the block edit template takes care of that)

    // Create a row for "Publication type"
    pnModDBInfoLoad('PageMaster');
    $pubTypesData = DBUtil::selectObjectArray('pagemaster_pubtypes');

    $pubTypes = array ();
    foreach ($pubTypesData as $pubType) {
        $pubTypes[] = array(
            'name' => $pubType['title'],
            'id'   => $pubType['tid']
        );

        if ($pubType['tid'] == $vars['tid']) {
            $pubTypes[count($pubTypes)-1]['selected'] = 1;
        }
    }
    unset($pubTypesData);

    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Publication type', $dom));
    $row[] = $output->FormSelectMultiple('tid', $pubTypes);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Add filter
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('PID', $dom));
    $row[] = $output->FormText('pid', $vars['pid']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Add cachelifetime
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Cache lifetime', $dom));
    $row[] = $output->FormText('cachelifetime', $vars['cachelifetime']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Add template
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Template', $dom));
    $row[] = $output->FormText('template', $vars['template']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Return output
    return $output->GetOutput();
}

/**
 * update block settings
 */
function PageMaster_viewpubblock_update($blockinfo)
{
    $filters = pnVarCleanFromInput('filters');

    $vars = array (
        'tid'           => FormUtil::getPassedValue('tid'),
        'pid'           => FormUtil::getPassedValue('pid'),
        'template'      => FormUtil::getPassedValue('template'),
        'cachelifetime' => FormUtil::getPassedValue('cachelifetime')
    );

    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
