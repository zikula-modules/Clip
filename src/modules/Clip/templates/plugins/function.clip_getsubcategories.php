<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Retrieve a list of categories.
 *
 * Available attributes:
 *  - cid    (integer) The parent category ID.
 *  - assign (string) The name of a template variable to assign the output to.
 *
 * Example:
 *
 *  Get the subcategories of category #1 and assign it to the template variable $categories:
 *
 *  <samp>{clip_getsubcategories cid=1 assign='categories'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_clip_getsubcategories($params, Zikula_View $view)
{
    $assign      = isset($params['assign'])      ? $params['assign']          : null;
    $cid         = isset($params['cid'])         ? (int)$params['cid']        : 0;
    $recurse     = isset($params['recurse'])     ? $params['recurse']         : true;
    $relative    = isset($params['relative'])    ? $params['relative']        : false;
    $includeRoot = isset($params['includeRoot']) ? $params['includeRoot']     : false;
    $includeLeaf = isset($params['includeLeaf']) ? $params['includeLeaf']     : true;
    $all         = isset($params['all'])         ? $params['all']             : false;
    $excludeCid  = isset($params['excludeCid'])  ? (int)$params['excludeCid'] : '';
    $assocKey    = isset($params['assocKey'])    ? $params['assocKey']        : 'id';
    $sortField   = isset($params['sortField'])   ? $params['sortField']       : 'sort_value';

    if (!$cid) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_getsubcategories', 'cid')));
    }

    if (!$assign) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_getsubcategories', 'assign')));
    }

    $cats = CategoryUtil::getSubCategories($cid, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, null, $sortField, null);

    $lang = ZLanguage::getLanguageCode();

    foreach ($cats as &$cat) {
        $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
    }

    $view->assign($assign, $cats);
}
