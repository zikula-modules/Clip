{* $Id$ *}
<div class="z-formrow">
    <label for="viewpub_pubtype">{gt text='Publication type'}</label>
    {html_options name='tid' options=$pubtypes selected=$vars.tid}
</div>
<div class="z-formrow">
    <label for="viewpub_pid">{gt text='Publication ID'}</label>
    <input id="viewpub_pid" name="pid" type="text" size="30" maxlength="255" value="{$vars.pid|safetext}" />
</div>
<div class="z-formrow">
    <label for="viewpub_template">{gt text='Template'}</label>
    <input id="viewpub_template" name="template" type="text" size="30" maxlength="255" value="{$vars.template|safetext}" />
    {gt text='Template' assign='tpl'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl' value="output/viewpub_block_pub_$tpl.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template '%s'.<br />Leave it blank to default to the output template name of the chosen publication type." tag1=$tpl}
        <br />{gt text='If the template is not found, PageMaster will show an error if the development mode is enabled.'}
    </em>
</div>
<div class="z-formrow">
    <label for="viewpub_cachelt">{gt text='Cache lifetime'}</label>
    <input id="viewpub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
