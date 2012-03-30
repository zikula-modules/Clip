
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
                    {clip_form_label for='title' __text='Title' mandatorysym=true}
                    {clip_form_plugin field='title'}
                </div>

                <div class="z-formrow">
                    {clip_form_label for='category' __text='Category'}
                    {clip_form_plugin field='category'}
                </div>

                <div class="z-formrow">
                    {clip_form_label for='core_language' __text='Language'}
                    {clip_form_plugin field='core_language' mandatory=false}
                </div>

                <div class="z-formrow">
                    {clip_form_label for='content' __text='Content'}
                    {clip_form_plugin field='content' maxLength='65535' rows='25' cols='70'}
                </div>
            </fieldset>

            {clip_accessblock context='editor'}
            <fieldset>
                <legend>
                    <a id="clip_pagesettings_collapse" href="javascript:void(0);">{gt text='Page settings'}</a>
                </legend>

                <div id="clip_page_settings">
                    <div class="z-formrow">
                        {clip_form_label for='displayinfo' __text='Display page information'}
                        {clip_form_plugin field='displayinfo'}
                    </div>

                    <div class="z-formrow">
                        {clip_form_label for='core_urltitle' __text='Permalink title'}
                        {clip_form_plugin field='core_urltitle' mandatory=false}
                        <em class="z-formnote z-sub">{gt text='Leave blank to autogenerate'}</em>
                    </div>

                    <div class="z-formrow">
                        {clip_form_label for='core_publishdate' __text='Publish date'}
                        {clip_form_plugin field='core_publishdate' includeTime=true}
                        <em class="z-formnote z-sub">{gt text='Leave blank if you do not want to schedule the publication'}</em>
                    </div>

                    <div class="z-formrow">
                        {clip_form_label for='core_expiredate' __text='Expire date'}
                        {clip_form_plugin field='core_expiredate' includeTime=true}
                        <em class="z-formnote z-sub">{gt text='Leave blank if you do not want the plublication expires'}</em>
                    </div>

                    <div class="z-formrow">
                        {clip_form_label for='core_visible' __text='Visible'}
                        {clip_form_plugin field='core_visible'}
                        <em class="z-formnote z-sub">{gt text='If not visible, will be excluded from lists and search results'}</em>
                    </div>

                    <div class="z-formrow">
                        {clip_form_label for='core_locked' __text='Locked'}
                        {clip_form_plugin field='core_locked'}
                        <em class="z-formnote z-sub">{gt text='If enabled, the publication will be closed for changes'}</em>
                    </div>
                </div>
            </fieldset>

            {if $relations}
            <fieldset>
                <legend>{gt text='Related pages'}</legend>

                {foreach from=$relations key='field' item='item' name='relations'}
                <div class="z-formrow">
                    {clip_form_label for=$field text=$item.title|clip_translate}
                    {clip_form_relation field=$field relation=$item minchars=2 op='likefirst'}
                </div>
                {/foreach}

            </fieldset>
            {/if}
            {/clip_accessblock}

            <div class="clip-hooks-edit">
                {notifydisplayhooks eventname=$pubtype->getHooksEventName('form_edit') urlObject=$pubdata->clipUrl() id=$pubdata.core_pid}
            </div>

            <div class="z-buttons">
                {foreach item='action' from=$actions}
                    {formbutton commandName=$action.id text=$action.title zparameters=$action.parameters.button|default:''}
                {/foreach}
                <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
                {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
            </div>
        </div>
    {/form}
</div>

{clip_accessblock context='editor'}
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
{/clip_accessblock}
