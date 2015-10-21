<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form
 */

namespace Matheo\Clip\Form;

use Matheo\Clip\Util;

class PubtypeForm extends \Zikula_Form_Plugin_DropdownList
{
    public function getFilename()
    {
        return __FILE__;
    }
    
    public function load(Zikula_Form_View $view, &$params)
    {
        $this->addItem('', 0);
        $pubtypes = Util::getPubType();
        foreach ($pubtypes as $pubtype) {
            $this->addItem($pubtype['title'], $pubtype['tid']);
        }
        parent::load($view, $params);
    }

}
