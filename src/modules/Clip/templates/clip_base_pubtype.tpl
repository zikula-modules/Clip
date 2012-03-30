{ajaxheader module='Clip' filename='Clip.Pubtype.js' ui=true}
{pageaddvarblock name='header'}
<script type="text/javascript">
    document.observe('dom:loaded', Zikula.Clip.Pubtype.Init);
</script>
{/pageaddvarblock}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='db.png' set='icons/small' alt=''}
    {if isset($pubtype.tid)}
        <h3>{$pubtype.title} &raquo; {gt text='Edit publication type'}</h3>
        {clip_adminmenu}
    {else}
        <h3>{gt text='Create publication type'}</h3>
    {/if}
</div>

{insert name='getstatusmsg'}

{form cssClass='z-form clip-form' enctype='application/x-www-form-urlencoded'}
<div id="clip-pubtype-form">
    {formvalidationsummary}
    <fieldset>
        <legend class="pubtype-panel-header">{gt text='General options'}</legend>
        <div>
            <div class="z-formrow">
                {formlabel for='title' __text='Title' mandatorysym=true}
                {formtextinput id='title' group='pubtype' maxLength='255' mandatory=true}
                <span class="z-formnote z-sub">{gt text='Title of the publication type.'}</span>
            </div>
            <div class="z-formrow">
                {formlabel for='urltitle' __text='URL title'}
                {formtextinput id='urltitle' group='pubtype' maxLength='150'}
                <div class="z-formnote z-sub">{gt text='Leave blank to autogenerate from the title.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='description' __text='Description'}
                {formtextinput id='description' group='pubtype' maxLength='255'}
                <div class="z-formnote z-sub">{gt text='Description of the publication type.'}</div>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend class="pubtype-panel-header">{gt text='User settings'}</legend>
        <div>
            <fieldset>
                <legend>{gt text='Output cache'}</legend>
                <div>
                    <div class="z-formrow">
                        {formlabel for='cachelifetime' __text='Caching lifetime'}
                        {formintinput id='cachelifetime' group='pubtype' maxLength='6'}
                        <span class="z-sub" style="display: inline;">{gt text='seconds'}</span>
                        <div class="z-formnote z-sub">{gt text='How long should the publications be cached. 0 for no cache.'}</div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>{gt text='Listing options'}</legend>
                <div>
                    <div class="z-formrow">
                        {formlabel for='fixedfilter' __text='Fixed filter'}
                        {formtextinput id='fixedfilter' group='pubtype' maxLength='255'}
                        <div class="z-formnote z-sub">{gt text='The filter used always on public listings.'}</div>
                    </div>
                    <div class="z-formrow">
                        {formlabel for='defaultfilter' __text='Default filter'}
                        {formtextinput id='defaultfilter' group='pubtype' maxLength='255'}
                        <div class="z-formnote z-sub">{gt text='The filter which is used by default if none specified.'}</div>
                    </div>
                    <div class="z-formrow">
                        {formlabel for='itemsperpage' __text='Items per page' mandatorysym=true}
                        {formintinput id='itemsperpage' group='pubtype' maxLength='255' mandatory=true}
                        <span class="z-sub" style="display: inline;">{gt text='publications'}</span>
                        <div class="z-formnote z-sub">{gt text='After how many publications the list will be paged. 0 for no paging.'}</div>
                    </div>

                    {if isset($pubfields)}
                    <div class="z-formrow">
                        {formlabel for='sortfield1' __text='First sort field'}
                        {formdropdownlist items=$pubfields id='sortfield1' group='pubtype'}
                        <div class="z-formnote z-sub">
                            {formcheckbox id='sortdesc1' group='pubtype'}
                            {formlabel for='sortdesc1' __text='Sort descending'}
                        </div>
                    </div>
                    <div class="z-formrow">
                        {formlabel for='sortfield2' __text='Second sort field'}
                        {formdropdownlist items=$pubfields id='sortfield2' group='pubtype'}
                        <div class="z-formnote z-sub">
                            {formcheckbox id='sortdesc2' group='pubtype'}
                            {formlabel for='sortdesc2' __text='Sort descending'}
                        </div>
                    </div>
                    <div class="z-formrow">
                        {formlabel for='sortfield3' __text='Third sort field'}
                        {formdropdownlist items=$pubfields id='sortfield3' group='pubtype'}
                        <div class="z-formnote z-sub">
                            {formcheckbox id='sortdesc3' group='pubtype'}
                            {formlabel for='sortdesc3' __text='Sort descending'}
                        </div>
                    </div>
                    {/if}
                </div>
            </fieldset>
        </div>
    </fieldset>

    <fieldset>
        <legend class="pubtype-panel-header">{gt text='Admin settings'}</legend>
        <div>
            <div class="z-formrow">
                {formlabel for='enableeditown' __text='Edit own'}
                {formcheckbox id='enableeditown' group='pubtype'}
                <div class="z-formnote z-sub">{gt text='Allow editing of own publications.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='enablerevisions' __text='Revision'}
                {formcheckbox id='enablerevisions' group='pubtype'}
                <div class="z-formnote z-sub">{gt text='Enable revisioning. Currently, the relations does not work properly with revisioning.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='folder' __text='Folder' mandatorysym=true}
                {formtextinput id='folder' group='pubtype' maxLength='255' mandatory=true}
                <div class="z-formnote z-sub">{gt text='Folder where publication type template are.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='workflow' __text='Workflow'}
                {formdropdownlist id='workflow' group='pubtype' items=$clipworkflows}
                <div class="z-formnote z-sub">{gt text='You can choose a special workflow for the publications.'}</div>
            </div>
            {* grouptype selector *}
        </div>
    </fieldset>

    <fieldset>
        <legend class="pubtype-panel-header">{gt text='Relations options'}</legend>
        <div>
            <div class="z-informationmsg">
                {gt text='Note that for performance reasons, only single records are loaded (if enabled). The rest are loaded on-demand from the templates.'}
            </div>

            <fieldset>
                <legend>{gt text='List view'}</legend>
                <div class="z-formrow">
                    {formlabel __text='Load related records?'}
                    <div id="list_load">
                        {formradiobutton id='list_load_1' groupName='list_load' dataField='list.load' group='config' value='1'}
                        {formlabel for='list_load_1' __text='Yes'}
                        {formradiobutton id='list_load_0' groupName='list_load' dataField='list.load' group='config' value='0'}
                        {formlabel for='list_load_0' __text='No'}
                    </div>
                </div>
                <div id="list_advancedconfig" class="z-formnote">
                    <div>
                        {formcheckbox id='list_onlyown' name='list.onlyown' dataField='list.onlyown' group='config'}
                        {formlabel for='list_onlyown' __text='Only own relations?'}
                    </div>
                    <div>
                        {formcheckbox id='list_checkperm' name='list.checkperm' dataField='list.checkperm' group='config'}
                        {formlabel for='list_checkperm' __text='Check related publications permissions?'}
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
                        {formcheckbox id='display_checkperm' name='display.checkperm' dataField='display.checkperm' group='config'}
                        {formlabel for='display_checkperm' __text='Check related publications permissions?'}
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
        </div>
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
