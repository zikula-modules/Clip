
<div class="clip-edit clip-edit-{$pubtype.urltitle} clip-edit-{$pubtype.urltitle}-{$clipargs.edit.state}">
    {include file='generic_navbar.tpl'}

    <h2>
        {if $pubdata.id}
            {gt text='Edit page' assign='pagetitle'}
        {else}
            {gt text='New page' assign='pagetitle'}
        {/if}
    </h2>

    {form cssClass='z-form' enctype='multipart/form-data'}
        <div>
            {formvalidationsummary}
            <fieldset class="z-linear">
                <legend>{gt text='Content'}</legend>

                <div class="z-formrow">
                    {formlabel for='title' __text='Title' mandatorysym=true}
                    {clip_form_plugin field='title' group='pubdata'}
                </div>

                <div class="z-formrow">
                    {formlabel for='category' __text='Category'}
                    {clip_form_plugin field='category' group='pubdata'}
                </div>

                <div class="z-formrow">
                    {formlabel for='core_language' __text='Language'}
                    {formlanguageselector id='core_language' group='pubdata' mandatory=false}
                </div>

                <div class="z-formrow">
                    {formlabel for='content' __text='Content'}
                    {clip_form_plugin field='content' maxLength='65535' rows='25' cols='70' group='pubdata'}
                </div>
            </fieldset>

            <fieldset>
                <legend>
                    <a id="clip_pagesettings_collapse" href="javascript:void(0);">{gt text='Page settings'}</a>
                </legend>

                <div id="clip_page_settings">
                    <div class="z-formrow">
                        {formlabel for='displayinfo' __text='Display page information'}
                        {clip_form_plugin field='displayinfo' group='pubdata'}
                    </div>

                    <div class="z-formrow">
                        {formlabel for='core_visible' __text='Visible'}
                        {formcheckbox id='core_visible' group='pubdata' checked='checked'}
                        <em class="z-formnote z-sub">{gt text='If not visible, will be excluded from lists and search results'}</em>
                    </div>

                    <div class="z-formrow">
                        {formlabel for='core_locked' __text='Locked'}
                        {formcheckbox id='core_locked' group='pubdata' checked='checked'}
                        <em class="z-formnote z-sub">{gt text='If enabled, the publication will be closed for changes'}</em>
                    </div>

                    <div class="z-formrow">
                        {formlabel for='core_publishdate' __text='Publish date'}
                        {formdateinput id='core_publishdate' group='pubdata' includeTime=true}
                        <em class="z-formnote z-sub">{gt text='leave blank if you do not want to schedule the publication'}</em>
                    </div>

                    <div class="z-formrow">
                        {formlabel for='core_expiredate' __text='Expire date'}
                        {formdateinput id='core_expiredate' group='pubdata' includeTime=true}
                        <em class="z-formnote z-sub">{gt text='leave blank if you do not want the plublication expires'}</em>
                    </div>
                </div>
            </fieldset>

            {if $relations}
            <fieldset>
                <legend>{gt text='Related pages'}</legend>

                {foreach from=$relations key='alias' item='item' name='relations'}
                <div class="z-formrow">
                    {formlabel for=$alias text=$item.title}
                    {clip_form_relation alias=$alias relation=$item minchars=2 op='likefirst'}
                </div>
                {/foreach}

            </fieldset>
            {/if}

            <div class="clip-hooks-edit">
                {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.form_edit" id=$pubdata.core_uniqueid}
            </div>

            <div class="z-buttons z-formbuttons">
                {foreach item='action' from=$actions}
                    {formbutton commandName=$action.id text=$action.title zparameters=$action.parameters.button|default:''}
                {/foreach}
                <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
                {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
            </div>
        </div>
    {/form}
</div>

{ajaxheader}
{pageaddvarblock name='header'}
<script type="text/javascript">
//<![CDATA[
    document.observe('dom:loaded', clip_page_init, false)

    function clip_page_init()
    {
        $('clip_pagesettings_collapse').observe('click', clip_page_click);
        $('clip_pagesettings_collapse').addClassName('z-toggle-link');
        clip_page_click();
    }

    function clip_page_click()
    {
        if ($('clip_page_settings').style.display != 'none') {
            Element.removeClassName.delay(0.9, $('clip_pagesettings_collapse'), 'z-toggle-link-open');
        } else {
            $('clip_pagesettings_collapse').addClassName('z-toggle-link-open');
        }
        switchdisplaystate('clip_page_settings');
    }
// ]]>
</script>
{/pageaddvarblock}
