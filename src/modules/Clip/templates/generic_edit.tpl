
<div class="clip-wrapper clip-edit clip-edit-{$pubtype.urltitle} clip-edit-{$pubtype.urltitle}-{$clipargs.edit.state}">
    {include file='generic_navbar.tpl'}

    <h2>
        {if $pubdata.id}
            {gt text="Edit '%s'" tag1=$pubdata.core_title|truncate:50}
        {else}
            {gt text='Submit a publication'}
        {/if}
    </h2>

    {form cssClass='z-form' enctype='multipart/form-data'}
        <div>
            {formvalidationsummary}

            <fieldset class="z-linear">
                <legend>{gt text='Publication content'}</legend>
{$code}
            </fieldset>

            {clip_accessblock context='editor'}
            {if $relations}
            <fieldset>
                <legend>{gt text='Related publications'}</legend>

                {foreach from=$relations key='field' item='item' name='relations'}
                <div class="z-formrow">
                    {clip_form_label for=$field text=$item.title|clip_translate}
                    {clip_form_relation field=$field relation=$item minchars=2 op='likefirst'}
                </div>
                {/foreach}

            </fieldset>
            {/if}

            <fieldset>
                <legend>{gt text='Publication options'}</legend>

                <div class="z-formrow">
                    {clip_form_label for='core_language' __text='Language'}
                    {clip_form_plugin field='core_language' mandatory=false}
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
            </fieldset>
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
