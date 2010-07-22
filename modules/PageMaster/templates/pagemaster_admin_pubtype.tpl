{* $Id$ *}

{include file='pagemaster_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='database.gif' set='icons/large' __alt='Create / Edit publication type' }</div>

    {if isset($tid)}
    <h2>{gt text='Edit publication type'}</h2>
    {pmadminsubmenu tid=$tid}
    {else}
    <h2>{gt text='Create publication type'}</h2>
    {/if}

    {form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <legend>{gt text='General options'}</legend>
            <div class="z-formrow">
                {formlabel for='title' text='Title' mandatorysym=true}
                {formtextinput id='title' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text='Title of the publication type, can be a custom gettext string.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='description' text='Description'}
                {formtextinput id='description' maxLength='255'}
                <div class="z-formnote">{gt text='Description of the publication type, can be a custom gettext string.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='itemsperpage' text='Items per page' mandatorysym=true}
                {formintinput id='itemsperpage' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text='After how many publications the list will be paged. 0 for no paging.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='enablerevisions' text='Revision'}
                {formcheckbox id='enablerevisions'}
                <div class="z-formnote">{gt text='Enable revisioning.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='enableeditown' text='Edit own'}
                {formcheckbox id='enableeditown'}
                <div class="z-formnote">{gt text='Allow editing of own publications.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='defaultfilter' text='Default filter'}
                {formtextinput id='defaultfilter' maxLength='255'}
                <div class="z-formnote">{gt text='The filter which is used by default.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='workflow' text='Workflow'}
                {formdropdownlist id='workflow' items=$pmworkflows}
                <div class="z-formnote">{gt text='You can choose a special workflow for the publications.'}</div>
            </div>
        </fieldset>

        {if isset($pubfields)}
        <fieldset>
            <legend>{gt text='Sort options'}</legend>
            <div class="z-formrow">
                {formlabel for='sortfield1' text='Sort field'}
                {formdropdownlist items=$pubfields id='sortfield1'}
                <div class="z-formnote">{gt text='Field for sorting.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='sortdesc1' text='Sort descending'}
                {formcheckbox id='sortdesc1'}
            </div>
            <div class="z-formrow">
                {formlabel for='sortfield2' text='Sort field'}
                {formdropdownlist items=$pubfields id='sortfield2'}
                <div class="z-formnote">{gt text='Field for sorting.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='sortdesc2' text='Sort descending'}
                {formcheckbox id='sortdesc2'}
            </div>
            <div class="z-formrow">
                {formlabel for='sortfield3' text='Sort field'}
                {formdropdownlist items=$pubfields id='sortfield3'}
                <div class="z-formnote">{gt text='Field for sorting.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='sortdesc3' text='Sort descending'}
                {formcheckbox id='sortdesc3'}
            </div>
        </fieldset>
        {/if}

        <fieldset>
            <legend>{gt text='Output options'}</legend>
            <div class="z-formrow">
                {formlabel for='filename' text='Output template name'}
                {formtextinput id='filename' maxLength='255'}
                <div class="z-formnote">{gt text='Name of the output template, used in the publist and pubview template names.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='formname' text='Input template name'}
                {formtextinput id='formname' maxLength='255'}
                <div class="z-formnote">{gt text='Name of the output, used in the pubedit template name.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='cachelifetime' text='Caching time'}
                {formintinput id='cachelifetime' maxLength='6'}
                <div class="z-formnote">{gt text='How long should the publications be cached. Empty for no cache.'}</div>
            </div>
        </fieldset>

        <div class="z-formbuttons">
            {formbutton commandName='create' __text='Save'}
            {formbutton commandName='delete' __text='Delete'}
            {formbutton commandName='cancel' __text='Cancel'}
        </div>
    </div>
    {/form}
</div>
