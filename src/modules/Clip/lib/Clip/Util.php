<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Lib
 */

/**
 * Clip Util.
 */
class Clip_Util
{
    const REGEX_FOLDER   = '#[^a-z0-9_/]+#i';
    const REGEX_TEMPLATE = '/[^a-z0-9_\.\-]+/i';
    const REGEX_URLTITLE = '/[^a-z0-9_\-]+/i';

    /**
     * Arguments store.
     *
     * @var array
     */
    protected static $args = array();

    /**
     * self::$args getter.
     */
    public static function getArgs($id=null)
    {
        if ($id && isset(self::$args[$id])) {
            return self::$args[$id];
        }

        return self::$args;
    }

    /**
     * self::$args setter.
     */
    public static function setArgs($id, $args)
    {
        self::$args[$id] = $args;
    }

    /**
     * Clip boot
     * 
     * @return void
     */
    public static function boot()
    {
        static $booted = false;

        if (!$booted) {
            // add the dynamic models path
            ZLoader::addAutoloader('ClipModels', realpath(StringUtil::left(ModUtil::getVar('Clip', 'modelspath'), -11)));

            // check if the models are already created
            Clip_Generator::checkModels();

            $booted = true;
        }
    }

    /**
     * Extract the TID from a string end.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getDefaultCategoryID()
    {
        static $id;

        if (!isset($id)) {
            $id = (int)CategoryRegistryUtil::getRegisteredModuleCategory('Clip', 'clip_pubtypes', 'Global');
        }

        return $id;
    }

    /**
     * Extract the arguments.
     *
     * @param array $args
     *
     * @return array Extracted arguments.
     */
    public static function getClipArgs(&$clipvalues, $get, $args = array())
    {
        foreach (array_keys($get->getCollection()) as $param) {
            if (strpos($param, '_') === 0) {
                $clipvalues[substr($param, 1)] = $get->filter($param);
            }
        }

        foreach (array_keys((array)$args) as $param) {
            if (strpos($param, '_') === 0) {
                $clipvalues[substr($param, 1)] = $args[$param];
            }
        }
    }

    /**
     * Format the orderby parameter.
     *
     * @param string $orderby   Order by string.
     * @param array  $relfields Relation field aliases on this table.
     *
     * @return string Orderby clause.
     */
    public static function createOrderBy($orderby, $relfields = array())
    {
        $orderbylist = !is_array($orderby) ? explode(',', $orderby) : $orderby;
        $orderbylist = array_map('trim', $orderbylist);

        $orderby = '';
        foreach ($orderbylist as $key => $value) {
            if ($key > 0) {
                $orderby .= ', ';
            }
            // $value = {col[:(asc|desc)]}
            $value    = explode(':', $value);
            $orderby .= isset($relfields[$value[0]]) ? DataUtil::formatForStore($relfields[$value[0]]) : DataUtil::formatForStore($value[0]);
            $orderby .= (isset($value[1]) && in_array(strtoupper($value[1]), array('ASC', 'DESC')) ? ' '.strtoupper($value[1]) : '');
        }

        return $orderby;
    }

    /**
     * Name reference generator.
     *
     * @return string Random id.
     */
    public static function getNewFileReference()
    {
        $chars   = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charLen = strlen($chars);

        $id = '';

        for ($i = 0; $i < 30; ++ $i) {
            $id .= $chars[mt_rand(0, $charLen-1)];
        }

        return $id;
    }

    /**
     * Extract the filter from the input to build a cacheid.
     *
     * @param string $varname Name of the filter variable on use.
     *
     * @see FilterUtil::getFiltersFromInput()
     *
     * @return string Filter id to use inside cacheid.
     */
    public static function getFilterCacheId($varname = 'filter')
    {
        $i = 1;
        $filterid = array();

        // Get unnumbered filter string
        $filterStr = FormUtil::getPassedValue($varname, '');
        if (!empty($filterStr)) {
            $filterid[] = urldecode($filterStr);
        }

        // Get filter1 ... filterN
        while (true) {
            $filterStr = FormUtil::getPassedValue("{$varname}{$i}", '');

            if (empty($filterStr)) {
                break;
            }

            $filterid[] = urldecode($filterStr);
            ++$i;
        }

        if (count($filterid) > 0) {
            $filterid = implode('*', $filterid);
        }

        return self::getFilterCacheString($filterid);
    }

