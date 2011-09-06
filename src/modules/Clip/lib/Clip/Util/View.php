<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Util
 */

/**
 * Utility class used in Clip templates.
 *
 * The methods of this class behaves as Smarty plugins.
 * The 'assign' parameter is handled automatically.
 */
class Clip_Util_View
{
    /**
     * Get one publication.
     *
     * Available attributes:
     *  - assign        (string)  The name of a template variable to assign the output to.
     *  - toarray       (boolean) Whether to convert the resulting publications to an array (default: false).
     *  - tid           (integer) ID of the publication type.
     *  - pid           (integer) ID of the publication.
     *  - id            (integer) ID of the publication revision (optional if pid is used).
     *  - checkperm     (boolean) Whether to check the permissions.
     *  - handleplugins (boolean) Whether to parse the plugin fields.
     *  - loadworkflow  (boolean) Whether to add the workflow information.
     *  - rel           (array)   Relation configuration flags to use {load, onlyown, processrefs, checkperm, handleplugins, loadworkflow}.
     *
     * Example:
     *
     *  Get a specific publication of the pubtype #1 and assign it to the template variable $pub:
     *
     *  <samp>{clip_util->getone tid=1 id=$pubdata.relation assign='pub'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function getone($args, Zikula_View &$view)
    {
        if (!isset($args['pid']) || empty($args['pid'])) {
            $args['pid'] = ModUtil::apiFunc('Clip', 'user', 'getPid', $args);
        }

        // API call
        $pub = ModUtil::apiFunc('Clip', 'user', 'get', $args);

        // processing
        if (isset($args['toarray']) && $args['toarray']) {
            $pub = $pub->toArray();
        }

        return $pub;
    }

    /**
     * Get many publications.
     *
     * Available attributes:
     *  - assign        (string)  The name of a template variable to assign the output to (default: pubs).
     *  - toarray       (boolean) Whether to convert the resulting publications to an array (default: false).
     *  - tid           (integer) ID of the publication type.
     *  - filter        (string)  Filter string.
     *  - orderby       (string)  OrderBy string.
     *  - startnum      (integer) Offset to start from.
     *  - itemsperpage  (integer) Number of items to retrieve.
     *  - checkperm     (boolean) Whether to check the permissions.
     *  - handleplugins (boolean) Whether to parse the plugin fields.
     *  - loadworkflow  (boolean) Whether to add the workflow information.
     *  - rel           (array)   Relation configuration flags to use {load, onlyown, processrefs, checkperm, handleplugins, loadworkflow}.
     *
     * Example:
     *
     *  Get a filtered list of publications of the pubtype #2 and assign it to the template variable $pubs:
     *
     *  <samp>{clip_util->getmany tid=2 filter="relation:eq:`$pubdata.id`" assign='pubs'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function getmany($args, Zikula_View &$view)
    {
        $args['countmode']    = 'no';
        $args['itemsperpage'] = isset($args['itemsperpage']) ? $args['itemsperpage'] : ModUtil::getVar('Clip', 'maxperpage', 100);
        $args['orderby']      = isset($args['orderby']) ? $args['orderby'] : '';

        $args['where']   = array();
        $args['where'][] = array('core_online = ?', 1);
        $args['where'][] = array('core_visible = ?', 1);
        $args['where'][] = array('core_intrash = ?', 0);

        $args['filter'] = isset($args['filter']) ? $args['filter'] : '()';
        // search for additional filters like FilterUtil
        $i = 1;
        while (isset($args["filter$i"])) {
            $args['filter'] .= '*' . !empty($args["filter$i"]) ? $args["filter$i"] : '()';
            $i++;
        }

        // API call
        $pubs = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        // processing
        $pubs = (isset($args['toarray']) && $args['toarray']) ? $pubs['publist']->toArray() : $pubs['publist'];

        return $pubs;
    }

    /**
     * Count the publications under a criteria.
     *
     * Available attributes:
     *  - assign  (string)  The name of a template variable to assign the output to (optional).
     *  - tid     (integer) ID of the publication type.
     *  - filter  (string)  Filter string.
     *  - online  (boolean) Online status of the publications (default: 1).
     *  - visible (boolean) Visibility status of the publications (default: 1).
     *  - intrash (boolean) Recycle byn status of the publications (default: 0).
     *
     * Example:
     *
     *  Get a the number of publications of the pubtype #3 related with the current one:
     *
     *  <samp>{clip_util->count tid=3 filter="relation:eq:`$pubdata.id`"}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function count($args, Zikula_View &$view)
    {
        // status
        $online  = isset($args['online'])  ? (int)(bool)$args['online']  : 1;
        $visible = isset($args['visible']) ? (int)(bool)$args['visible'] : 1;
        $intrash = isset($args['intrash']) ? (int)(bool)$args['intrash'] : 0;
        unset($args['online'], $args['visible'], $args['intrash']);

        $args['countmode']    = 'just';

        $args['where']   = array();
        $args['where'][] = array('core_online = ?',  $online);
        $args['where'][] = array('core_visible = ?', $visible);
        $args['where'][] = array('core_intrash = ?', $intrash);

        $args['filter'] = isset($args['filter']) ? $args['filter'] : '()';
        // search for additional filters like FilterUtil
        $i = 1;
        while (isset($args["filter$i"])) {
            $args['filter'] .= '*' . !empty($args["filter$i"]) ? $args["filter$i"] : '()';
            $i++;
        }

        // API call
        $pubs = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        return $pubs['pubcount'];
    }

    /**
     * Retrieve a list of categories.
     *
     * Available attributes:
     *  - cid    (integer) The parent category ID.
     *  - assign (string)  The name of a template variable to assign the output to.
     *
     * Example:
     *
     *  Get the subcategories of category #1 and assign it to the template variable $categories:
     *
     *  <samp>{clip_util->getsubcategories cid=1 assign='categories'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    function getsubcategories($params, Zikula_View $view)
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
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->getsubcategories', 'cid')));
        }

        if (!$assign) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->getsubcategories', 'assign')));
        }

        $cats = CategoryUtil::getSubCategories($cid, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, null, $sortField, null);

        $lang = ZLanguage::getLanguageCode();

        foreach ($cats as &$cat) {
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
        }

        return $cats;
    }
}
