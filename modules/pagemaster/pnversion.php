<?php

/**
 *
 * pnversion
 *
 * Module version information
 *
 * @author      kundi
 * @version     0.1
 * @link        http://www.pagemaster.org
 * @license     http://www.gnu.org/copyleft/gpl.html  GNU General Public License
 * @package     PostNuke_3rd_party_Modules
 * @subpackage  pagemaster
 *
 */

//---- Module name:
$modversion['name']			= 'pagemaster' ;

//---- Version (10 chars or less):
$modversion['version']		= '0.1' ;
$modversion['description']	= 'Content Modul like pagesetter' ;
$modversion['displayname']	= 'pagemaster' ;

//---- Used by the Credits module:
$modversion['changelog']    = 'pndocs/changelog.txt';
$modversion['help']         = 'pndocs/help.txt';
$modversion['license']		= '' ;
$modversion['official']		= 0;
$modversion['author']		= 'kundi' ;
$modversion['contact']		= 'mk@sexyandfamous.com' ;

//---- Module security:
$modversion['securityschema'] = 
	array(
	'pagemaster::' => '::',
	'pagemaster:input:' => 'tid::',
	'pagemaster:input:' => 'tid:pid:workflowstate', 
  	'pagemaster:full:' => 'tid:pid:template',
  	'pagemaster:list:' => 'tid:template'
  	);

//---- Module depedencies
$modversion['dependencies'] = array(
                                    array('modname'    => 'scribite', 
                                          'minversion' => '1.3', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                    array('modname'    => 'Thumbnail', 
                                          'minversion' => '1.1', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));
