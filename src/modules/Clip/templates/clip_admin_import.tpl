
{include file='clip_admin_header.tpl'}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='db_comit.png' set='icons/small' alt=''}
    <h3>{gt text='Import'}</h3>
</div>

{form cssClass='z-form' enctype='multipart/form-data'}
<div>
    {formvalidationsummary}
    <fieldset>
        <div class="z-formrow">
            {formlabel text='File' for='file'}
            {formuploadinput id='file'}
            <span class="z-formnote z-sub">{gt text='Select the file with the publication(s) data.'}</span>
        </div>
        <div class="z-formrow">
            {formlabel text='Redirect' for='redirect_options'}
            <div id="redirect_options">
                {formradiobutton id='redirect1' dataField='redirect' value=1} {formlabel for='redirect1' __text='Yes' for='redirect1'}
                {formradiobutton id='redirect0' dataField='redirect' value=0} {formlabel for='redirect0' __text='No' for='redirect0'}
            </div>
            <span class="z-formnote z-sub">{gt text='Go to the newly created publication type after the import.'}</span>
        </div>
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {formbutton commandName='import' __text='Import' class='z-bt-ok'}
        {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>
</div>
{/form}
{adminfooter}
