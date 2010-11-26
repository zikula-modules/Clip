
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='configure.gif' set='icons/large' __alt='Settings'}</div>

    <h2>{gt text='Settings'}</h2>

    <div class="z-menu clip-menu">
        <span class="z-menuitem-title"><a href="{modurl modname='Clip' type='admin' func='importps'}">{gt text='Import pagesetter publications'}</a></span>
    </div>

    {form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <legend>{gt text='General settings'}</legend>
            <div class="z-formrow">
                {formlabel for='uploadpath' __text='Upload path'}
                {formtextinput id='uploadpath' maxLength='200'}
                {if $updirstatus lt 3}
                    {assign var='updir_msgcolor' value='#ff0000'}
                    {if $updirstatus eq 0}
                        {assign var='updir_message' __value='The given path doesn\'t exists'}
                    {elseif $updirstatus eq 1}
                        {assign var='updir_message' __value='The given path is not a directory'}
                    {elseif $updirstatus eq 2}
                        {assign var='updir_message' __value='The given path is not writeable'}
                    {/if}
                {else}
                    {assign var='updir_msgcolor' value='#00d900'}
                    {assign var='updir_message' __value='The given path is writeable'}
                {/if}
                <span class="z-formnote">
                    {gt text='Path where uploaded files will be stored, relative to the site root (%s)' tag1=$siteroot}<br />
                    <span style="color: {$updir_msgcolor};">{$updir_message}</span>
                </span>
            </div>
            <div class="z-formrow">
                {formlabel for='maxperpage' __text='Max. items per page'}
                {formintinput id='maxperpage' maxLength=4 minValue=0 maxValue=9999}
                <span class="z-formnote">
                    {gt text='Maximum number of items to display when a pubtype does not have a limit.'}
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

        {*notifydisplayhooks eventname='clip.config.ui.edit' module='Clip' subject=$modvars.Clip*}

        <div class="z-buttons z-formbuttons">
            {formbutton id='update' commandName='update' __text='Save' class='z-bt-ok'}
            {formbutton id='cancel' commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
