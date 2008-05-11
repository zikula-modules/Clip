<?php

/**
 * initialise block
 */
function pagemaster_pubblock_init() {
	// Security
	pnSecAddSchema('pagemaster:Pubblock:', 'Block title:Publication Type ID:Publication ID');
}

/**
 * get information on block
 */
function pagemaster_pubblock_info() {
	// Values
	return array (
		'text_type' => 'pagemasterPublication',
		'module' => 'pagemaster',
		'text_type_long' => 'Display a pagemaster publication',
		'allow_multiple' => true,
		'form_content' => false,
		'form_refresh' => false,
		'show_preview' => true
	);
}

/**
 * display block
 */
function pagemaster_pubblock_display($blockinfo) {
	// Get variables from content block
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	if (!array_key_exists('tid', $vars)) {
		$blockinfo['content'] = _PGPUBBLOCKEDITBLOCK;
		return themesideblock($blockinfo);
	}

	// Defaults
	$tid = $vars['tid'];
	$pid = $vars['pid'];
	$tpl = $vars['tpl'];

	// Security check
	if (!SecurityUtil :: checkPermission('pagemaster:Pubblock:', "$blockinfo[title]:$tid:$pid", ACCESS_READ))
		return;

	// get the formatted publication
	$pubFormatted = pnModFunc('pagemaster', 'user', 'viewpub', array (
		'tid' => $tid,
		'pid' => $pid,
		'template' => $tpl
	));

	// Populate block info and pass to theme
	$blockinfo['content'] = $pubFormatted;
	return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 * This is the function that is called to display the Admin / Blocks / Pagesetter Publication block
 */
function pagemaster_pubblock_modify($blockinfo) {
	// Create output object
	$output = new pnHTML();

	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// Defaults
	if (!isset ($vars['tid']))
		$vars['tid'] = pnModGetVar('pagemaster', 'frontpagePubType');
	if (!isset ($vars['pid']))
		$vars['pid'] = 1;
	if (!isset ($vars['tpl']))
		$vars['tpl'] = "";

	if (!pnModAPILoad('pagemaster', 'admin'))
		return pagemasterErrorPage(__FILE__, __LINE__, 'Failed to load pagemaster admin API');

	$pubTypesData = DBUtil :: selectObjectArray("pagemaster_pubtypes");

	$pubTypes = array ();
	foreach ($pubTypesData as $pubType) {
		$pubTypes[] = array (
			'name' => $pubType['title'],
			'id' => $pubType['tid']
		);

		if ($pubType['tid'] == $vars['tid'])
			$pubTypes[count($pubTypes) - 1]['selected'] = 1;
	}

	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PGPUBBLOCKPUBTYPE);
	$row[] = $output->FormSelectMultiple('tid', $pubTypes);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Create row for Publication
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PGPUBBLOCKPUB);
	$row[] = $output->FormText('pid', $vars['pid']);
	$output->SetOutputMode(_PNH_KEEPOUTPUT);

	// Add row
	$output->SetInputMode(_PNH_VERBATIMINPUT);
	$output->TableAddRow($row, 'left');
	$output->SetInputMode(_PNH_PARSEINPUT);

	// Create row for Template to use
	$row = array ();
	$output->SetOutputMode(_PNH_RETURNOUTPUT);
	$row[] = $output->Text(_PGPUBBLOCKTEMPLATE);
	$row[] = $output->FormText('tpl', $vars['tpl']);
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
function pagemaster_pubblock_update($blockinfo) {
	$vars = array (
		'tid' => pnVarCleanFromInput('tid'),
		'pid' => pnVarCleanFromInput('pid'),
		'tpl' => pnVarCleanFromInput('tpl')
	);

	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;
}
?>