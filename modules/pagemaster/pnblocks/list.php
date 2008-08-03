<?php
/**
 * PageMaster
 *
 * @copyright (c) 2008, PageMaster Team
 * @link        http://code.zikula.org/projects/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rd_party_Modules
 * @subpackage  pagemaster
 */

/**
 * initialise block
 */
function pagemaster_listblock_init()
{
	// Security
	pnSecAddSchema('pagemaster:Listblock:', 'Block title:Block Id:Type Id');
}

/**
 * get information on block
 */
function pagemaster_listblock_info()
{
	// Values
	return array (
        'text_type'      => 'pagemasterList',
        'module'         => 'pagemaster',
        'text_type_long' => 'pagemaster list N publications',
        'allow_multiple' => true,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => true
	);
}

function pagemaster_listblock_display($blockinfo)
{
	// Get variables from content block
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	if (!array_key_exists('tid', $vars)) {
		return '';
	}

	$tid = $vars['tid'];
	if (!isset ($tid)) {
		$blockinfo['content'] = 'No type ID set for this block.';
		return pnBlockThemeBlock($blockinfo);
	}

	$listCount  = $vars['listCount'];
	$listOffset = $vars['listOffset'];
	$template   = (isset ($vars['template']) && $vars['template'] != '' ? $vars['template'] : 'block-list.htm');
	$filterStr  = $vars['filters'];
	$orderBy    = $vars['orderBy'];
	$cachelifetime  = $vars['cachelifetime'];

	// Security check
	if (!SecurityUtil::checkPermission('pagemaster:Listblock:', "$blockinfo[title]:$blockinfo[bid]:$tid", ACCESS_READ)) {
		return;
	}

	$html = pnModFunc('pagemaster', 'user', 'main',
	array('tid'                => $tid,
                            'filter'             => $filterStr,
                            'orderby'            => $orderBy,
                            'itemsperpage'           => $listCount,
                            'startnum'           => $listOffset,
                            'checkPerm'          => true,
                            'template'           => $template,
                            'handlePluginFields' => true,
							'cachelifetime' 	 => $cachelifetime	
	));

	$blockinfo['content'] = $html;

	return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 */
function pagemaster_listblock_modify($blockinfo)
{
	$output = new pnHTML();

	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// Defaults
	if (!isset ($vars['tid'])) {
		$vars['tid'] = pnModGetVar('pagemaster', 'frontpagePubType');
	}
	if (!isset ($vars['listCount'])) {
		$vars['listCount'] = 10;
	}
	if (!isset ($vars['listOffset'])) {
		$vars['listOffset'] = '';
	}
	if (!isset ($vars['template'])) {
		$vars['template'] = '';
	}
	if (!isset ($vars['cachelifetime'])) {
		$vars['cachelifetime'] = '';
	}

	$cachelifetime  = $vars['cachelifetime'];
	$listCount  = $vars['listCount'];
	$listOffset = $vars['listOffset'];
	$template   = $vars['template'];
	$filters    = array_key_exists('filters', $vars) ? $vars['filters'] : null;
	$orderBy    = array_key_exists('orderBy', $vars) ? $vars['orderBy'] : null;
	if (!pnModAPILoad('pagemaster', 'admin')) {
		return pagemasterErrorPage(__FILE__, __LINE__, 'Failed to load pagemaster admin API');
	}

	// (no table start/end since the block framework takes care of that)

	// Create row for "Publication type"
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

	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTPUBTYPE);
	$row[] = $output->FormSelectMultiple('tid', $pubTypes);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Add filter
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTFILTER);
	$row[] = $output->FormText('filters', $filters);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Add order by
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTORDERBY);
	$row[] = $output->FormText('orderBy', $orderBy);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);


	// Add cachelifetime
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTCACHELIFETIME);
	$row[] = $output->FormText('cachelifetime', $cachelifetime);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Add no. of publications
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTSHOWCOUNT);
	$row[] = $output->FormText('listCount', $listCount);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Add no. of publications offset
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTSHOWOFFSET);
	$row[] = $output->FormText('listOffset', $listOffset);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Add template
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PMBLOCKLISTTEMPLATE);
	$row[] = $output->FormText('template', $template);
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
function pagemaster_listblock_update($blockinfo)
{
	$filters = pnVarCleanFromInput('filters');

	$vars = array (
        'tid'        => FormUtil::getPassedValue('tid'),
        'filters'    => $filters,
        'listCount'  => FormUtil::getPassedValue('listCount'),
        'listOffset' => FormUtil::getPassedValue('listOffset'),
        'template'   => FormUtil::getPassedValue('template'),
    	'cachelifetime'   => FormUtil::getPassedValue('cachelifetime'),
        'orderBy'    => FormUtil::getPassedValue('orderBy')
	);

	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;
}
