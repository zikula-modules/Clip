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

namespace Clip\Filter\Plugin;


/**
 * Clip base filter plugin class.
 *
 * Its methods are:
 *
 * - <b>create</b>: Similar to a constructor since it is called directly after the plugin has been created.
 *   In this event handler you should set the various member variables your plugin requires. You can access
 *   Smarty parameters through the $params object. The automatic setting of member variables from Smarty
 *   parameters happens <i>before</i> the create event.
 *
 * - <b>load</b>: Called immediately after the create event. So the plugin is assumed to be fully initialized when the load event
 *   is fired. During the load event the plugin is expected to load values from the render object.
 *
 *   A typical load event handler will just call the loadValue
 *   handler and pass it the values of the render object (to improve reuse). The loadValue method will then take care of the rest.
 *   Example:
 *   <code>
 *   function load(Zikula_Form_View $view, &$params)
 *   {
 *     $this->loadValue($view, $view->get_template_vars());
 *   }
 *   </code>
 *
 * - <b>render</b>: this event is fired when the plugin is required to render itself based on the data
 *   it got through the previous events. This function is only called on Smarty function plugins.
 *   The event handler is supposed to return the rendered output.
 */
abstract class AbstractPlugin implements \Zikula_TranslatableInterface
{
    /**
     * Plugin identifier.
     *
     * This contains the identifier for the plugin.
     * Do <i>not</i> change this variable!
     *
     * @var string
     */
    public $id;
    /**
     * Field identifier.
     *
     * This contains the field identifier for the plugin.
     *
     * @var string
     */
    public $field;
    /**
     * Operator to use.
     *
     * This contains the operator to use in the filter.
     *
     * @var string
     */
    public $op;
    /**
     * HTML attributes.
     *
     * Associative array of attributes to add to the plugin. For instance:
     * array('title' => 'A tooltip title', onclick => 'doSomething()')
     *
     * @var array
     */
    public $attributes = array();
    /**
     * Styles added programatically.
     *
     * @var array
     */
    public $styleAttributes = array();
    /**
     * Translation domain.
     *
     * @var string
     */
    protected $domain;
    /**
     * Constructor.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     */
    public function __construct($params, $filter)
    {
        $this->readParameters($params);
        $this->create($params, $filter);
        $this->load($params, $filter);
    }
    
    /**
     * Retrieve the plugin identifier (see {@link $id}).
     *
     * @return string The id.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Retrieve the field identifier (see {@link $field}).
     *
     * @return string The field.
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * Retrieve the HTML attributes.
     *
     * @return array An associative array of attributes to add to the plugin.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Retrieve the styles added programatically.
     *
     * @return array The styles.
     */
    public function getStyleAttributes()
    {
        return $this->styleAttributes;
    }
    
    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * Set translation domain.
     *
     * @param string $domain The translation domain.
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
    
    /**
     * Read Smarty plugin parameters.
     *
     * This is the function that takes care of reading smarty parameters and storing them in the member variables
     * or attributes (all unknown parameters go into the "attributes" array).
     * You can override this for special situations.
     *
     * @param array $params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function readParameters($params)
    {
        $varInfo = get_class_vars(get_class($this));
        // adds the zparameters to the $params if exists
        if (array_key_exists('zparameters', $params)) {
            if (is_array($params['zparameters'])) {
                $params = array_merge($params['zparameters'], $params);
            }
            unset($params['zparameters']);
        }
        // Iterate through all params: place known params in member variables and the rest in the attributes set
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $varInfo)) {
                $this->{$name} = $value;
            } else {
                $this->attributes[$name] = $value;
            }
        }
    }
    
    /**
     * Create event handler.
     *
     * This fires once, immediately <i>after</i> member variables have been populated from Smarty parameters
     * (in {@link readParameters()}). Default action is to do nothing.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function create($params, $filter)
    {
        
    }
    
    /**
     * Load event handler.
     *
     * This fires once, immediately <i>after</i> the create event. Default action is to do nothing.
     *
     * @param array            $params Parameters passed from the Smarty plugin function.
     * @param Clip_Filter_Form $filter Clip filter form manager instance.
     *
     * @return void
     */
    public function load($params, $filter)
    {
        
    }
    
    /**
     * Utility function to generate HTML for ID attribute.
     *
     * Generate id="..." for use in the plugin's render methods.
     *
     * This function ignores automatically created IDs (those named "plgNNN") and will
     * return an empty string for these.
     *
     * @param string $id The ID of the item.
     *
     * @return string The generated HTML.
     */
    public function getIdHtml($id = null)
    {
        if ($id == null) {
            $id = $this->id;
        }
        if (preg_match('/^plg[0-9]+$/', $id)) {
            return '';
        }
        return " id=\"{$id}\"";
    }
    
    /**
     * RenderAttributes event handler.
     *
     * Default action is to do render all attributes in form name="value".
     *
     * @return string The rendered output.
     */
    public function renderAttributes()
    {
        static $styleElements = array('width', 'height', 'color', 'background_color', 'border', 'padding', 'margin', 'float', 'display', 'position', 'visibility', 'overflow', 'clip', 'font', 'font_family', 'font_style', 'font_weight', 'font_size');
        $attr = '';
        $style = '';
        foreach ($this->attributes as $name => $value) {
            if ($name == 'style') {
                $style = $value;
            } elseif (in_array($name, $styleElements)) {
                $this->styleAttributes[$name] = $value;
            } else {
                $attr .= " {$name}=\"{$value}\"";
            }
        }
        $style = trim($style);
        if (count($this->styleAttributes) > 0 && strlen($style) > 0 && $style[strlen($style) - 1] != ';') {
            $style .= ';';
        }
        foreach ($this->styleAttributes as $name => $value) {
            $style .= str_replace('_', '-', $name) . ":{$value};";
        }
        if (!empty($style)) {
            $attr .= " style=\"{$style}\"";
        }
        return $attr;
    }
    
    /**
     * Render event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Zikula_View $view Reference to Zikula_View object.
     *
     * @return string The rendered output.
     */
    public function render(Zikula_View $view)
    {
        return '';
    }
    
    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }
    
    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }
    
    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    public function _n(
        $singular,
        $plural,
        $count
    ) {
        return _n($singular, $plural, $count, $this->domain);
    }
    
    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string
     */
    public function _fn(
        $sin,
        $plu,
        $n,
        $params
    ) {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

}