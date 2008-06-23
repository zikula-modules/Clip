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

// Module name:
$modversion['name']           = 'pagemaster';

// Version (10 chars or less):
$modversion['version']        = '0.1';
$modversion['description']    = 'Content Module like pagesetter';
$modversion['displayname']    = 'pagemaster';

// Used by the Credits module:
$modversion['credits']        = 'pndocs/credits.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['help']           = 'pndocs/help.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'kundi';
$modversion['contact']        = 'mk@sexyandfamous.com';

// Module security:
$modversion['securityschema'] = array(
                                      'pagemaster::' => '::',
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
