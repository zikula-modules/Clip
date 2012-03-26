<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin
 */

/**
 * Plugin used to manage a relation with a text input.
 */
class Clip_Form_Plugin_Relations_Text extends Zikula_Form_Plugin_TextInput
{
    // custom plugin vars
    public $relation;
    public $reldata;
    public $delimiter = ',';

    // Clip data handling
    public $alias;
    public $tid;
    public $rid;
    public $pid;
    public $field;

    /**
     * Get filename for this plugin.
     *
     * @internal
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create($view, &$params)
    {
        if ($this->maxLength == null && !in_array(strtolower($this->textMode), array('multiline', 'hidden'))) {
            $params['maxLength'] = 65535;
        }

        parent::create($view, $params);
    }

    /**
     * Data loading.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     */
    public function load($view, &$params)
    {
        if (!$this->relation) {
            $this->relation = Clip_Util::getPubType($this->tid)->getRelation($this->field);
        }

        $this->reldata = array();

        parent::load($view, $params);
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_ViewetValues.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $data = $values[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field];

            // assign existing data
            if ($data) {
                if ($this->relation['single']) {
                    $this->reldata[$data['id']] = $data['core_title'];
                } else {
                    foreach ($data as $rec) {
                        $this->reldata[$rec['id']] = $rec['core_title'];
                    }
                }
            }

            $ids = array_keys($this->reldata);

            // check if the main pub received relations on get parameters
            if ($this->alias == 'clipmain') {
                $pubdata = $view->getTplVar('pubdata');
                $relflds = $pubdata->getRelationFields();

                if (isset($relflds[$this->field]) && $pubdata->clipModified($relflds[$this->field])) {
                    $old = $pubdata->clipOldValues($relflds[$this->field]);
                    $ids = !is_null($old) ? array($old) : array();
                }
            }

            // update the input value
            if (!$this->text) {
                $this->text = implode($this->delimiter, $ids);
            }

            // save the data in the state session
            $links = $view->getStateData('links');
            $links[$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $ids;
            $view->setStateData('links', $links);
        }
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $view->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view  Reference to Zikula_Form_View object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    public function saveValue($view, &$data)
    {
        if ($this->dataBased) {
            $ids = $this->relation['single'] ? array($this->text) : explode($this->delimiter, $this->text);

            if (!array_key_exists($this->group, $data)) {
                $data[$this->group] = array($this->alias => array($this->tid => array($this->rid => array($this->pid => array()))));
            }
            $data[$this->group][$this->alias][$this->tid][$this->rid][$this->pid][$this->field] = $ids;
        }
    }
}
