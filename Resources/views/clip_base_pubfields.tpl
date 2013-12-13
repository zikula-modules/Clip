{pageaddvar name='jsgettext' value='module_clip_js:Clip'}
{ajaxheader module='Clip' filename='Clip.Pubfields.js' ui=true}
{pageaddvarblock name='header'}
<script type="text/javascript">
    document.observe('dom:loaded', Zikula.Clip.Pubfields.Init);
</script>
{/pageaddvarblock}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='db_update.png' set='icons/small' alt=''}
    <h3>{$pubtype.title} &raquo; {gt text='Manage Publication fields'}</h3>
    {clip_adminmenu field=$field.id}
</div>

{insert name='getstatusmsg'}

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
            {foreach from=$pubfields item='pubfield' name='pubfields'}
            <li id="pubfield_{$pubfield.id}" class="{cycle name='pubfieldlist' values='z-even,z-odd'} z-sortable z-clearfix z-itemsort">
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
                        <dt>{gt text='Filterable'}</dt>
                        <dd>{$pubfield.isfilterable|yesno}</dd>
                        <dt>{gt text='Counter'}</dt>
                        <dd>{$pubfield.iscounter|yesno}</dd>
                        {*<dt>{gt text='Pageable'}</dt>
                        <dd>{$pubfield.ispageable|yesno}</dd>*}
                        <dt>{gt text='Creation date'}</dt>
                        <dd>{$pubfield.cr_date|dateformat:'datetimebrief'}</dd>
                        <dt>{gt text='Update date'}</dt>
                        <dd>{$pubfield.lu_date|dateformat:'datetimebrief'}</dd>
                    </dl>
                </span>
                <span class="z-itemcell z-w15">
                    {$pubfield.fieldplugin|clip_plugintitle}&nbsp;
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
                    <a href="{clip_url func='pubfields' tid=$pubfield.tid id=$pubfield.id fragment='newpubfield'}">
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

{form cssClass='z-form clip-form' enctype='application/x-www-form-urlencoded'}
<div id="newpubfield">
    {formvalidationsummary}
    <fieldset>
        {if isset($field.id)}
            <legend>{gt text='Edit publication field'}</legend>
        {else}
            <legend>{gt text='Add a publication field'}</legend>
        {/if}
        {if isset($field.id)}
        <p class="z-warningmsg">{gt text='Warning: When publication fields are changed or deleted, the database table of the publication type is updated automatically, and you could loss data of this publication type permanently. Be careful!'}</p>
        {/if}
        <div class="z-formrow">
            {formlabel for='name' __text='Name' mandatorysym=true}
            {formtextinput id='name' group='field' maxLength='255' mandatory=true regexValidationPattern='/^[a-zA-Z0-9_]+$/' __regexValidationMessage='Field name can only contain a-z letters and numbers.'}
            <div class="z-formnote z-warningmsg" style="margin-bottom: 0 !important">
                {gt text='This value is used as the form field ID, so be sure to choose a unique one.'}
            </div>
            <div class="z-formnote z-sub">{gt text='Name of this field (is used e.g. in the template variables).'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='title' __text='Display name' mandatorysym=true}
            {formtextinput id='title' group='field' maxLength='255' mandatory=true}
            <div class="z-formnote z-sub">{gt text="Title (is shown e.g. in the automatically generated templates) and can be a custom gettext string."}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='description' __text='Note'}
            {formtextinput id='description' group='field' maxLength='255'}
            <div class="z-formnote z-sub">{gt text='Optional tooltip of this field used on the input form, and can be a custom gettext string.'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='fieldplugin' __text='Fieldtype (Plugin)' mandatorysym=true}
            {clip_form_plugintype id='fieldplugin' group='field'}
            <span class="z-formnote z-sub">{gt text='Which kind of fieldtype is used (can be extended by plugins). Detailed informations about the individual plugins can be found in the documentation.'}</span>
            <span class="z-formnote" id="typedata_wrapper">
                {formtextinput id='typedata' group='field' maxLength='4000'} 
                <span class="z-warningmsg">{gt text='Edit this field only if you know what you are doing.'}</span>
                <br />
                <span class="z-sub">{gt text="This is the configuration data of the field, if you edit it manually you can get unexpected results. Please use the configuration icon next to the selector to configure the field with ease."}</span>
            </span>
        </div>
        <div class="z-formrow">
            {formlabel for='istitle' __text='Title field'}
            {formcheckbox id='istitle' group='field'}
            <div class="z-formnote z-sub">{gt text='The content of this field will be used as the title?'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='ismandatory' __text='Mandatory'}
            {formcheckbox id='ismandatory' group='field'}
            <div class="z-formnote z-sub">{gt text='Is this field mandatory?'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='issearchable' __text='Searchable'}
            {formcheckbox id='issearchable' group='field'}
            <div class="z-formnote z-sub">{gt text='The content of this field can be searched?'}</div>
        </div>
        <div class="z-formrow">
            {formlabel for='isfilterable' __text='Filterable'}
            {formcheckbox id='isfilterable' group='field'}
            <div class="z-formnote z-sub">{gt text='This field can be used to filter a list?'}</div>
        </div>
        {if in_array($field.fieldplugin, array('BigInt', 'Float', 'Int'))}
        <div class="z-formrow">
            {formlabel for='iscounter' __text='Counter'}
            {formcheckbox id='iscounter' group='field'}
            <div class="z-formnote z-sub">{gt text='This field acts as a counter?'}</div>
        </div>
        {/if}
        {if !in_array($field.fieldplugin, array('BigInt', 'Checkbox', 'Date', 'Float', 'Image', 'Int', 'List', 'Ms', 'MultiCheck', 'MultiList', 'RadioList', 'Upload', 'User'))}
        <div class="z-formrow">
            {formlabel for='fieldmaxlength' __text='Max. length'}
            {formintinput id='fieldmaxlength' group='field' maxLength='15'}
            <div class="z-formnote z-sub">{gt text='The maximum length for the content of this field.'}</div>
        </div>
        {/if}
        {*if $field.fieldplugin eq 'Text'}
        <div class="z-formrow">
            {formlabel for='ispageable' __text='Pageable'}
            {formcheckbox id='ispageable' group='field'}
            <div class="z-formnote z-sub">{gt text='The content of this field is pageable?'}</div>
        </div>
        {/if*}
    </fieldset>

    <div class="z-buttons z-formbuttons">
        {if isset($field.id)}
            {formbutton commandName='save' __text='Save' class='z-bt-save'}
            {gt text='Are you sure you want to delete this field?' assign='confirmdeletion'}
            {formbutton commandName='delete' __text='Delete' class='z-btred z-bt-delete' confirmMessage=$confirmdeletion}
        {else}
            {formbutton commandName='save' __text='Create' class='z-bt-ok'}
        {/if}
        <input class="clip-bt-reload" type="reset" value="{gt text='Reset'}" title="{gt text='Reset the form to its initial state'}" />
        {formbutton commandName='cancel' __text='Cancel' class='z-bt-cancel'}
    </div>
</div>
{/form}
