
{include file='clip_generic_navbar.tpl'}

{if $pubdata.id}
    {gt text='Edit a page' assign='pagetitle'}
{else}
    {gt text='New page' assign='pagetitle'}
{/if}
{if !$homepage}{pagesetvar name="title" value="`$pagetitle` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

<h2>{$pagetitle}</h2>

{assign var='zformclass' value="z-form clip-editform clip-editform-`$pubtype.tid` clip-editform-`$pubtype.tid`-`$clipargs.edit.state`"}

{form cssClass=$zformclass enctype='multipart/form-data'}
    <div>
        {formvalidationsummary}
        <fieldset class="z-linear">
            <legend>{gt text='Content'}</legend>

            <div class="z-formrow">
                {formlabel for='title' __text='Title' mandatorysym=true}
                {clip_form_genericplugin id='title' group='pubdata'}
            </div>

            <div class="z-formrow">
                {formlabel for='category' __text='Category'}
                {clip_form_genericplugin id='category' group='pubdata'}
            </div>

            <div class="z-formrow">
                {formlabel for='core_language' __text='Language'}
                {formlanguageselector id='core_language' group='pubdata' mandatory=false}
            </div>

            <div class="z-formrow">
                {formlabel for='content' __text='Content'}
                {clip_form_genericplugin id='content' maxLength='65535' rows='25' cols='70' group='pubdata'}
            </div>
        </fieldset>

        <fieldset>
            <legend>
                <a id="clip_pagesettings_collapse" href="javascript:void(0);">{gt text='Page settings'}</a>
            </legend>

            <div id="clip_page_settings">
                <div class="z-formrow">
                    {formlabel for='displayinfo' __text='Display page information'}
                    {clip_form_genericplugin id='displayinfo' group='pubdata'}
                </div>

                <div class="z-formrow">
                    {formlabel for='core_showinlist' __text='Show in list'}
                    {formcheckbox id='core_showinlist' group='pubdata' checked='checked'}
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
            <legend>{gt text='Related publications'}</legend>

            {foreach from=$relations key='alias' item='item' name='relations'}
            <div class="z-formrow">
                {formlabel for=$alias text=$item.title}
                {clip_form_relation id=$alias relation=$item minchars=2 op='likefirst' group='pubdata'}
            </div>
            {/foreach}

        </fieldset>
        {/if}

        {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.form_edit" id=$pubobj.core_uniqueid}

        <div class="z-buttons z-formbuttons">
            {foreach item='action' from=$actions}
                {formbutton commandName=$action.id text=$action.title zparameters=$action.parameters.button|default:''}
            {/foreach}
            <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
{/form}

{capture assign='clippagejs'}
<script type="text/javascript">
    //<![CDATA[
    Event.observe(window, 'load', clip_page_init, false)

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
{/capture}
{ajaxheader}
{pageaddvar name='header' value=$clippagejs}
