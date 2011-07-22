
<div class="z-formrow">
    <label for="viewpub_pubtype">{gt text='Publication type'}</label>
    {html_options name='tid' options=$pubtypes selected=$vars.tid}
</div>

<div class="z-formrow">
    <label for="viewpub_orderby">{gt text='Order by'}</label>
    {html_options name='orderBy' options=$pubfields selected=$vars.orderBy}
</div>

<div class="z-formrow">
    <label for="viewpub_filters">{gt text='Filter string'}</label>
    <input id="viewpub_filters" name="listfilter" type="text" size="30" maxlength="255" value="{$vars.listfilter|safetext}" />
</div>

<div class="z-formrow">
    <label for="viewpub_numitems">{gt text='Number of items'}</label>
    <input id="viewpub_numitems" name="listCount" type="text" size="30" maxlength="255" value="{$vars.listCount|safetext}" />
</div>

<div class="z-formrow">
    <label for="viewpub_offset">{gt text='Starting from'}</label>
    <input id="viewpub_offset" name="listOffset" type="text" size="30" maxlength="255" value="{$vars.listOffset|safetext}" />
</div>

<div class="z-formrow">
    <label for="viewpub_template">{gt text='Template'}</label>
    <input id="viewpub_template" name="template" type="text" size="30" maxlength="255" value="{$vars.template|safetext}" />
    {gt text='Template' assign='tpl'}
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl' value="$set/list_block_$tpl.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template '%s'." tag1=$tpl}
        {if $vars.tid}
        {modurl modname='Clip' type='admin' func='showcode' tid=$vars.tid mode='blocklist' assign='codeurl'}
        <br />{gt text='You can customize the generic code <a href="%s">available here</a>.' tag1=$codeurl|safetext}
        {/if}
        <br />{gt text='If the template is not found, clip_generic_blocklist.tpl will be used.'}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_cachelt">{gt text='Cache lifetime'}</label>
    <input id="viewpub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
