{ajaxheader module='Clip' filename='clip_admin_pubtype.js'}

{include file='clip_admin_header.tpl'}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='db.png' set='icons/small' alt=''}
    {if isset($pubtype.tid)}
        <h3>{$pubtype.title} &raquo; {gt text='Edit publication type'}</h3>
        {clip_adminmenu tid=$pubtype.tid}
    {else}
        <h3>{gt text='Create publication type'}</h3>
    {/if}
</div>

{form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
<div>
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text='General options'}</legend>
        <div class="z-formrow">
            {formlabel for='title' __text='Title' mandatorysym=true}
            {formtextinput id='title' group='pubtype' maxLength='255' mandatory=true}
            <div class="z-formnote">
                {gt text='Title of the publication type, can be a custom gettext string.'}<br />
                <a id="clip_pubtype_collapse" href="javascript:void(0);">{gt text='Click here'}</a> {gt text='to set a custom URL title for the publication type'}
            </div>
        </div>
        <div id="clip_pubtype_urltitle">
            <div class="z-formrow">
                {formlabel for='urltitle' __text='URL title'}
                {formtextinput id='urltitle' group='pubtype' maxLength='150'}
                <div class="z-formnote">{gt text='Leave blank to autogenerate from the title.'}</div>
            </div>
        </div>

        <div class="z-formrow">
            {formlabel for='description' __text='Description'}
            {formtextinput id='description' group='pubtype' maxLength='255'}
            <div class="z-formnote">{gt text='Description of the publication type, can be a custom gettext string.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='itemsperpage' __text='Items per page' mandatorysym=true}
            {formintinput id='itemsperpage' group='pubtype' maxLength='255' mandatory=true}
            <div class="z-formnote">{gt text='After how many publications the list will be paged. 0 for no paging.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='enablerevisions' __text='Revision'}
            {formcheckbox id='enablerevisions' group='pubtype'}
            <div class="z-formnote">{gt text='Enable revisioning. Currently, the relations does not work properly with revisioning.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='enableeditown' __text='Edit own'}
            {formcheckbox id='enableeditown' group='pubtype'}
            <div class="z-formnote">{gt text='Allow editing of own publications.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='defaultfilter' __text='Default filter'}
            {formtextinput id='defaultfilter' group='pubtype' maxLength='255'}
            <div class="z-formnote">{gt text='The filter which is used by default.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='workflow' __text='Workflow'}
            {formdropdownlist id='workflow' group='pubtype' items=$clipworkflows}
            <div class="z-formnote">{gt text='You can choose a special workflow for the publications.'}</div>
        </div>
    </fieldset>

    {if isset($pubfields)}
    <fieldset>
        <legend>{gt text='Sort options'}</legend>
        <div class="z-formrow">
            {formlabel for='sortfield1' __text='Sort field'}
            {formdropdownlist items=$pubfields id='sortfield1' group='pubtype'}
            <div class="z-formnote">{gt text='Field for sorting.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='sortdesc1' __text='Sort descending'}
            {formcheckbox id='sortdesc1' group='pubtype'}
        </div>
        <div class="z-formrow">
            {formlabel for='sortfield2' __text='Sort field'}
            {formdropdownlist items=$pubfields id='sortfield2' group='pubtype'}
            <div class="z-formnote">{gt text='Field for sorting.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='sortdesc2' __text='Sort descending'}
            {formcheckbox id='sortdesc2' group='pubtype'}
        </div>
        <div class="z-formrow">
            {formlabel for='sortfield3' __text='Sort field'}
            {formdropdownlist items=$pubfields id='sortfield3' group='pubtype'}
            <div class="z-formnote">{gt text='Field for sorting.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='sortdesc3' __text='Sort descending'}
            {formcheckbox id='sortdesc3' group='pubtype'}
        </div>
    </fieldset>
    {/if}

    <fieldset>
        <legend>{gt text='Output options'}</legend>
        <div class="z-formrow">
            {formlabel for='folder' __text='Folder' mandatorysym=true}
            {formtextinput id='folder' group='pubtype' maxLength='255' mandatory=true}
            <div class="z-formnote">{gt text='Folder where publication type template are.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='cachelifetime' __text='Caching time'}
            {formintinput id='cachelifetime' group='pubtype' maxLength='6'}
            <span class="z-sub" style="display: inline;">{gt text='(in seconds)'}</span>
            <div class="z-formnote">{gt text='How long should the publications be cached. Empty for no cache.'}</div>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text='Relations options'}</legend>
        <div class="z-informationmsg">
            {gt text='Note that for performance reasons, only single records are loaded (if enabled). The rest are loaded on-demand from the templates.'}
        </div>

        <fieldset>
            <legend>{gt text='List view'}</legend>
            <div class="z-formrow">
                {formlabel __text='Load related records?'}
                <div id="view_load">
                    {formradiobutton id='view_load_1' groupName='view_load' dataField='view.load' group='config' value='1'}
                    {formlabel for='view_load_1' __text='Yes'}
                    {formradiobutton id='view_load_0' groupName='view_load' dataField='view.load' group='config' value='0'}
                    {formlabel for='view_load_0' __text='No'}
                </div>
            </div>
            <div id="view_advancedconfig" class="z-formnote">
                <div>
                    {formcheckbox id='view_onlyown' name='view.onlyown' dataField='view.onlyown' group='config'}
                    {formlabel for='view_onlyown' __text='Only own relations?'}
                </div>
                <div>
                    {formcheckbox id='view_processrefs' name='view.processrefs' dataField='view.processrefs' group='config'}
                    {formlabel for='view_processrefs' __text='Process relations data'}
                </div>
                <div id="view_advancedprocess">
                    <div>
                        {formcheckbox id='view_checkperm' name='view.checkperm' dataField='view.checkperm' group='config'}
                        {formlabel for='view_checkperm' __text='Check permissions?'}
                    </div>
                    <div>
                        {formcheckbox id='view_handleplugins' name='view.handleplugins' dataField='view.handleplugins' group='config'}
                        {formlabel for='view_handleplugins' __text='Handle plugins data?'}
                    </div>
                    <div>
                        {formcheckbox id='view_loadworkflow' name='view.loadworkflow' dataField='view.loadworkflow' group='config'}
                        {formlabel for='view_loadworkflow' __text='Load workflow state?'}
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text='Display view'}</legend>
            <div class="z-formrow">
                {formlabel __text='Load related records?'}
                <div id="display_load">
                    {formradiobutton id='display_load_1' groupName='display_load' dataField='display.load' group='config' value='1'}
                    {formlabel for='display_load_1' __text='Yes'}
                    {formradiobutton id='display_load_0' groupName='display_load' dataField='display.load' group='config' value='0'}
                    {formlabel for='display_load_0' __text='No'}
                </div>
            </div>
            <div id="display_advancedconfig" class="z-formnote">
                <div>
                    {formcheckbox id='display_onlyown' name='display.onlyown' dataField='display.onlyown' group='config'}
                    {formlabel for='display_onlyown' __text='Only own relations?'}
                </div>
                <div>
                    {formcheckbox id='display_processrefs' name='display.processrefs' dataField='display.processrefs' group='config'}
                    {formlabel for='display_processrefs' __text='Process relations data'}
                </div>
                <div id="display_advancedprocess">
                    <div>
                        {formcheckbox id='display_checkperm' name='display.checkperm' dataField='display.checkperm' group='config'}
                        {formlabel for='display_checkperm' __text='Check permissions?'}
                    </div>
                    <div>
                        {formcheckbox id='display_handleplugins' name='display.handleplugins' dataField='display.handleplugins' group='config'}
                        {formlabel for='display_handleplugins' __text='Handle plugins data?'}
                    </div>
                    <div>
                        {formcheckbox id='display_loadworkflow' name='display.loadworkflow' dataField='display.loadworkflow' group='config'}
                        {formlabel for='display_loadworkflow' __text='Load workflow state?'}
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text='Edit form'}</legend>
            <div class="z-formrow">
                {formcheckbox id='edit_load' name='edit.load' dataField='edit.load' group='config'}
                {formlabel for='edit_load' __text='Load relations autocompleters?'}
            </div>
            <div id="edit_advancedprocess">
                <div class="z-formrow">
                    {formcheckbox id='edit_onlyown' name='edit.onlyown' dataField='edit.onlyown' group='config'}
                    {formlabel for='edit_onlyown' __text='Edit only own relations?'}
                </div>
            </div>
        </fieldset>
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {if isset($pubtype.tid)}
            {formbutton commandName='save' __text='Save' class='z-bt-save'}
            {formbutton commandName='clone' __text='Clone' class='clip-bt-clone'}
            {gt text='Are you sure you want to delete this publication type and all its fields and publications?' assign='confirmdeletion'}
            {formbutton commandName='delete' __text='Delete' class='z-btred z-bt-delete' confirmMessage=$confirmdeletion}
        {else}
            {formbutton commandName='save' __text='Create' class='z-bt-ok'}
        {/if}
        <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
        {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>
</div>
{/form}
{adminfooter}
