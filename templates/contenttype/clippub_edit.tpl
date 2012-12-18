
<div class="z-formrow">
    {formlabel for='tid' __text='Publication type'}
    {clip_content_pubtypes id='tid' group='data' mandatory=true}
</div>

<div class="z-formrow">
    {formlabel for='pid' __text='Publication PID'}
    {formtextinput id='pid' group='data' maxLength='5'}
</div>

<div class="z-formrow">
    {formlabel for='tpl' __text='Template'}
    {formtextinput id='tpl' group='data' maxLength='255'}
    {gt text='Template' assign='tpl'}
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl1' value="$set/display_$tpl.tpl"}
    {assign var='tpl2' value="$set/display_block.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template <var>%s</var> if available, or <var>%s</var> if empty." tag1=$tpl1 tag2=$tpl2}
        {if $data.tid}
        {modurl modname='Clip' type='admin' func='generator' tid=$data.tid code='blockpub' assign='codeurl'}
        <br />{gt text='You can customize the generic code <a href="%s">available here</a>.' tag1=$codeurl|safetext}
        {/if}
        <br />{gt text='If the template is not found, Clip will show an error if the development mode is not enabled.'}
    </em>
</div>

<div class="z-formrow">
    {formlabel for='clt' __text='Cache lifetime in seconds'}
    {formtextinput id='clt' group='data' maxLength='255'}
</div>
