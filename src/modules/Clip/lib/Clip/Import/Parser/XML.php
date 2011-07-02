<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Import_Parser
 */

/**
 * Import XML Parser class.
 */
class Clip_Import_Parser_XML
{
    const SKIP = '__DISCARDEDNODE__';

    protected $reader = null;

    /**
     * Constructor.
     */
    public function __construct($file)
    {
        if (file_exists($file)) {
            $this->reader = new XMLReader();
            $this->reader->open($file);
            $this->reader->read();
        }
    }

    /**
     * Destructor.
     */
    public function  __destruct()
    {
        if ($this->reader) {
            $this->reader->close();
        }
    }

    /**
     * Begin a section.
     *
     * @return string Name of the section being processed
     */
    public function parseSections($callback)
    {
        $dom = ZLanguage::getModuleDomain('Clip');

        $sections = array('pubtypes', 'pubfields', 'pubdata', 'workflows');
        $elements = array('pubtype', 'pubfield', 'pub', 'workflow');

        $section = '';
        while ($this->reader->read())
        {
            if ($this->reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE || $this->reader->nodeType == XMLReader::WHITESPACE) {
                continue;
            }

            if (!$section && $this->reader->nodeType == 1) {
                // detect the start of a section
                if (!in_array(Clip_Util::getStringPrefix($this->reader->name), $sections)) {
                    return LogUtil::registerError(__f('Unexpected section found [%s].', DataUtil::formatForDisplay($this->reader->name), $dom));
                }
                $section = $this->reader->name;
                continue;

            } elseif ($this->reader->nodeType == 15) {
                $section = '';
                continue;
            }

            // expected node is a single element
            if (!in_array($this->reader->name, $elements)) {
                return LogUtil::registerError(__f('Unexpected element found [%s].', DataUtil::formatForDisplay($this->reader->name), $dom));
            }

            $result = $this->reader->expand();
            $result = self::DOMtoArray($result);
            $result['section'] = $section;

            call_user_func($callback, $result);
            $this->reader->next();
        }

        return true;
    }

    /**
     * DOMNode2Array conversor.
     *
     * @param DOMNode $node DOMNode to convert.
     *
     * @return array
     */
    public static function DOMtoArray(DOMNode $DomNode = null)
    {
        $array = array();

        if (!$DomNode) {
            return $array;
        }

        if (!$DomNode->hasChildNodes()) {
            $array[$DomNode->nodeName] = $DomNode->nodeValue;
        } else {
            foreach ($DomNode->childNodes as $oChildNode) {
                // how many of these child nodes do we have?
                $oChildNodeList = $DomNode->getElementsByTagName($oChildNode->nodeName); // count = 0
                $iChildCount = 0;
                // there are x number of childs in this node that have the same tag name
                // however, we are only interested in the # of siblings with the same tag name
                foreach ($oChildNodeList as $oNode) {
                    if ($oNode->parentNode->isSameNode($oChildNode->parentNode)) {
                        $iChildCount++;
                    }
                }

                $mValue = self::DOMtoArray($oChildNode);

                if ($mValue != Clip_Import_Parser_XML::SKIP) {
                    $mValue = is_array($mValue) ? $mValue[$oChildNode->nodeName] : $mValue;
                    $sKey = ($oChildNode->nodeName{0} == '#') ? 0 : $oChildNode->nodeName;
                    // this will give us a clue as to what the result structure should be
                    // how many of thse child nodes do we have?
                    if ($iChildCount == 1) { // only one child – make associative array
                        $array[$sKey] = $mValue;
                    } elseif ($iChildCount > 1) { // more than one child like this – make numeric array
                        $array[$sKey][] = $mValue;
                    } elseif ($iChildCount == 0) { // no child records found, this is DOMText or DOMCDataSection
                        $array[$sKey] = $mValue;
                    }
                }
            }
            // if the child is bar, the result will be array(bar)
            // make the result just ‘bar’
            if (count($array) == 1 && isset($array[0]) && !is_array($array[0])) {
                $array = $array[0];
            }

            $array = array($DomNode->nodeName => $array);
        }

        // get our attributes if we have any
        if ($DomNode->hasAttributes()) {
            foreach ($DomNode->attributes as $sAttrName => $oAttrNode) {
                // retain namespace prefixes
                $array["@{$oAttrNode->nodeName}"] = $oAttrNode->nodeValue;
            }
        }

        // manual discard of Clip large spacings
        if (isset($array['#text']) && ($array['#text'] === "\n  " || $array['#text'] === "\n   ")) {
            $array = Clip_Import_Parser_XML::SKIP;
        }

        return $array;
    }
}
