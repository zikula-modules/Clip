
  0.9.2
    Some usability and sexy code improvements
    Added support for common templates, like common_list.tpl when pubtype/list.tpldoesn't exists
    Improved set_$field parameters from URL for edit forms
    Moved all the internal URLs to use the clip_url plugin
    userapi.getall method extended to perform distinct and max/min/sum/count operations
    Hooks handling fixed
    Content module integration included
    Thumbnail module no longer required, now using the Imagine library bundled in the core
    PageMaster upgrade path addressed

  0.9.1
    Multilingual Javascript fixed with Zikula 1.3.2-dev
    Enable shortURLs to support the $clipvalues
    BigInt pubfield plugin added to support large numeric values
    Added the clip_capture plugin to assign postfiltered sections like clip_func
    Permission scheme improvements
    Category pubfields now resolves the localized description on $field.fullDesc as does with fullTitle

  0.9.0
    Event notifications on strategic points included
    Added support to $clipvalues from the URL to the template
    Form handling rework to support the edition of multiple publications on the same form
    Added template utilities like the clip_util and clip_form objects, and the clip_include and clip_func plugins
    Introduced a new Editor Panel to separate the content administration
    Deprecated editlist*
    Extended workflow support
    Blocks refactored and reworked. Template names changed.
    User methods renamed from main/viewpub/pubedit to view/display/edit
    User Api methods renamed from pubList/getPub/editPub to getall/get/edit
    Template paths changed to a folder per pubtype, and with filenames: list.tpl, display.tpl and form_all.tpl
    Rework of the available variables in the list and display templates.
        list:
            $tid => $pubtype.tid
            $core_titlefield => $pubdata.core_titlefield
        display:
            Publication data embeded in the $pubdata object
            $core_titlefield => $pubdata.core_titlefield
            $core_tid => $pubdata.core_tid or $pubtype.tid
            $core_approvalstate => $pubdata.core_approvalstate
            $core_uniqueid => $pubdata.core_uniqueid
            $core_creator => $pubdata.core_creator
            $core_approvalstate => $pubdata.core_approvalstate
    Support of styled buttons through worfkflow action parameters
    API arguments renamed, a massive rename on your custom templates is needed for Clip 1.0:
        checkPerm               checkperm
        handlePluginFields      handleplugins
        getApprovalState        loadworkflow
    Plugins were renamed to have the clip_ prefix:
        pmadminsubmenu          clip_admin_submenu
        pmarray                 clip_dump
        pmformplugintype        clip_form_plugintype
        category_browser        clip_category_browser
        genericformplugin       clip_form_plugin
        get_workflow_state      clip_getstate
        hitcount                clip_hitcount
        multilistdecode         clip_multilistdecode
