<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
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
     * @param object  &$list               The list object (here: $this).
     * @param boolean $includeEmptyElement Whether or not to include an empty null item.
     * @param array   $params              The parameters passed from the Smarty plugin.
     *
     * @return void
     */
    public static function loadParameters(&$list, $includeEmptyElement, $params)
    {
        $all            = isset($params['all'])         ? $params['all']         : false;
        $lang           = isset($params['lang'])        ? $params['lang']        : ZLanguage::getLanguageCode();
        $list->category = isset($params['category'])    ? $params['category']    : 0;
        $includeLeaf    = isset($params['includeLeaf']) ? $params['includeLeaf'] : true;
        $includeRoot    = isset($params['includeRoot']) ? $params['includeRoot'] : false;
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

        foreach ($allCats as $cat) {
            $cslash = StringUtil::countInstances(isset($cat['ipath_relative']) ? $cat['ipath_relative'] : $cat['ipath'], '/');
            $indent = '';
            if ($cslash > 0) {
                $indent = '| ' . substr($line, 0, $cslash * 2);
            }

            $catName = html_entity_decode((isset($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name']));
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

        self::loadParameters($this, $this->includeEmptyElement, $params);

        parent::create($params, $filter);
    }
}
