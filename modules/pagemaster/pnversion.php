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
$dom = ZLanguage::getModuleDomain('pagemaster');
// Module name:
$modversion['name']           = 'pagemaster';

// Version (10 chars or less):
$modversion['version']        = '0.3.3';
$modversion['description']    = __('Content Module like pagesetter');
$modversion['displayname']    = 'pagemaster';
$modversion['url']            = __('pagemaster', $dom);

// Used by the Credits module:
$modversion['credits']        = 'pndocs/credits.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'spax';
$modversion['contact']        = ' ';

// Module security:
$modversion['securityschema'] = array(
                                      'pagemaster::'      => '::',
                                      'pagemaster:input:' => 'tid::',
                                      'pagemaster:input:' => 'tid:pid:workflowstate',
                                      'pagemaster:full:'  => 'tid:pid:template',
                                      'pagemaster:list:'  => 'tid::template'
                                     );

// Module depedencies
$modversion['dependencies'] = array(
                                    array('modname'    => 'Workflow',
                                          'minversion' => '1.0',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_REQUIRED),
                                    array('modname'    => 'scribite',
                                          'minversion' => '2.0',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                    array('modname'    => 'Thumbnail',
                                          'minversion' => '1.1',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
                                   );
