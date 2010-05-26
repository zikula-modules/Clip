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
function PageMaster_listblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('pagemaster:Listblock:', 'Block title:Block Id:Pubtype Id');
}

/**
 * get information on block
 */
function PageMaster_listblock_info()
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    return array (
        'module'         => 'PageMaster',
        'text_type'      => __('PageMaster List', $dom),
        'text_type_long' => __('PageMaster N publications list', $dom),
        'allow_multiple' => true,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => true
    );
}

/**
 * display the block according its configuration
 */
function PageMaster_listblock_display($blockinfo)
{
    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Validation of required parameters
    if (!isset($vars['tid'])) {
        $vars['tid'] = pnModGetVar('PageMaster', 'frontpagePubType');
    }

    // Security check
    if (!SecurityUtil::checkPermission('pagemaster:Listblock:', "$blockinfo[title]:$blockinfo[bid]:$vars[tid]", ACCESS_READ)) {
        return;
    }

    // Default values
    $template      = (isset($vars['template']) && !empty($vars['template'])) ? $vars['template'] : 'block_list';
    $listCount     = (isset($vars['listCount']) && (int)$vars['listCount'] > 1) ? $vars['listCount'] : 5;
    $listOffset    = (isset($vars['listOffset'])) ? $vars['listOffset'] : 0;
    $filterStr     = (isset($vars['filters'])) ? $vars['filters'] : '';
    $orderBy       = (isset($vars['orderBy'])) ? $vars['orderBy'] : '';
    $cachelifetime = (isset($vars['cachelifetime'])) ? $vars['cachelifetime'] : null;

    $blockinfo['content'] = pnModFunc('PageMaster', 'user', 'main',
                                      array('tid'                => $vars['tid'],
                                            'filter'             => $filterStr,
                                            'orderby'            => $orderBy,
                                            'itemsperpage'       => $listCount,
                                            'startnum'           => $listOffset,
                                            'checkPerm'          => true,
                                            'template'           => $template,
                                            'handlePluginFields' => true,
                                            'cachelifetime'      => $cachelifetime));

    if (empty($blockinfo['content'])) {
        return;
    }

    return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 */
function PageMaster_listblock_modify($blockinfo)
{
    $dom = ZLanguage::getModuleDomain('PageMaster');

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (!isset($vars['tid'])) {
        $vars['tid'] = pnModGetVar('PageMaster', 'frontpagePubType');
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
        $vars['template'] = 'block_list';
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
    $row[] = $output->Text(__('Filter string', $dom));
    $row[] = $output->FormText('filters', $vars['filters']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Add order by
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Order by', $dom));
    $row[] = $output->FormText('orderBy', $vars['orderBy']);
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

    // Add no. of publications
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Number of items', $dom));
    $row[] = $output->FormText('listCount', $vars['listCount']);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    // Add row
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $output->TableAddRow($row, 'left');
    $output->SetInputMode(_PNH_PARSEINPUT);

    // Add no. of publications offset
    $row = array ();
    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $row[] = $output->Text(__('Starting from', $dom));
    $row[] = $output->FormText('listOffset', $vars['listOffset']);
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
function PageMaster_listblock_update($blockinfo)
{
    $filters = pnVarCleanFromInput('filters');

    $vars = array (
        'tid'           => FormUtil::getPassedValue('tid'),
        'filters'       => $filters,
        'listCount'     => FormUtil::getPassedValue('listCount'),
        'listOffset'    => FormUtil::getPassedValue('listOffset'),
        'template'      => FormUtil::getPassedValue('template'),
        'cachelifetime' => FormUtil::getPassedValue('cachelifetime'),
        'orderBy'       => FormUtil::getPassedValue('orderBy')
    );

    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