    /**
     * Checker of simple templates.
     *
     * Simple templates are display ones without a Publication loaded on them,
     * useful for notifications like pending.
     *
     * @param string $template Template to evaluate.
     *
     * @return boolean True if it's a simple template, false otherwise.
     */
    public static function isSimpleTemplate($template)
    {
        $simpletemplates = array('pending');

        if (!in_array($template, $simpletemplates)) {
            $template = strpos($template, 'simple') === 0 ? substr($template, 6) : false;
        }

        return $template;
    }

    /**
     * Replace some critical vars of the filter definition.
     *
     * @param string $filter Filter definition.
     *
     * @return string Filter string to use inside cacheid.
     */
    public static function getFilterCacheString($filter)
    {
        return str_replace(array('(', ')', '*', ','), array('-', '-', '__', '___'), $filter);
    }

    /**
     * Publication type configuration getter.
     *
     * @param string $section Section to retrieve.
     * @param array  $config  A configuration section to validate.
     *
     * @return array Pubtype config array.
     */
    public static function getPubtypeConfig($section = null, $config = array())
    {
        $result = array(
            'list' => array(
                'load' => false,
                'onlyown' => true,
                'checkperm' => false
            ),
            'display' => array(
                'load' => true,
                'onlyown' => true,
                'checkperm' => true
            ),
            'edit' => array(
                'load' => true,
                'onlyown' => true,
                'checkperm' => false
            )
        );

        if ($config && $section && isset($result[$section])) {
            $config = array_intersect_key($config, $result[$section]);
            return array_merge($result[$section], $config);
        }

        if ($section && isset($result[$section])) {
            return $result[$section];
        }

        return $result;
    }

    /**
     * Validates that a value is not a reserved word.
     *
     * @param string $value
     *
     * @return boolean True on valid, false on reserved word.
     */
    public static function validateReservedWord($value)
    {
        $reservedwords = array(
            'module', 'modname', 'func', 'type', 'tid', 'pid', 'id',
            'submit', 'edit', 'commandName', '__WORKFLOW__'
        );

        return (in_array($value, $reservedwords) || strpos('core_', $value) === 0 || strpos('rel_', $value) === 0);
    }

    /**
     * Validates a TID number.
     *
     * @param integer $tid
     *
     * @return boolean True on valid publication type, false otherwise.
     */
    public static function validateTid($tid)
    {
        if (is_numeric($tid) && $tid > 0 && self::getPubType($tid)) {
            return true;
        }

        return false;
    }

    /**
     * Extract the TID from a string end.
     *
     * @param string $tablename
     *
     * @return integer Publication type ID.
     */
    public static function getTidFromString($tablename)
    {
        $tid = '';
        while (is_numeric(substr($tablename, -1))) {
            $tid = substr($tablename, -1) . $tid;
            $tablename = substr($tablename, 0, strlen($tablename) - 1);
        }

        return $tid;
    }

    /**
     * Removes any numerical suffix of a string.
     *
     * @param string $string
     *
     * @return string String without numeric suffix.
     */
    public static function getStringPrefix($string)
    {
        $suffixnumber = self::getTidFromString($string);

        return str_replace($suffixnumber, '', $string);
    }

