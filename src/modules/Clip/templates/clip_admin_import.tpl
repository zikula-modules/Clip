
{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='db_update.gif' set='icons/large' __alt='Import' }</div>

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
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {formbutton commandName='import' __text='Import' class='z-bt-ok'}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
