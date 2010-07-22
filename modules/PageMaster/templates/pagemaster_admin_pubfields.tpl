{* $Id$ *}

{include file='pagemaster_admin_header.tpl'}

{ajaxheader module='PageMaster' filename='pmadmin_pubfields.js' dragdrop=true}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='db_update.gif' set='icons/large' __alt='Manage Publication fields' }</div>

    <h2>{gt text='Manage Publication fields'}</h2>

    {pmadminsubmenu tid=$tid id=$id|default:''}

    {modurl modname='PageMaster' type='admin' func='dbupdate' tid=$tid assign='urlupdate'}
    {assign var='urlupdate' value=$urlupdate|safetext}
    <p class="z-warningmsg">{gt text='When publication fields are added or changed you need to <a href="%s">Update the DB Table</a>.' tag1=$urlupdate}</p>

    <div class="z-form">
        <fieldset>
            <legend>{gt text='Existing publication fields'}</legend>

            <span id='pm_tid' style="display: none">{$tid}</span>
            <ul id="pubfieldlist" class="z-itemlist">
                <li id="pubfieldlistheader" class="pubfieldlistheader z-itemheader z-itemsortheader z-clearfix">
                    <span class="z-itemcell z-w15">{gt text='Name'}</span>
                    <span class="z-itemcell z-w15">{gt text='Title'}</span>
                    <span class="z-itemcell z-w20">{gt text='Description'}</span>
                    <span class="z-itemcell z-w10">{gt text='Title field'}</span>
                    <span class="z-itemcell z-w10">{gt text='Mandatory'}</span>
                    <span class="z-itemcell z-w10">{gt text='Searchable'}</span>
                    <span class="z-itemcell z-w10">{gt text='Max. length'}</span>
                    <span class="z-itemcell z-w10">{gt text='Actions'}</span>
                </li>
                {foreach from=$pubfields item=pubfield name=pubfields}
                <li id="pubfield_{$pubfield.id}" class="{cycle name='pubfieldlist' values='z-odd,z-even'} z-sortable z-clearfix z-itemsort">
                    <span class="z-itemcell z-w15" id="pubfielddrag_{$pubfield.id}">
                        {$pubfield.name}
                    </span>
                    <span class="z-itemcell z-w15">
                        {$pubfield.title}&nbsp;
                    </span>
                    <span class="z-itemcell z-w20">
                        {$pubfield.description|truncate:45}&nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.istitle}
                        {img modname='core' src='greenled.gif' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.ismandatory}
                        {img modname='core' src='greenled.gif' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.issearchable}
                        {img modname='core' src='greenled.gif' width='10' height='10' set='icons/extrasmall'}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        {if $pubfield.fieldmaxlength}
                        {$pubfield.fieldmaxlength}
                        {/if}
                        &nbsp;
                    </span>
                    <span class="z-itemcell z-w10">
                        <a href="{modurl modname='PageMaster' type='admin' func='pubfields' tid=$pubfield.tid id=$pubfield.id fragment='newpubfield'}">
                            {img modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
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
        <fieldset id="newpubfield">
            <legend>{gt text='Add a publication field'}</legend>
            <div class="z-formrow">
                {formlabel for='name' text='Name' mandatorysym=true}
                {formtextinput id='name' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text='Name of this field (is used e.g. in the template variables).'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='title' text='Display name' mandatorysym=true}
                {formtextinput id='title' maxLength='255' mandatory=true}
                <div class="z-formnote">{gt text="Title (is shown e.g. in the automatically generated templates) and can be a custom gettext string."}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='description' text='Note'}
                {formtextinput id='description' maxLength='255'}
                <div class="z-formnote">{gt text='Optional tooltip of this field used on the input form, and can be a custom gettext string.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='fieldplugin' text='Fieldtype (Plugin)'}
                {pmformplugintype id='fieldplugin'}
                <span class="z-formnote">{gt text='Which kind of fieldtype is used (can be extended by plugins). Detailed informations about the individual plugins can be found in the documentation.'}</span>
                <span class="z-formnote" id="typedata_wrapper">
                    {formtextinput id='typedata' maxLength='4000'} <span class="z-warningmsg">{gt text='Edit this field only if you know what you are doing.'}</span><br />
                    {gt text="This is the configuration data of the field, if you edit it manually you can get unexpected results. Please use the configuration icon next to the selector to configure the field with ease."}
                </span>
            </div>
            <div class="z-formrow">
                {formlabel for='istitle' text='Title field'}
                {formcheckbox id='istitle'}
                <div class="z-formnote">{gt text='The content of this field will be used as the title?'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='ispageable' text='Pageable'}
                {formcheckbox id='ispageable'}
                <div class="z-formnote">{gt text='The content of this field is pageable?'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='ismandatory' text='Mandatory'}
                {formcheckbox id='ismandatory'}
                <div class="z-formnote">{gt text='Is this field mandatory?'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='issearchable' text='Searchable'}
                {formcheckbox id='issearchable'}
                <div class="z-formnote">{gt text='The content of this field can be searched?'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='fieldmaxlength' text='Max. length'}
                {formintinput id='fieldmaxlength' maxLength='15'}
                <div class="z-formnote">{gt text='The maximum length for the content of this field.'}</div>
            </div>
            <div class="z-formrow">
                {formlabel for='lineno' text='Weight order'}
                {formintinput id='lineno' maxLength='4'}
                <div class="z-formnote">{gt text='Weight number of this field. You can leave it blank and order it later with Drag & Drop in the full list.'}</div>
            </div>
        </fieldset>

        <div class="z-formbuttons">
            {formbutton commandName='create' __text='Save'}
            {if isset($id)}
            {formbutton commandName='delete' __text='Delete'}
            {/if}
            {formbutton commandName='cancel' __text='Cancel'}
        </div>
    </div>
    {/form}
</div>
