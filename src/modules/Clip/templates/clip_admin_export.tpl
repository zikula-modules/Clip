
{*ajaxheader module='Clip' filename='clip_admin_export.js'*}
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='db_comit.png' set='icons/large' __alt='Export' }</div>

    <h2>{gt text='Export'}</h2>

    {form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <div class="z-formrow">
                {formlabel for='tid' text='Publication Type' mandatorysym=true}
                {formdropdownlist items=$pubtypes id='tid' mandatory=true}
                <div class="z-formnote">{gt text='Publication type to export.'}</div>
            </div>
            {*
            <div class="z-formrow">
                {formlabel for='withrelations' text='Include relations'}
                {formcheckbox id='withrelations'}
                <div class="z-formnote">{gt text='Include its related publication types and data?'}</div>
            </div>
            *}
            <div class="z-formrow">
                {formlabel for='format' text='Format'}
                {formdropdownlist items=$formats id='format'}
                <div class="z-formnote">{gt text='Field for sorting.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='filter' text='Filter'}
                {formtextinput id='filter' maxLength='1000'}
                <div class="z-formnote">{gt text='Any filter string to use in the export.'}</div>
            </div>
            {assign var='outputto' value=1}
            {formtextinput id='outputto' textMode="hidden"}
            {*
            <div class="z-formrow">
                {formlabel text='Output to'}
                <div id="output_options">
                    {foreach from=$outputs item='output'}
                        {formradiobutton id="outputto`$output.value`" dataField='outputto' value=$output.value} {formlabel for="outputto`$output.value`" text=$output.text}
                    {/foreach}
                </div>
            </div>
            <div id="wrap_filename">
                <div class="z-formrow">
                    {formlabel for='filename' text='Filename'}
                    {formintinput id='filename' maxLength='255'}
                    <div class="z-formnote">{gt text='Without the extension.'}</div>
                </div>
            </div>
            *}
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {formbutton commandName='export' __text='Export' class='z-bt-ok'}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
