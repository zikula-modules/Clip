
<div class="z-formrow">
    {formlabel for='tid' __text='Publication type'}
    {clip_content_pubtypes id='tid' group='data' mandatory=true}
</div>

<div class="z-formrow">
    {formlabel for='orderby' __text='Order by'}
    {clip_content_pubfields id='orderby' group='data'}
    <div class="z-formnote">
        {formcheckbox id='orderdir' group='data'} {gt text='Sort ascending'}
    </div>
</div>

<div class="z-formrow">
    {formlabel for='filter' __text='Filter'}
    {formtextinput id='filter' group='data' maxLength='255'}
    <em class="z-formnote">
        {gt text='A <a href="%s">filter string</a> for FilterUtil (e.g. "core_title:likefirst:A").' tag1='https://github.com/zikula/core/blob/release-1.3/src/lib/util/FilterUtil/docs/users.markdown'}
    </em>
</div>

<div class="z-formrow">
    {formlabel for='numitems' __text='Number of items'}
    {formtextinput id='numitems' group='data' maxLength='5'}
    <em class="z-formnote">
        {gt text="Leave empty to use the pubtype's default value."}
    </em>
</div>

<div class="z-formrow">
    {formlabel for='offset' __text='Starting from'}
    {formtextinput id='offset' group='data' maxLength='5'}
    <em class="z-formnote">
        {gt text='Offset to start the list from.'}
    </em>
</div>

<div class="z-formrow">
    {formlabel for='tpl' __text='Template'}
    {formtextinput id='tpl' group='data' maxLength='255'}
    {gt text='Template' assign='tpl'}
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl1' value="$set/list_$tpl.tpl"}
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
    {formlabel for='clt' __text='Cache lifetime in seconds'}
    {formtextinput id='clt' group='data' maxLength='255'}
</div>