    /**
     * PubType getter.
     *
     * @param integer $tid Pubtype ID.
     *
     * @return Clip_Model_Pubtype Information of one or all the pubtypes.
     */
    public static function getPubType($tid = -1, $field = null, $force = false)
    {
        static $pubtypes;

        if (!isset($pubtypes) || $force) {
            $pubtypes = Doctrine_Core::getTable('Clip_Model_Pubtype')->getPubtypes();
        }

        if ($tid == -1) {
            return $pubtypes;
        }

        if (isset($pubtypes[$tid])) {
            $pubtype = self::getPubTypeSub($pubtypes[$tid], $tid);
            if ($pubtype != null) {
                if ($field) {
                    // TODO get() for unloaded properties?
                    return isset($pubtype[$field]) ? $pubtype[$field] : $field;
                }
                return $pubtype;
            }
        }

        $null = null;
        return $null;
    }

    /* Utility function to return the pubtype reference */
    private static function getPubTypeSub(&$pubtype, $tid)
    {
        if ($pubtype['tid'] == $tid) {
            return $pubtype;
        }

        $null = null;
        return $null;
    }

    /**
     * Pubtype Relations getter.
     *
     * @param integer $tid        Pubtype ID.
     * @param boolean $owningSide Whether to fetch the owning side relations of the pubtype.
     * @þaram boolean $force      Whether to force the refresh of the cache.
     *
     * @return array Relations for the passed pubtype.
     */
    public static function getRelations($tid = -1, $owningSide = true, $force = false)
    {
        static $relation_arr;

        if (!isset($relation_arr) || $force) {
            $relation_arr = Doctrine_Core::getTable('Clip_Model_Pubrelation')->getClipRelations();
        }

        $own = $owningSide ? 'own' : 'not';

        if ($tid == -1) {
            return $relation_arr[$own];
        }

        return isset($relation_arr[$own][$tid]) ? $relation_arr[$own][$tid] : array();
    }

    /**
     * PubFields getter.
     *
     * @param integer $tid     Pubtype ID.
     * @param string  $name    Name of the field to get.
     * @param string  $orderBy Field name to sort by.
     * @param boolean $attrs   Whether to load field attributes or not.
     *
     * @return array Array of fields of one or all the loaded pubtypes.
     */
    public static function getPubFields($tid, $name = null, $orderBy = 'lineno', $attrs = false)
    {
        static $pubfields_arr;

        $tid = (int)$tid;
        if ($tid && !isset($pubfields_arr[$tid])) {
            $pubfields_arr[$tid] = Doctrine_Core::getTable('Clip_Model_Pubfield')
                                   ->selectCollection("tid = '$tid'", $orderBy, -1, -1, 'name');
        }

        if ($name) {
            return isset($pubfields_arr[$tid][$name]) ? $pubfields_arr[$tid][$name] : array();
        }

        if ($attrs) {
            foreach ($pubfields_arr[$tid] as $name => &$field) {
                if ($field->hasMappedValue('attrs')) {
                    // already loaded
                    break;
                }

                $plugin = Clip_Util_Plugins::get($field['fieldplugin']);

                $field->mapValue('attrs', method_exists($plugin, 'clipAttributes') ? (array)$plugin->clipAttributes($field) : array());
            }
        }

        return isset($pubfields_arr[$tid]) ? $pubfields_arr[$tid] : array();
    }

    /**
     * PubField data getter.
     *
     * @param integer $tid      Pubtype ID.
     * @param string  $name     Name of the field to get.
     * @param string  $property Field to retrieve.
     *
     * @return mixed Field or one of its properties.
     */
    public static function getPubFieldData($tid, $name, $property = null)
    {
        if (!$name) {
            return null;
        }

        if (strpos($name, 'core_') === 0) {
            return Clip_Util_Plugins::getCoreFieldData($name, $property);
        }

        $pubfield = self::getPubFields($tid, $name);

        if (!$pubfield) {
            return null;
        }

        if ($property) {
            return isset($pubfield[$property]) ? $pubfield[$property] : null;
        }

        return $pubfield;
    }

