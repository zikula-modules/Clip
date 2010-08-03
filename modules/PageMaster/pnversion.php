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

$dom = ZLanguage::getModuleDomain('PageMaster');

$modversion['name']           = 'PageMaster';
$modversion['oldnames']       = array('pagemaster');
$modversion['displayname']    = __('PageMaster', $dom);
$modversion['description']    = __('Content Module like pagesetter', $dom);
//! module name that appears in URL
$modversion['url']            = __('pagemaster', $dom);
$modversion['version']        = '0.4.2'; // 10 chars or less

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = false;
$modversion['author']         = 'spax';
$modversion['contact']        = ' ';

// Permissions schema
$modversion['securityschema'] = array(
                                      'pagemaster::'      => '::',
                                      'pagemaster:input:' => 'tid::',
                                      'pagemaster:input:' => 'tid:pid:workflowstate',
                                      'pagemaster:full:'  => 'tid:pid:template',
                                      'pagemaster:list:'  => 'tid::template'
                                     );

// Module depedencies
$modversion['dependencies'] = array(
                                    array('modname'    => 'scribite',
                                          'minversion' => '2.0',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                    array('modname'    => 'Thumbnail',
                                          'minversion' => '1.1',
                                          'maxversion' => '',
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
                                   );
