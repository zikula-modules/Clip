<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_Plugin_Content
 */

/**
 * Plugin used to support pubfields list on Clip Content Types.
 */
class Clip_Form_Plugin_Content_Pubfields extends Zikula_Form_Plugin_DropdownList
{
    public function getFilename()
    {
        return __FILE__;
    }

    function load(Zikula_Form_View $view, &$params)
    {
        if (!$view->isPostBack()) {
            $data = $view->getTplVar('data');
            $tid = isset($data['tid']) && $data['tid'] ? $data['tid'] : null;
            $this->setItems(Clip_Util_Selectors::fields($tid));
        }

        parent::load($view, $params);
    }
}