    /**
     * Title field getter.
     *
     * @param integer $tid Pubtype ID.
     *
     * @return array One or all the pubtype titles.
     */
    public static function getTitleField($tid)
    {
        $titlefield = Doctrine_Core::getTable('Clip_Model_Pubfield')
                          ->selectField('name', "tid = '$tid' AND istitle = '1'");

        return $titlefield ? $titlefield : 'id';
    }

    /**
     * Loop the pubfields array until get the title field.
     *
     * @param array $pubfields
     *
     * @return string Name of the title field.
     */
    public static function findTitleField($pubfields)
    {
        $core_title = 'id';

        foreach ($pubfields as $i => $pubfield) {
            if ($pubfield['istitle'] == 1) {
                $core_title = $pubfield['name'];
                break;
            }
        }

        return $core_title;
    }

    /**
     * Install the default 'blog' and 'staticpages' publication types.
     *
     * @return void
     */
    public static function installDefaultypes()
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $lang  = ZLanguage::getLanguageCode();
        $batch = new Clip_Import_Batch();

        $defaults = array('blog', 'staticpages');

        foreach ($defaults as $default) {
            // check if the pubtype exists
            $pubtype = Doctrine_Core::getTable('Clip_Model_Pubtype')->findByUrltitle($default);
            if (count($pubtype)) {
                LogUtil::registerStatus(__f("There is already a '%s' publication type.", $default, $dom));
            } else {
                // import the default XML
                $file = "modules/Clip/docs/xml/$lang/$default.xml";
                if (!file_exists($file)) {
                    $file = "modules/Clip/docs/xml/en/$default.xml";
                }

                if ($batch->setup(array('url' => $file)) && $batch->execute()) {
                    LogUtil::registerStatus(__f("Default '%s' publication type created successfully.", $default, $dom));
                } else {
                    LogUtil::registerStatus(__f("Could not import the '%s' publication type.", $default, $dom));
                }
            }
        }
    }

    /**
     * Form view instance builder.
     *
     * @param Zikula_Controller $controller Related controller.
     * @param boolean           $force      Wheter to force the creation of a new instance or not.
     * @see FormUtil::newForm
     *
     * @return Clip_Form_View Form view instance.
     */
    public static function newForm($controller=null, $force=false)
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId      = 'zikula.view.form.clip';

        if ($force && $serviceManager->hasService($serviceId)) {
            $serviceManager->detachService($serviceId);
        }

        if ($force || !$serviceManager->hasService($serviceId)) {
            $form = new Clip_Form_View($serviceManager, 'Clip');
            $serviceManager->attachService($serviceId, $form);
        } else {
            $form = $serviceManager->getService($serviceId);
        }

        if ($controller) {
            $form->setController($controller);
            $form->assign('controller', $controller);
            $form->setEntityManager($controller->getEntityManager());
        }

        return $form;
    }

    /**
     * Clear the Theme's Engine cache.
     *
     * @return void
     */
    public static function clearThemeCache($cacheid)
    {
        $serviceManager = ServiceUtil::getManager();
        $serviceId = 'zikula.theme';

        if ($serviceManager->hasService($serviceId)) {
            $themeInstance = $serviceManager->getService($serviceId);
            if ($themeInstance->getCaching()) {
                $themeInstance->clear_cache(null, $cacheid);
            }
        }
    }

    /**
     * Registration of Clip's plugins sensible to cache.
     *
     * @return void
     */
    public static function register_nocache_plugins(Zikula_View &$view)
    {
        // disables the cache for them and do not load them yet
        // that happens later when required
        $delayed_load = true;
        $cacheable    = false;

        /* blocks */
        // clip_accessblock
        Zikula_View_Resource::register($view, 'block', 'clip_accessblock', $delayed_load, $cacheable, array('gid', 'tid', 'pub', 'pid', 'id', 'context', 'tplid', 'permlvl'));

        /* plugins */
        // clip_access
        Zikula_View_Resource::register($view, 'function', 'clip_access', $delayed_load, $cacheable, array('gid', 'tid', 'pub', 'pid', 'id', 'context', 'tplid', 'permlvl', 'assign'));
        // clip_hitcount
        Zikula_View_Resource::register($view, 'function', 'clip_hitcount', $delayed_load, $cacheable, array('pid', 'tid'));
    }

    /**
     * Process of Clip's view for its controllers.
     *
     * @return void
     */
    public static function register_utilities(Zikula_View &$view)
    {
        static $tids, $dirs;

        if (!isset($tids) || !isset($dirs)) {
            $pubtypes = self::getPubType();
            // index the IDs with the urltitle
            $tids = $dirs = array();
            foreach ($pubtypes as $tid => $pubtype) {
                $tids[$pubtype->urltitle] = $tid;
                $dirs[$pubtype->urltitle] = $pubtype->folder;
            }
        }

        // clip pubtype IDs array
        $view->assign('cliptids', $tids)
             ->assign('clipdirs', $dirs);

        // clip_util
        if (!isset($view->_reg_objects['clip_util'])) {
            $clip_util = new Clip_Util_View();
            $view->register_object('clip_util', $clip_util);
        }
    }

    /**
     * Build a public Clip URL.
     *
     * @param mixed          $obj          A pubtype, a publication or a tid.
     * @param string         $func         The specific function to run.
     * @param array          $args         The array of arguments to put on the URL.
     * @param boolean|null   $ssl          Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
     *                                     true - create a ssl url, false - create a non-ssl url.
     * @param string         $fragment     The framgment to target within the URL.
     * @param boolean|null   $fqurl        Fully Qualified URL. True to get full URL, eg for Redirect, else gets root-relative path unless SSL.
     * @param boolean        $forcelongurl Force ModUtil::url to not create a short url even if the system is configured to do so.
     * @param boolean|string $forcelang    Force the inclusion of the $forcelang or default system language in the generated url.
     *
     * @return string
     */
    public static function url($obj, $func, $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang = false)
    {
        if ($obj instanceof Clip_Model_Pubtype) {
            $args['tid'] = $obj['tid'];

        } else if ($obj instanceof Clip_Doctrine_Pubdata) {
            $args['tid'] = $obj['core_tid'];
            if ($func == 'display' || $func == 'edit') {
                if ($obj['core_pid']) {
                    $args['pid'] = $obj['core_pid'];
                    if ($func == 'edit') {
                        $args['id'] = $obj['id'];
                    }
                    $args['urltitle'] = $obj['core_urltitle'];
                } else {
                    $func = 'main';
                }
            }

        } else if (is_numeric($obj)) {
            $args['tid'] = $obj;
        }

        return ModUtil::url('Clip', 'user', $func, $args, $ssl, $fragment, $fqurl, $forcelongurl, $forcelang);
    }

    /**
     * Build a public Clip URL object.
     *
     * @param mixed  $obj      A pubtype, a publication or a tid.
     * @param string $func     The specific function to run.
     * @param array  $args     The array of arguments to put on the URL.
     * @param string $language Force the inclusion of the $forcelang or default system language in the generated url.
     * @param string $fragment The framgment to target within the URL.
     *
     * @return object Clip_Url instance.
     */
    public static function urlobj($obj, $func, $args = array(), $language = null, $fragment = null)
    {
        if ($obj instanceof Clip_Model_Pubtype) {
            $args['tid'] = $obj['tid'];

        } else if ($obj instanceof Clip_Doctrine_Pubdata) {
            $args['tid'] = $obj['core_tid'];
            if ($func == 'display' || $func == 'edit') {
                $args['pid'] = $obj['core_pid'];
                if ($func == 'edit') {
                    $args['id'] = $obj['id'];
                }
                $args['urltitle'] = $obj['core_urltitle'];
            }

        } else if (is_numeric($obj)) {
            $args['tid'] = $obj;
        }

        return new Clip_Url('Clip', 'user', $func, $args, $language, $fragment);
    }
}
