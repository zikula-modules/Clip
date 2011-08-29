<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form
 */

class Clip_Form_Pubtype extends Zikula_Form_Plugin_DropdownList
{
    public function getFilename()
    {
        return __FILE__;
    }

    public function load(Zikula_Form_View $view, &$params)
    {
        $this->addItem('', 0);

        $pubtypes = Clip_Util::getPubType();

        foreach ($pubtypes as $pubtype) {
            $this->addItem($pubtype['title'], $pubtype['tid']);
        }

        parent::load($view, $params);
    }
}
