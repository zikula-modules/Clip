<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Form_View
 */

/**
 * Clip Fork to handle the postRender event.
 */
class Clip_Form_View extends Zikula_Form_View
{
    /**
     * Post render event.
     *
     * @return boolean
     */
    public function postRender()
    {
        if (ModUtil::available('Scribite')) {
            // looks for Text plugins with Scribite enabled
            $editor = '';
            $ids = array();
            foreach ($this->plugins as $plugin) {
                if ($plugin instanceof Clip_Form_Plugin_Text) {
                    if ($plugin->config['usescribite']) {
                        $ids[] = $plugin->getId();
                        if ($plugin->config['editor'] != '-') {
                            $editor = $plugin->config['editor'];
                        }
                    }
                }
            }
            // calls scribite if there are textareas enabled
            if (!empty($ids)) {
                $args = array(
                    'modulename' => 'Clip',
                    'editor'     => $editor,
                    'areas'      => $ids
                );
                $scribite = ModUtil::apiFunc('Scribite', 'user', 'loader', $args);

                // add the scripts to page header
                if ($scribite) {
                    PageUtil::AddVar('header', $scribite);
                }
            }
        }

        $this->postRender_rec($this->plugins);

        return true;
    }
}
