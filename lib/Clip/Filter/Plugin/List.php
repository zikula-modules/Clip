<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Filter_Plugin
 */

/**
 * List category selector plugin.
 *
 * This plugin creates a category selector using a drop down list.
 * The selected value of the base drop down list will be set to ID of the selected category.
 */
class Clip_Filter_Plugin_List extends Clip_Filter_Plugin_ListDropdown
{
    /**
     * Base category.
     *
     * May be the id, the category array or the path.
     *
     * @var mixed
     */
    public $category;

    /**
     * Enable inclusion of an empty null value element.
     *
     * @var boolean (default true)
     */
    public $includeEmptyElement;

    /**
     * Enable inclusion of categories with no publications.
     *
     * @var boolean (default false)
     */
    public $includeEmpty;

    /**
     * Show the quantity of publications inside the category.
     *
     * @var boolean (default false)
     */
    public $showQuantity;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Load the parameters.
     *
     * static method called by other category based plugins.
     *
     * @param object           &$list               The list object (here: $this).
     * @param boolean          $includeEmptyElement Whether or not to include an empty null item.
     * @param array            $params              The parameters passed from the Smarty plugin.
     * @param Clip_Filter_Form $filter              Clip filter form manager instance.
     *
     * @return void
     */
    public static function loadParameters(&$list, $includeEmptyElement, $params, $filter)
    {
        $all            = isset($params['all'])         ? $params['all']         : false;
        $lang           = isset($params['lang'])        ? $params['lang']        : ZLanguage::getLanguageCode();
        $list->category = isset($params['category'])    ? $params['category']    : 0;
        $includeLeaf    = isset($params['includeLeaf']) ? $params['includeLeaf'] : true;
        $includeRoot    = isset($params['includeRoot']) ? $params['includeRoot'] : false;
        $includeEmpty   = isset($params['includeEmpty']) ? $params['includeEmpty'] : false;
        $showQuantity   = isset($params['showQuantity']) ? $params['showQuantity'] : false;
        $path           = isset($params['path'])        ? $params['path']        : '';
        $pathfield      = isset($params['pathfield'])   ? $params['pathfield']   : 'path';
        $recurse        = isset($params['recurse'])     ? $params['recurse']     : true;
        $relative       = isset($params['relative'])    ? $params['relative']    : true;
        $sortField      = isset($params['sortField'])   ? $params['sortField']   : 'sort_value';
        $catField       = isset($params['catField'])    ? $params['catField']    : 'id';

        $allCats = array();

        // if we don't have a category-id we see if we can get a category by path
        if (!$list->category && $path) {
            $list->category = CategoryUtil::getCategoryByPath($path, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_array($list->category) && isset($list->category['id']) && is_integer($list->category['id'])) {
            // check if we have an actual category object with a numeric ID set
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_numeric($list->category)) {
            // check if we have a numeric category
            $list->category = CategoryUtil::getCategoryByID($list->category);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);

        } elseif (is_string($list->category) && strpos($list->category, '/') === 0) {
            // check if we have a string/path category
            $list->category = CategoryUtil::getCategoryByPath($list->category, $pathfield);
            $allCats = CategoryUtil::getSubCategoriesForCategory($list->category, $recurse, $relative, $includeRoot, $includeLeaf, $all, null, '', null, $sortField);
        }

        if (!$allCats) {
            $allCats = array();
        }

        $line = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -';

        if ($includeEmptyElement) {
            $list->addItem('', null);
        }

        // check the indent level
        $maxlevel = 0;
        foreach ($allCats as &$c) {
            $c['ilevel'] = StringUtil::countInstances(isset($c['ipath_relative']) ? $c['ipath_relative'] : $c['ipath'], '/');
            // grab the max one
            if ($maxlevel < $c['ilevel']) {
                $maxlevel = $c['ilevel'];
            }
        }

        $emptylvl = 99;
        foreach ($allCats as $cat) {
            // check if we need to omit the category
            if (!$includeEmpty) {
                if ($emptylvl < $cat['ilevel']) {
                    continue;
                } else {
                    $emptylvl = 99;
                }
            }

            // count the publications inside the category
            $op  = ($cat['ilevel'] < $maxlevel) ? 'sub' : 'eq';
            $res = ModUtil::apiFunc('Clip', 'user', 'getall',
                                    array('tid'           => $filter->getTid(),
                                          'countmode'     => 'just',
                                          'filter'        => "{$list->field}:{$op}:{$cat['id']},core_online:eq:1,core_visible:eq:1,core_intrash:eq:0",
                                          'checkperm'     => false,
                                          'handleplugins' => false));

            if (!$includeEmpty && $res['pubcount'] == 0) {
                $emptylvl = $cat['ilevel'];
                continue;
            }

            $indent = '';
            if ($cat['ilevel'] > 0) {
                $indent = '| ' . substr($line, 0, $cat['ilevel'] * 2);
            }

            $catName = html_entity_decode((isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name']));
            if ($showQuantity) {
                $catName .= " ({$res['pubcount']})";
            }

            $list->addItem($indent . ' ' . $catName, isset($cat[$catField]) ? $cat[$catField] : $cat['id']);
        }
    }

    /**
     * Create event handler.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function create($params, $filter)
    {
        $this->includeEmptyElement = (isset($params['includeEmptyElement']) ? $params['includeEmptyElement'] : true);
        $this->op                  = (array_key_exists('op', $params) ? $params['op'] : 'eq');

        if (!isset($params['category']) || !$params['category']) {
            $field  = Clip_Util::getPubFields($filter->getTid(), $this->field);
            $plugin = Clip_Util_Plugins::get($field['fieldplugin']);
            $params['category'] = $plugin->getRootCategoryID($field['typedata']);
        }

        self::loadParameters($this, $this->includeEmptyElement, $params, $filter);

        parent::create($params, $filter);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_View $view Reference to Zikula_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_View $view)
    {
        // adds the form observer
        $filter   = $view->get_registered_object('clip_filter');
        $filterid = $filter->getFilterID($this->field);

        if ($filter->hasPlugin($this->field, $this->id.'_op')) {
            $code = "$('$filterid').value = '{$this->field}:'+\$F('{$this->id}_op')+':'+\$F('{$this->id}');";
        } else {
            $code = "$('$filterid').value = '{$this->field}:{$this->op}:'+\$F('{$this->id}');";
        }
        $code = "if (\$F('{$this->id}') != '#null#') { $code }";

        $filter->addFormObserver($code);

        return parent::render($view);
    }
}
