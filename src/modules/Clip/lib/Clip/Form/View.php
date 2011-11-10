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

/**
 * Clip Fork to handle the postRender event.
 */
class Clip_Form_View extends Zikula_Form_View
{
    protected $response;

    /**
     * Main event loop handler.
     *
     * This is the function to call instead of the normal $view->fetch(...).
     *
     * @param boolean                     $template     Name of template file.
     * @param Zikula_Form_AbstractHandler $eventHandler Instance of object that inherits from Zikula_Form_AbstractHandler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function execute($template, Zikula_Form_AbstractHandler $eventHandler)
    {
        if (!$eventHandler instanceof Zikula_Form_AbstractHandler) {
            throw new Zikula_Exception_Fatal('Form handlers must inherit from Zikula_Form_AbstractHandler.');
        }

        // Save handler for later use
        $this->eventHandler = $eventHandler;
        $this->eventHandler->setView($this);
        $this->eventHandler->setEntityManager($this->entityManager);
        $this->eventHandler->setRequest($this->request);
        $this->eventHandler->setDomain($this->domain);
        $this->eventHandler->setName($this->getModuleName());
        $this->eventHandler->setup();
        $this->eventHandler->preInitialize();

        if ($this->isPostBack()) {
            if (!SecurityUtil::validateCsrfToken($this->request->getPost()->filter('csrftoken', '', FILTER_SANITIZE_STRING), $this->serviceManager)) {
                return LogUtil::registerAuthidError();
            }

            // retrieve form id
            $formId = $this->request->getPost()->filter("__formid", '', FILTER_SANITIZE_STRING);
            $this->setFormId($formId);

            $this->decodeIncludes();
            $this->decodeStateData();
            $this->decodeState();

            if ($eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }

            // if we get this far, the form processed correctly and we can GC the session
            unset($_SESSION['__formid'][$this->formId]);

            $this->eventHandler->postInitialize();

            // (no create event)
            $this->initializePlugins(); // initialize event
            $this->decodePlugins(); // decode event
            $this->decodePostBackEvent(); // Execute optional postback after plugins have read their values

            // check if there's an ajax response
            if ($this->response instanceof Zikula_Response_Ajax_AbstractBase) {
                return $this->response;
            }

            // redirect if handleCommand was executed and redirected
            if ($this->redirected) {
                return true;
            }
        } else {
            $this->setFormId(uniqid('f'));
            if ($eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }
            $this->eventHandler->postInitialize();
        }

        // render event (calls registerPlugin)
        $this->assign('__formid', $this->formId);
        $output = $this->fetch($template);

        if ($this->hasError()) {
            return $this->getErrorMsg();
        }

        // Check redirection at this point, ignore any generated HTML if redirected is required.
        // We cannot skip HTML generation entirely in case of System::redirect since there might be
        // some relevant code to execute in the plugins.
        if ($this->redirected) {
            return true;
        }

        return $output;
    }

    /**
     * Raise event in the main user event handler.
     *
     * This method raises an event in the main user event handler.
     * It is usually called from a plugin to signal that something in that
     * plugin needs attention.
     *
     * @param string $eventHandlerName The event handler method name.
     * @param mixed  $args             The event arguments.
     *
     * @return boolean
     */
    public function raiseEvent($eventHandlerName, $args)
    {
        $handlerClass = & $this->eventHandler;

        if (method_exists($handlerClass, $eventHandlerName)) {
            $this->response = $handlerClass->$eventHandlerName($this, $args);
            if ($this->response === false) {
                return false;
            }
        }

        return true;
    }

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

    /**
     * Trigger error.
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        throw new Exception($error_msg);
    }

    /**
     * Read all values from the form.
     *
     * @return mixed
     */
    public function getValues()
    {
        static $result = array();

        if (empty($result)) {
            $this->getValues_rec($this->plugins, $result);
        }

        return $result;
    }
}
