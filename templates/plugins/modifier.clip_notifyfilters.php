<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://github.com/zikula-modules/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Modifiers
 */

/**
 * Clip modifier to process the filter hooks.
 *
 * Examples:
 *
 *  <samp>{$pubdata.content|clip_notifyfilters:$pubtype}</samp>
 *
 *  <samp>{$pubdata.content|clip_notifyfilters:$cliptids.urltitle}</samp>
 *
 * @param string $content  The content to filter.
 * @param mixed  $pubtype  Pubtype instance or ID.
 * @param string $hooktype Type of hook to notify (default: 'filter').
 * @param string $category Hook category to notify (default: 'filter_hooks').
 * @param string $subarea  Clip subarea to notify (default: '').
 *
 * @return string CSS class or parameter result.
 */
function smarty_modifier_clip_notifyfilters($content, $pubtype, $hooktype = 'filter', $category = 'filter_hooks', $subarea = '')
{
    // NOTE: notifyfilters core modifier is added on Clip_Util::register_utilities

    if (!is_object($pubtype)) {
        $pubtype = Clip_Util::getPubType($pubtype);
    }

    $eventName = $pubtype->getHooksEventName($hooktype, $category, $subarea);

    return smarty_modifier_notifyfilters($content, $eventName);
}
