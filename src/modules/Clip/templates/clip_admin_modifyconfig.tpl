
{include file='clip_admin_header.tpl'}

<div class="z-admin-content-pagetitle">
    {icon type='config' size='small'}
    <h3>{gt text='Settings'}</h3>
</div>

<ul class="z-menulinks clip-menu">
    <li>
        <a href="{modurl modname='Clip' type='admin' func='clipreset'}">{gt text='Reset models'}</a>
    </li>
    <li>
        <a href="{modurl modname='Clip' type='import' func='defaultypes'}">{gt text="Install 'Blog' and 'Pages' publication types"}</a>
    </li>
    <li>
        <a href="{modurl modname='Clip' type='import' func='importps'}">{gt text='Import pagesetter publications'}</a>
    </li>
</ul>

{form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
<div>
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text='General settings'}</legend>
        <div class="z-formrow">
            {formlabel for='uploadpath' __text='Upload path' mandatorysym=true}
            {formtextinput id='uploadpath' maxLength='500' mandatory=true}
            {if $status.upload lt 3}
                {assign var='msgcolor' value='#ff0000'}
                {if $status.upload eq 0}
                    {assign var='message' __value="The given path doesn't exists"}
                {elseif $status.upload eq 1}
                    {assign var='message' __value='The given path is not a directory'}
                {elseif $status.upload eq 2}
                    {assign var='message' __value='The given path is not writeable'}
                {/if}
            {else}
                {assign var='msgcolor' value='#00d900'}
                {assign var='message' __value='The given path is writeable'}
            {/if}
            <span class="z-formnote z-sub">
                {gt text='Path where uploaded files will be stored, relative to the site root (%s)' tag1=$siteroot}<br />
                <span style="color: {$msgcolor};">{$message}</span>
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='modelspath' __text='Models path' mandatorysym=true}
            {formtextinput id='modelspath' maxLength='500' mandatory=true}
            {if $status.models lt 3}
                {assign var='msgcolor' value='#ff0000'}
                {if $status.models eq 0}
                    {assign var='message' __value="The given path doesn't exists"}
                {elseif $status.models eq 1}
                    {assign var='message' __value='The given path is not a directory'}
                {elseif $status.models eq 2}
                    {assign var='message' __value='The given path is not writeable'}
                {/if}
            {else}
                {assign var='msgcolor' value='#00d900'}
                {assign var='message' __value='The given path is writeable'}
            {/if}
            <span class="z-formnote z-sub">
                {gt text="Path where Clip stores temporary files. The folder must be named 'ClipModels'"}<br />
                <span style="color: {$msgcolor};">{$message}</span>
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='pubtype' text='Publication Type'}
            {formdropdownlist items=$pubtypes id='pubtype'}
            <span class="z-formnote z-sub">{gt text='Default publication type to use when none is passed to Clip.'}</span>
        </div>
        <div class="z-formrow">
            {formlabel for='shorturls' __text='Default template'}
            {formtextinput id='shorturls' maxLength='40'}
            <span class="z-formnote z-sub">
                {gt text="Default template used in short URLs. Leave it empty to nor use extensions with short URls enabled. Only 'htm' and 'html' are omitted of special processing."}
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='maxperpage' __text='Max. items per page'}
            {formintinput id='maxperpage' maxLength=4 minValue=0 maxValue=9999}
            <span class="z-formnote z-sub">
                {gt text='Maximum number of items to display when a pubtype does not have a limit.'}
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='commontpls' __text='Common templates'}
            {formcheckbox id='commontpls'}
            <span class="z-formnote z-informationmsg">
                {gt text='Enable the use of common templates for the main, list and display screens.'}<br />
                {gt text='If the template <var>$pubtype/$func[_$tpl].tpl</var> does not exists, Clip will search for <var>common_$func[_$tpl].tpl</var> and use it if exists.'}
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='devmode' __text='Development mode'}
            {formcheckbox id='devmode'}
            <span class="z-formnote z-informationmsg">
                {gt text='Enable the development mode to see detailed notices about Clip requirements.'}
            </span>
        </div>
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {formbutton id='update' commandName='update' __text='Save' class='z-bt-ok'}
        <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
        {formbutton id='cancel' commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>
</div>
{/form}
{adminfooter}
