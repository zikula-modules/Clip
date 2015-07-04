<?php

class PubListener extends Zikula_AbstractEventHandler implements Doctrine_Overloadable
{
    /**
     * Setup handler definitions.
     *
     * @return void
     */
    protected function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('module.clip.pub.listeners', 'register');
    }

    /**
     * Register this listener.
     *
     * @param Zikula_Event $event
     */
    public function register(Zikula_Event $event)
    {
        /** @var ArrayObject $data */
        $data = $event->getData();
        // listen all the pubtypes
        $data['-'][] = $this;
        // listen a specific pubtype
        // $data[8][] = $this; // pubtype with tid = 8
        // $data['blog'][] = $this; // pubtype with urltitle = blog
    }

    /**
     * Method overloader
     *
     * @see Doctrine_Record_Listener
     *
     * @param  string $method the name of the method
     * @param  array $args method arguments
     * @return mixed          return value of the method
     */
    public function __call($method, $args)
    {
        /**
         * You can build one method for each event and leave empty this generic handler.
         */
        $supportedEvents = array(
            'preSerialize', 'postSerialize', 'preUnserialize', 'postUnserialize', 'preDqlSelect', 'preSave',
            'postSave', 'preDqlDelete', 'preDelete', 'postDelete', 'preDqlUpdate', 'preUpdate', 'postUpdate',
            'preInsert', 'postInsert', 'preValidate', 'postValidate'
        );
        // preHydrate and postHydrate are invoked by Doctrine_Tables not pub Records

        if (!in_array($method, $supportedEvents)) return;

        /** @var Doctrine_Event $event */
        $event = $args[0];
        /** @var Clip_Doctrine_Pubdata $pub */
        $pub = $event->getInvoker();

        switch ($method)
        {
            case 'preSerialize':
                break;

            case 'postSerialize':
                break;

            case 'preUnserialize':
                break;

            case 'postUnserialize':
                break;

            case 'preDqlSelect':
                break;

            case 'preSave':
                break;

            case 'postSave':
                break;

            case 'preDqlDelete':
                break;

            case 'preDelete':
                break;

            case 'postDelete':
                break;

            case 'preDqlUpdate':
                break;

            case 'preUpdate':
                break;

            case 'postUpdate':
                break;

            case 'preInsert':
                break;

            case 'postInsert':
                break;

            case 'preValidate':
                break;

            case 'postValidate':
                break;
        }
    }
}
