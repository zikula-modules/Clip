
<div class="z-formrow">
    <label for="viewpub_pubtype">{gt text='Publication type'}</label>
    {html_options name='tid' options=$pubtypes selected=$vars.tid}
</div>

<div class="z-formrow">
    <label for="viewpub_orderby">{gt text='Order by'}</label>
    {html_options name='orderBy' options=$pubfields selected=$vars.orderBy}
    <div class="z-formnote">
        <input type="checkbox" class="z-form-checkbox" value="1" name="orderDir"{if $vars.orderDir} checked="checked"{/if}> {gt text='Sort ascending'}
    </div>
</div>

<div class="z-formrow">
    <label for="viewpub_filters">{gt text='Filter string'}</label>
    <input id="viewpub_filters" name="listfilter" type="text" size="30" maxlength="255" value="{$vars.listfilter|safetext}" />
    <em class="z-formnote">
        {gt text='A <a href="%s">filter string</a> for FilterUtil (e.g. "core_title:likefirst:A")' tag1='https://github.com/zikula/core/blob/release-1.3/src/lib/util/FilterUtil/docs/users.markdown'}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_numitems">{gt text='Number of items'}</label>
    <input id="viewpub_numitems" name="listCount" type="text" size="30" maxlength="255" value="{$vars.listCount|safetext}" />
    <em class="z-formnote">
        {gt text="Leave empty to use the pubtype's default value."}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_offset">{gt text='Starting from'}</label>
    <input id="viewpub_offset" name="listOffset" type="text" size="30" maxlength="255" value="{$vars.listOffset|safetext}" />
    <em class="z-formnote">
        {gt text='Offset to start the list from.'}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_template">{gt text='Template'}</label>
    <input id="viewpub_template" name="template" type="text" size="30" maxlength="255" value="{$vars.template|safetext}" />
    {gt text='Template' assign='tpl'}
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl1' value="$set/list_block_$tpl.tpl"}
    {assign var='tpl2' value="$set/list_block.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template <var>%s</var> if available, or <var>%s</var> if empty." tag1=$tpl1 tag2=$tpl2}
        {if $vars.tid}
        {modurl modname='Clip' type='admin' func='showcode' tid=$vars.tid code='blocklist' assign='codeurl'}
        <br />{gt text='You can customize the generic code <a href="%s">available here</a>.' tag1=$codeurl|safetext}
        {/if}
        <br />{gt text='If the template is not found, Clip will use generic_blocklist.tpl if the development mode is enabled.'}
    </em>
</div>

<div class="z-formrow">
    <label for="viewpub_cachelt">{gt text='Cache lifetime in seconds'}</label>
    <input id="viewpub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
