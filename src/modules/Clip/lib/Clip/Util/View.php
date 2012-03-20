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
     * Utility var storage.
     *
     * @var array
     */
    protected $storage = array();

    /**
     * Store an utility var.
     *
     * Available attributes:
     *  - assign (string) The name of a template variable to assign the output to.
     *  - var    (string) The name of the variable to store.
     *  - value  (mixed)  The value to store.
     *
     * Example:
     *
     *  Store an utility value to be used later on a Clip template:
     *
     *  <samp>{clip_util->store var='myvar' value=$myvar}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function store($params, Zikula_Form_View &$view)
    {
        if (!isset($params['var']) || !$params['var']) {
            return;
        }

        $this->storage[$params['var']] = isset($params['value']) ? $params['value'] : null;
    }

    /**
     * Retrieve an utility var.
     *
     * Available attributes:
     *  - assign (string) The name of a template variable to assign the output to.
     *  - var    (string) The name of the variable to store.
     *
     * Example:
     *
     *  Retrieve an utility value stored previously:
     *
     *  <samp>{clip_util->retrieve var='myvar' assign='retrieved'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return mixed
     */
    public function retrieve($params, Zikula_Form_View &$view)
    {
        $var = isset($params['var']) ? $params['var'] : null;

        return $var && isset($this->storage[$var]) ? $this->storage[$var] : null;
    }

    /**
     * Deletes an utility var.
     *
     * Available attributes:
     *  - assign (string) The name of a template variable to assign the popped value to.
     *  - var    (string) The name of the variable to delete.
     *
     * Example:
     *
     *  Delete an utility value stored previously:
     *
     *  <samp>{clip_util->pop var='myvar' assign='deleted'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return mixed
     */
    public function pop($params, Zikula_Form_View &$view)
    {
        $var = isset($params['var']) ? $params['var'] : null;

        if ($var && isset($this->storage[$var])) {
            $value = $this->storage[$var];
            unset($this->storage[$var]);
        }

        return isset($value) ? $value : null;
    }

    /**
     * Get one publication.
     *
     * Available attributes:
     *  - assign        (string)  The name of a template variable to assign the output to.
     *  - array         (boolean) Whether to fetch the resulting publication as array (default: false).
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

        $pubtype = $view->getTplVar('pubtype');

        $args['tid'] = isset($args['tid']) ? $args['tid'] : $pubtype->tid;

        // API call
        $pub = ModUtil::apiFunc('Clip', 'user', 'get', $args);

        return $pub;
    }

    /**
     * Get many publications.
     *
     * Available attributes:
     *  - assign        (string)  The name of a template variable to assign the output to (default: pubs).
     *  - array         (boolean) Whether to fetch the resulting publications as array (default: false).
     *  - tid           (integer) ID of the publication type.
     *  - filter        (string)  Filter string.
     *  - distinct      (string)  Distinct field(s) to select.
     *  - function      (string)  Function(s) to perform.
     *  - groupby       (string)  GroupBy field.
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
        $pubtype = $view->getTplVar('pubtype');

        $args['tid']           = isset($args['tid']) ? $args['tid'] : $pubtype->tid;
        $args['distinct']      = isset($args['distinct']) ? $args['distinct'] : null;
        $args['function']      = isset($args['function']) ? $args['function'] : null;
        $args['groupby']       = isset($args['groupby']) ? $args['groupby'] : null;
        $args['countmode']     = 'no';
        $args['itemsperpage']  = isset($args['itemsperpage']) ? $args['itemsperpage'] : ModUtil::getVar('Clip', 'maxperpage', 100);
        $args['orderby']       = isset($args['orderby']) ? $args['orderby'] : '';
        $args['handleplugins'] = isset($args['handleplugins']) ? $args['handleplugins'] : false;

        $args['where']   = array();
        $args['where'][] = array('core_online = ?', 1);
        $args['where'][] = array('core_visible = ?', 1);
        $args['where'][] = array('core_intrash = ?', 0);

        $args['filter'] = isset($args['filter']) && !empty($args['filter']) ? $args['filter'] : '()';
        // search for additional filters like FilterUtil
        $i = 1;
        while (isset($args["filter$i"]) && $args["filter$i"]) {
            $args['filter'] .= '*' . $args["filter$i"];
            $i++;
        }

        // API call
        $pubs = ModUtil::apiFunc('Clip', 'user', 'getall', $args);

        return $pubs['publist'];
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
        $pubtype = $view->getTplVar('pubtype');

        $args['tid']       = isset($args['tid']) ? $args['tid'] : $pubtype->tid;
        $args['countmode'] = 'just';

        // status
        $online  = isset($args['online'])  ? (int)(bool)$args['online']  : 1;
        $visible = isset($args['visible']) ? (int)(bool)$args['visible'] : 1;
        $intrash = isset($args['intrash']) ? (int)(bool)$args['intrash'] : 0;
        unset($args['online'], $args['visible'], $args['intrash']);

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
     * Matches two collections.
     * 
     * The non existing values are filled with zero.
     *
     * Available attributes:
     *  - assign (string) The name of a template variable to assign the output to (optional).
     *  - keys   (mixed)  Array of the required data indexes.
     *  - values (mixed)  Data to organize and validate against the provided keys.
     *  - index  (bool)   Whether to index the result with the keys or not (default: false).
     *
     * Example:
     *
     *  Get an array of values ready to tabulate:
     *
     *  <samp>{clip_util->match keys=$list values=$data assign='listdata'}</samp>
     *
     * @param array       $args All parameters passed to this plugin from the template.
     * @param Zikula_View $view Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function match($args, Zikula_View &$view)
    {
        if (!isset($args['keys'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->match', 'keys')));
        }

        if (!isset($args['values'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->match', 'values')));
        }

        $index = isset($args['index']) ? $args['index'] : false;

        $data = array();

        foreach ($args['keys'] as $key) {
            if ($index) {
                $data[$key] = isset($args['values'][$key]) ? $args['values'][$key] : 0;
            } else {
                $data[] = isset($args['values'][$key]) ? $args['values'][$key] : 0;
            }
        }

        return $data;
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
     *  <samp>{clip_util->tab var='collection' a='date' b='category' c='value' assign='table'}</samp>
     *
     * @param array       $args All parameters passed to this plugin from the template.
     * @param Zikula_View $view Reference to the {@link Zikula_View} object.
     *
     * @return void
     */
    public function tab($args, Zikula_View &$view)
    {
        $var = isset($args['var']) ? $args['var'] : null;

        if (!$var) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->tab', 'var')));
        }

        if (!isset($args['a'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->tab', '"a"')));
        }

        // gets and validates the data to tabulate
        $list = $view->getTplVar($var);

        if ($list instanceof Doctrine_Collection) {
            $record = $list->getFirst();
        } else if (is_array($list)) {
            $record = reset($list);
        } else {
            $view->trigger_error(__f('Error! in %1$s: the variable [%2$s] is not a collection or array.', array('clip_util->tab', $var)));
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
            $this->tab_rec($table, $record, $columns);
        }

        return $table;
    }
    
    private function tab_rec(&$table, $record, $columns, $level = 0)
    {
        $col = $columns[$level];
        $val = $record[$col];

        if (count($columns)-1 == $level) {
            // reached the end
            if (!$level) {
                // special treatment for unidimensional array
                $table[] = $val;
            } else {
                $table = $val;
            }
        } else {
            if (!isset($table[$val])) {
                $table[$val] = array();
            }
            $this->tab_rec($table[$val], $record, $columns, $level + 1);
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
     * Retrieve a category.
     *
     * Available attributes:
     *  - cid    (integer) The parent category ID.
     *  - field  (string)  The name of the field to retrieve (optional).
     *  - assign (string)  The name of a template variable to assign the output to.
     *
     * Examples:
     *
     *  Get the category on $pub.cat and assign it to the template variable $category:
     *
     *  <samp>{clip_util->getcategory cid=$pub.cat assign='category'}</samp>
     *
     *  Get the dsplay name of $pub.cat:
     *
     *  <samp>{clip_util->getcategory cid=$pub.cat field='fullTitle'}</samp>
     *
     * @param array       $params All parameters passed to this plugin from the template.
     * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
     *
     * @return mixed
     */
    function getcategory($params, Zikula_View $view)
    {
        $cid   = isset($params['cid'])   ? (int)$params['cid'] : 0;
        $field = isset($params['field']) ? $params['field']    : null;

        if (!$cid) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_util->getcategory', 'cid')));
        }

        $lang = ZLanguage::getLanguageCode();

        $cat = CategoryUtil::getCategoryByID($cid);

        if (!$cat) {
            return $field ? '#error#' : array();
        }

        $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
        $cat['fullDesc']  = isset($cat['display_desc'][$lang]) ? $cat['display_desc'][$lang] : '';

        if ($field) {
            if (!isset($cat[$field])) {
                $view->trigger_error(__f('Error! Category [%1$s] does not have the field [%2$s] set.', array($id, $field)));
            }

            return $cat[$field];
        }

        return $cat;
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
     * @return mixed
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

        $lang = ZLanguage::getLanguageCode();

        $cats = CategoryUtil::getSubCategories($cid, $recurse, $relative, $includeRoot, $includeLeaf, $all, $excludeCid, $assocKey, null, $sortField, null);

        foreach ($cats as $k => &$cat) {
            if ($onlyLeafs && !(bool)$cat['is_leaf']) {
                unset($cats[$k]);
                continue;
            }
            $cat['fullTitle'] = isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'];
            $cat['fullDesc']  = isset($cat['display_desc'][$lang]) ? $cat['display_desc'][$lang] : '';
        }

        return $cats;
    }
}
