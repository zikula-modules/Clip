<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage View_Plugins
 */

/**
 * Returns a download url to a specified field.
 *
 * Available parameters:
 *  - pub   (object)  Publication instance to get all the IDs (default: current $pubdata).
 *  - field (string)  Upload field to download.
 *  - count (string)  Counter field to increment.
 *
 * Examples:
 *
 *  <samp>{clip_downloadlink field='fileupload'}</samp>
 *
 *  <samp>{clip_downloadlink field='imageupload' count='hits'}</samp>
 *
 * @param array       $params All parameters passed to this plugin from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string Download URL.
 */
function smarty_function_clip_downloadurl($params, Zikula_View &$view)
{
    if ((!isset($params['field']) || !$params['field']) && (!isset($params['count']) || !$params['count'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter must be specified.', array('clip_downloadurl', 'field | count')));
        return false;
    }

    if (!isset($params['pub'])) {
        $params['pub'] = $view->getTplVar('pubdata');
    }

    if (!$params['pub'] instanceof Clip_Doctrine_Pubdata && !is_array($params['pub'])) {
        $view->trigger_error($view->__f('Error! in %1$s: the %2$s parameter is not valid.', array('clip_downloadurl', 'pub')));
        return false;
    }

    // process the parameters
    if (!isset($params['tid']) && !isset($params['pid'])) {
        $params['tid'] = $params['pub']['core_tid'];
        $params['pid'] = $params['pub']['core_pid'];
    }

    $assign = isset($params['assign']) ? $params['assign'] : null;

    // build the URL
    $url = System::getBaseUrl()."ajax.php?module=Clip&func=count&tid={$params['tid']}&pid={$params['pid']}";

    if ($params['field']) {
        $url .= "&field={$params['field']}";
    }

    if ($params['count']) {
        $url .= "&count={$params['count']}";
    }

    $url .= '&csrftoken='.SecurityUtil::generateCsrfToken();

    if ($assign) {
        $view->assign($assign, $url);
    } else {
        return DataUtil::formatForDisplay($url);
    }
}
