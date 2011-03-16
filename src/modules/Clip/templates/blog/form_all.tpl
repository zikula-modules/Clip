
{include file='clip_generic_navbar.tpl' section='form'}

{if $pubdata.id}
    <h2>{gt text='Edit post'}</h2>
{else}
    <h2>{gt text='New post'}</h2>
{/if}

{assign var='zformclass' value="z-form z-form-light clip-editform clip-editform-`$pubtype.tid` clip-editform-`$pubtype.tid`-`$pubtype.stepname`"}

{form cssClass=$zformclass enctype='multipart/form-data'}
    <div>
        {formvalidationsummary}
        <fieldset class="z-linear">
            <div class="z-formrow">
                {clip_form_genericplugin id='title' cssClass='z-form-text-big' group='pubdata'}
                <span class="z-formnote z-sub">{gt text='Enter title here'}</span>
            </div>

            <div class="z-formrow">
                {formlabel for='content' __text='Content'}
                {clip_form_genericplugin id='content' maxLength='65535' rows='25' cols='70' group='pubdata'}
            </div>

            <div class="z-formrow">
                {formlabel for='summary' __text='Summary'}
                {clip_form_genericplugin id='summary' maxLength='65535' rows='4' cols='70' group='pubdata'}
                <span class="z-formnote z-sub">{gt text='Optional hand-crafted summary of your content that can be used in your templates.'}</span>
            </div>

            <div class="z-formrow">
                {formlabel for='category' __text='Category'}
                {clip_form_genericplugin id='category' group='pubdata'}
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

        <fieldset>
            <legend>{gt text='Post options'}</legend>

            <div class="z-formrow">
                {formlabel for='core_language' __text='Language'}
                {formlanguageselector id='core_language' group='pubdata' mandatory=false}
            </div>

            <div class="z-formrow">
                {formlabel for='core_publishdate' __text='Publish date'}
                {formdateinput id='core_publishdate' group='pubdata' includeTime=true}
            </div>

            <div class="z-formrow">
                {formlabel for='core_expiredate' __text='Expire date'}
                {formdateinput id='core_expiredate' group='pubdata' includeTime=true}
            </div>

            <div class="z-formrow">
                {formlabel for='core_showinlist' __text='Show in list'}
                {formcheckbox id='core_showinlist' group='pubdata' checked='checked'}
            </div>
        </fieldset>

        {*if $pubdata.id}
            {notifydisplayhooks eventname='clip.hook.item.ui.edit' area='module_area.clip.item' subject=$pubdata module='Clip'}
        {else}
            {notifydisplayhooks eventname='clip.hook.item.ui.edit' area='module_area.clip.item' module='Clip'}
        {/if*}

        <div class="z-buttons z-formbuttons">
            {foreach item='action' from=$actions}
                {gt text=$action.title assign='actiontitle'}
                {formbutton commandName=$action.id text=$actiontitle zparameters=$action.parameters.button|default:''}
            {/foreach}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
{/form}
