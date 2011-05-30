
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='db_update.png' set='icons/large' __alt='Import' }</div>

    <h2>{gt text='Import'}</h2>

    {form cssClass='z-form' enctype='multipart/form-data'}
    <div>
        {formvalidationsummary}
        <fieldset>
            <div class="z-formrow">
                {formlabel text='File'}
                {formuploadinput id='file'}
                <div class="z-formnote">{gt text='Select the file with the publication(s) data.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel text='Redirect'}
                <div id="redirect_options">
                    {formradiobutton id='redirect1' dataField='redirect' value=1} {formlabel for='redirect1' __text='Yes'}
                    {formradiobutton id='redirect0' dataField='redirect' value=0} {formlabel for='redirect0' __text='No'}
                </div>
                <span class="z-formnote">{gt text='Go to the newly created publication type after the import.'}</span>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {formbutton commandName='import' __text='Import' class='z-bt-ok'}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
