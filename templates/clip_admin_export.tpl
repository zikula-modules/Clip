{pageaddvar name='javascript' value='prototype'}

{include file='clip_admin_header.tpl'}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='db_update.png' set='icons/small' alt=''}
    <h3>{gt text='Export'}</h3>
</div>

{form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
<div>
    {formvalidationsummary}
    <fieldset>
        <div class="z-formrow">
            {formlabel for='tid' __text='Publication Type' mandatorysym=true}
            {formdropdownlist items=$pubtypes id='tid' mandatory=true}
            <span class="z-formnote z-sub">{gt text='Publication type to export.'}</span>
        </div>
        {*
        <div class="z-formrow">
            {formlabel for='withrelations' __text='Include relations'}
            {formcheckbox id='withrelations'}
            <span class="z-formnote z-sub">{gt text='Include its related publication types and data?'}</span>
        </div>
        *}
        <div class="z-formrow">
            {formlabel for='format' __text='Format'}
            {formdropdownlist items=$formats id='format'}
            <span class="z-formnote z-sub">{gt text='Field for sorting.'}</span>
        </div>
        <div class="z-formrow">
            {formlabel for='filter' __text='Filter'}
            {formtextinput id='filter' maxLength='1000'}
            <span class="z-formnote z-sub">{gt text='Any filter string to use in the export.'}</span>
        </div>
        <div class="z-formrow">
            {formlabel for='exportdata_options' __text='Export data'}
            <div id="exportdata_options">
                {formradiobutton id='exportdata1' dataField='exportdata' value=1} {formlabel for='exportdata1' __text='Yes'}
                {formradiobutton id='exportdata0' dataField='exportdata' value=0} {formlabel for='exportdata0' __text='No'}
            </div>
        </div>
        {assign var='outputto' value=1}
        {formtextinput id='outputto' textMode="hidden"}
        {*
        <div class="z-formrow">
            {formlabel __text='Output to'}
            <div id="output_options">
                {foreach from=$outputs item='output'}
                    {formradiobutton id="outputto`$output.value`" dataField='outputto' value=$output.value} {formlabel for="outputto`$output.value`" text=$output.text}
                {/foreach}
            </div>
        </div>
        <div id="wrap_filename">
            <div class="z-formrow">
                {formlabel for='filename' __text='Filename'}
                {formintinput id='filename' maxLength='255'}
                <span class="z-formnote z-sub">{gt text='Without the extension.'}</span>
            </div>
        </div>
        *}
    </fieldset>

    <div id="clipex-buttons" class="z-buttons z-formbuttons">
        {capture assign='onClick'}{literal}window.setTimeout(function() { $('clipex-buttons').hide(); $('clipex-warning').show(); }, 500){/literal}{/capture}
        {formbutton commandName='export' __text='Export' class='z-bt-ok' onClick=$onClick}
        {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>

    <div id="clipex-warning" class="z-warningmsg" style="display: none;">
        {modurl modname='Clip' type='admin' func='clipexport' assign='exporturl'}
        {gt text='To use the export form again, <a href="%s">refresh it by clicking here</a>.' tag1=$exporturl|safetext}
    </div>
</div>
{/form}

{adminfooter}
