<?php

/**
 * Clip plugin generates forms select for selecting publication type
 *
 * Typical use in template file:
 * <code>
 * {clip_pubtypeselector id="tid"}
 * </code>
 */
class Clip_Form_Plugin_PubTypeSelector extends Zikula_Form_Plugin_DropdownList
{
    public function getFilename()
    {
        return __FILE__;
    }

    function load($view, &$params)
    {
        if (!ModUtil::loadApi('clip', 'admin')) {
            return false;
        }
        $pubtypeslist = ModUtil::apiFunc('clip', 'admin', 'getPublicationTypes');
        $this->addItem('', 0);

        foreach ($pubtypeslist as $pubtype) {
            $this->addItem($pubtype[title], $pubtype[id]);
        }

        parent::load($view, $params);
    }
}
