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
     *  - distinct      (string)  Distinct field to select.
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
        $args['distinct']     = isset($args['distinct']) ? $args['distinct'] : null;
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
     * Tabulate a collection.
     *
     * Available attributes:
     *  - assign (string) The name of a template variable to assign the output to (optional).
     *  - var    (string) Name of the template variable to process.
     *  - a,b,c  (string) Field names to nest. The last is the value.
     *
     * Example:
     *
     *  Get an array of values ready to tabulate:
     *
     *  <samp>{clip_util->tabulate var='collection' a='date' b='category' c='value' assign='table'}</samp>
     *
     * @param array       $args All parameters passed to this plugin from the template.
     * @param Zikula_View $view Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function tabulate($args, Zikula_View &$view)
    {
        $var = isset($args['var']) ? $args['var'] : null;

        if (!$var) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->tabulate', 'var')));
        }

        if (!isset($args['a'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->tabulate', '"a"')));
        }

        // gets and validates the data to tabulate
        $list = $view->getTplVar($var);

        if ($list instanceof Doctrine_Collection) {
            $record = $list->getFirst();
        } else if (is_array($list)) {
            $record = reset($list);
        } else {
            $view->trigger_error(__f('Error! in %1$s: the variable [%2$s] is not a collection or array.', array('clip_util->tabulate', $var)));
        }

        // collects and validates the required fields
        $columns = array();

        $name = 'a';
        do {
            if (!isset($record[$args[$name]])) {
                return $view->trigger_error(__f('Error! in %1$s: the parameter [%2$s] specifies a non existing field in the data to tabulate.', array('clip_util->tabulate', $name)));
            }
            $columns[] = $args[$name];
            $name++;
        } while (isset($args[$name]));

        $table = array();

        foreach ($list as $record) {
            $this->tabulate_rec($table, $record, $columns);
        }

        return $table;
    }
    
    private function tabulate_rec(&$table, $record, $columns, $level = 0)
    {
        $col = $columns[$level];
        $val = $record[$col];

        if (count($columns)-1 == $level) {
            $table = $val;
        } else {
            if (!isset($table[$val])) {
                $table[$val] = array();
            }
            $this->tabulate_rec($table[$val], $record, $columns, $level + 1);
        }
    }

    /**
     * Get a common field of a list.
     *
     * Available attributes:
     *  - list   (mixed)  The collection/array to process.
     *  - field  (string) The field to retrieve.
     *  - assign (string) The name of a template variable to assign the output to.
     *
     * Example:
     *
     *  Get the tid of a given collection and assign it to the template variable $tid:
     *
     *  <samp>{clip_util->getfield list=$publist field='core_tid' assign='tid'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return mixed Value of the field
     */
    function getvalue($params, Zikula_View $view)
    {
        $list  = isset($params['list'])  ? $params['list']  : array();
        $field = isset($params['field']) ? (string)$params['field'] : '';

        if (!$field) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->getvalue', 'field')));
        }

        if (!$list) {
            return;
        } else if ($list instanceof Doctrine_Collection) {
            $record = $list->getFirst();
        } else if (is_array($list)) {
            $record = reset($list);
        } else {
            $view->trigger_error(__f('Error! in %1$s: the passed list is not a Doctrine_Collection nor an array.', 'clip_util->getvalue'));
        }

        if (!isset($record[$field])) {
            $view->trigger_error(__f('Error! in %1$s: the field [%2$s] does not exist on a list record.', array('clip_util->getvalue', 'field')));
        }

        return $record[$field];
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
        $cid         = isset($params['cid'])         ? (int)$params['cid']        : 0;
        $recurse     = isset($params['recurse'])     ? $params['recurse']         : true;
        $relative    = isset($params['relative'])    ? $params['relative']        : false;
        $includeRoot = isset($params['includeRoot']) ? $params['includeRoot']     : false;
        $includeLeaf = isset($params['includeLeaf']) ? $params['includeLeaf']     : true;
        $onlyLeafs   = isset($params['onlyLeafs'])   ? $params['onlyLeafs']       : false;
        $all         = isset($params['all'])         ? $params['all']             : false;
        $excludeCid  = isset($params['excludeCid'])  ? (int)$params['excludeCid'] : '';
        $assocKey    = isset($params['assocKey'])    ? $params['assocKey']        : 'id';
        $sortField   = isset($params['sortField'])   ? $params['sortField']       : 'sort_value';

        if (!$cid) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->getsubcategories', 'cid')));
        }

        $cats = CategoryUtil::getSubCategories($cid, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, null, $sortField, null);

        $lang = ZLanguage::getLanguageCode();

        foreach ($cats as $k => &$cat) {
            if ($onlyLeafs && !(bool)$cat['is_leaf']) {
                unset($cats[$k]);
                continue;
            }
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
        }

        return $cats;
    }
}
