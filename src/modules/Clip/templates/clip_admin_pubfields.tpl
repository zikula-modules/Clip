
{include file='clip_admin_header.tpl'}

{ajaxheader module='Clip' filename='clip_admin_pubfields.js' ui=true}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='db_update.png' set='icons/large' __alt='Manage Publication fields' }</div>

    <h2>{gt text='Manage Publication fields'}</h2>

    {clip_admin_submenu tid=$tid field=1}

    <p class="z-informationmsg">{gt text='You can order the fields using Drag and Drop on the following list.'}</p>

    <div class="z-form">
        <fieldset class="z-clip-mini">
            <legend>{gt text='Existing publication fields'}</legend>

            <span id='clip_tid' style="display: none">{$tid}</span>
            <ul id="pubfieldlist" class="z-itemlist">
                <li id="pubfieldlistheader" class="pubfieldlistheader z-itemheader z-itemsortheader z-clearfix">
                    <span class="z-itemcell z-w20">{gt text='Name'}</span>
                    <span class="z-itemcell z-w25">{gt text='Title'}</span>
                    <span class="z-itemcell z-w15">{gt text='Type'}</span>
                    <span class="z-itemcell z-w10">{gt text='Title field'}</span>
                    <span class="z-itemcell z-w10">{gt text='Mandatory'}</span>
                    <span class="z-itemcell z-w10">{gt text='Searchable'}</span>
                    <span class="z-itemcell z-w10">{gt text='Actions'}</span>
                </li>
                {foreach from=$pubfields item=pubfield name=pubfields}
                <li id="pubfield_{$pubfield.id}" class="{cycle name='pubfieldlist' values='z-odd,z-even'} z-sortable z-clearfix z-itemsort">
                    <span class="z-itemcell z-w20" id="pubfielddrag_{$pubfield.id}">
                        <strong>{$pubfield.name}</strong>
                    </span>
                    <span class="z-itemcell z-w25">
                        {img modname='core' src='info.png' set='icons/extrasmall' alt='(i)' class='tooltips' title="#field_tooltip`$pubfield.id`"}
                        {$pubfield.title}
                    </span>
                    <span id="field_tooltip{$pubfield.id}" style="display: none">
                        <dl>
                            {if $pubfield.description}
                            <dt>{gt text='Description'}</dt>
                            <dd>{$pubfield.description}</dd>
                            {/if}
                            {if $pubfield.fieldmaxlength}
                            <dt>{gt text='Max. length'}</dt>
                            <dd>{$pubfield.fieldmaxlength}</dd>
                            {/if}
                            <dt>{gt text='Pageable'}</dt>
                            <dd>{$pubfield.ispageable|yesno}</dd>
                            <dt>{gt text='Creation date'}</dt>
                            <dd>{$pubfield.cr_date|dateformat:'datetimebrief'}</dd>
                            <dt>{gt text='Update date'}</dt>
                            <dd>{$pubfield.lu_date|dateformat:'datetimebrief'}</dd>
                        </dl>
                    </span>
                    <span class="z-itemcell z-w15">
                        {$pubfield.fieldplugin|clip_pluginname}&nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.istitle}
                        {img modname='core' src='greenled.png' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.ismandatory}
                        {img modname='core' src='greenled.png' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.issearchable}
                        {img modname='core' src='greenled.png' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        <a href="{modurl modname='Clip' type='admin' func='pubfields' tid=$pubfield.tid id=$pubfield.id fragment='newpubfield'}">
                            {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                        </a>
                    </span>
                </li>
                {foreachelse}
                <li class="z-odd z-clearfix z-center">{gt text="This publication type doesn't have fields yet."}</li>
                {/foreach}
            </ul>
        </fieldset>
    </div>

    {form cssClass='z-form' enctype='application/x-www-form-urlencoded'}
    <div>
        {formvalidationsummary}
        <fieldset>
            {if $field.name}
                <legend>{gt text='Edit publication field'}</legend>
            {else}
                <legend>{gt text='Add a publication field'}</legend>
            {/if}
            {if $field.name}
            <p class="z-warningmsg">{gt text='Warning: When publication fields are changed or deleted, the database table of the publication type is updated automatically, and you could loss data of this publication type permanently. Be careful!'}</p>
            {/if}
            <div class="z-formrow">
                {formlabel for='name' text='Name' mandatorysym=true}
                {formtextinput id='name' group='field' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text='Name of this field (is used e.g. in the template variables).'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='title' text='Display name' mandatorysym=true}
                {formtextinput id='title' group='field' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text="Title (is shown e.g. in the automatically generated templates) and can be a custom gettext string."}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='description' text='Note'}
                {formtextinput id='description' group='field' maxLength='255'}
                <div class="z-formnote">{gt text='Optional tooltip of this field used on the input form, and can be a custom gettext string.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='fieldplugin' text='Fieldtype (Plugin)'}
                {clip_form_plugintype id='fieldplugin' group='field'}
                <span class="z-formnote">{gt text='Which kind of fieldtype is used (can be extended by plugins). Detailed informations about the individual plugins can be found in the documentation.'}</span>
                <span class="z-formnote" id="typedata_wrapper">
                    {formtextinput id='typedata' group='field' maxLength='4000'} <span class="z-warningmsg">{gt text='Edit this field only if you know what you are doing.'}</span><br />
                    {gt text="This is the configuration data of the field, if you edit it manually you can get unexpected results. Please use the configuration icon next to the selector to configure the field with ease."}
                </span>
            </div>
            <div class="z-formrow">
                {formlabel for='istitle' text='Title field'}
                {formcheckbox id='istitle' group='field'}
                <div class="z-formnote">{gt text='The content of this field will be used as the title?'}</div>
            </div>
            {*if !isset($field) OR $field.fieldplugin eq 'Text'}
            <div class="z-formrow">
                {formlabel for='ispageable' text='Pageable'}
                {formcheckbox id='ispageable' group='field'}
                <div class="z-formnote">{gt text='The content of this field is pageable?'}</div>
            </div>
            {/if*}
            <div class="z-formrow">
                {formlabel for='ismandatory' text='Mandatory'}
                {formcheckbox id='ismandatory' group='field'}
                <div class="z-formnote">{gt text='Is this field mandatory?'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='issearchable' text='Searchable'}
                {formcheckbox id='issearchable' group='field'}
                <div class="z-formnote">{gt text='The content of this field can be searched?'}</div>
            </div>
            {if !isset($field) OR !in_array($field.fieldplugin, array('Checkbox', 'Date', 'Email', 'Float', 'Image', 'List', 'Ms', 'MultiCheck', 'MultiList', 'RadioList', 'Text', 'Upload', 'Url'))}
            <div class="z-formrow">
                {formlabel for='isuid' text='Is a User ID'}
                {formcheckbox id='isuid' group='field'}
                <div class="z-formnote">{gt text='This field contains a User ID? If enabled it will be filtered only with the user operator.'}</div>
            </div>
            {/if}
            {if !isset($field) OR !in_array($field.fieldplugin, array('Checkbox', 'Date', 'Float', 'Image', 'Int', 'List', 'Ms', 'MultiCheck', 'MultiList', 'RadioList', 'Upload', 'User'))}
            <div class="z-formrow">
                {formlabel for='fieldmaxlength' text='Max. length'}
                {formintinput id='fieldmaxlength' group='field' maxLength='15'}
                <div class="z-formnote">{gt text='The maximum length for the content of this field.'}</div>
            </div>
            {/if}
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {if $field.name}
                {formbutton commandName='create' __text='Save' class='z-bt-save'}
                {gt text='Are you sure you want to delete this field?' assign='confirmdeletion'}
                {formbutton commandName='delete' __text='Delete' class='z-bt-delete' confirmMessage=$confirmdeletion}
            {else}
                {formbutton commandName='create' __text='Create' class='z-bt-ok'}
            {/if}
            {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
        </div>
    </div>
    {/form}
</div>
