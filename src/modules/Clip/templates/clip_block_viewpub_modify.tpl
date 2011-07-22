
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
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl' value="$set/display_block_$tpl.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template '%s'." tag1=$tpl}
        {if $vars.tid}
        {modurl modname='Clip' type='admin' func='generator' tid=$vars.tid mode='blocklist' assign='codeurl'}
        <br />{gt text='You can customize the generic code <a href="%s">available here</a>.' tag1=$codeurl|safetext}
        {/if}
        <br />{gt text='If the template is not found, Clip will show an error if the development mode is enabled.'}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_cachelt">{gt text='Cache lifetime'}</label>
    <input id="viewpub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
