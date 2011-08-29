
<div class="clip-edit clip-edit-{$pubtype.urltitle} clip-edit-{$pubtype.urltitle}-{$clipargs.edit.state}">
    {include file='generic_navbar.tpl'}

    <h2>
        {if $pubdata.id}
            {gt text='Edit post'}
        {else}
            {gt text='New post'}
        {/if}
    </h2>

    {form cssClass='z-form z-form-light' enctype='multipart/form-data'}
        <div>
            {formvalidationsummary}
            <fieldset class="z-linear">
                <div class="z-formrow">
                    {clip_form_plugin field='title' cssClass='z-form-text-big' group='pubdata'}
                    <span class="z-formnote z-sub">{gt text='Enter title here'}</span>
                </div>

                <div class="z-formrow">
                    {formlabel for='content' __text='Content'}
                    {clip_form_plugin field='content' maxLength='65535' rows='25' cols='70' group='pubdata'}
                </div>

                <div class="z-formrow">
                    {formlabel for='summary' __text='Summary'}
                    {clip_form_plugin field='summary' maxLength='65535' rows='4' cols='70' group='pubdata'}
                    <span class="z-formnote z-sub">{gt text='Optional hand-crafted summary of your content that can be used in your templates.'}</span>
                </div>

                <div class="z-formrow">
                    {formlabel for='category' __text='Category'}
                    {clip_form_plugin field='category' group='pubdata'}
                </div>
            </fieldset>

            {if $relations}
            <fieldset>
                <legend>{gt text='Related publications'}</legend>

                {foreach from=$relations key='alias' item='item' name='relations'}
                <div class="z-formrow">
                    {formlabel for=$alias text=$item.title}
                    {clip_form_relation alias=$alias relation=$item minchars=2 op='likefirst'}
                </div>
                {/foreach}

            </fieldset>
            {/if}

            <fieldset>
                <legend>{gt text='Post options'}</legend>

                <div class="z-formrow">
                    {formlabel for='core_language' __text='Language'}
                    {formlanguageselector id='core_language' group='pubdata' mandatory=false}
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
            </fieldset>

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
