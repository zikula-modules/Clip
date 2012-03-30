{ajaxheader module='Clip' filename='Clip.Generator.js' ui=true}

<div class="z-admin-content-pagetitle">
    {img modname='core' src='exec.png' set='icons/small' __title='Show code' alt=''}
    <h3>{$pubtype.title} &raquo; {gt text='Template code'}</h3>
    {clip_adminmenu code=$code}
</div>

{insert name='getstatusmsg'}

<hr />

{clip_copytoclipboard id='clip_generatorcode' class='tooltips'}

{switch expr=$code}
    {case expr='main'}
        <h4>{gt text='Main template'}</h4>
        {assign var='cliptplname' value='main.tpl'}
    {/case}
    {case expr='list'}
        <h4>{gt text='List template'}</h4>
        {assign var='cliptplname' value='list.tpl'}
    {/case}
    {case expr='display'}
        <h4>{gt text='Display template'}</h4>
        {assign var='cliptplname' value='display.tpl'}
    {/case}
    {case expr='edit'}
        <h4>{gt text='Form template'}</h4>
        {assign var='cliptplname' value='form_all.tpl'}
    {/case}
    {case expr='blocklist'}
        <h4>{gt text='List block template'}</h4>
        {assign var='cliptplname' value='list_block.tpl'}
    {/case}
    {case expr='blockpub'}
        <h4>{gt text='Pub block template'}</h4>
        {assign var='cliptplname' value='display_block.tpl'}
    {/case}
{/switch}

{if isset($cliptplname)}
<p class="z-informationmsg">
    {gt text='Create a template file named <strong>%1$s</strong> with this code, and store it in the the config directory: <strong>/config/templates/Clip/%2$s/%1$s</strong>, or within your theme in the <strong>/themes/YourTheme/templates/modules/Clip/%2$s/%1$s</strong>.' tag1=$cliptplname tag2=$pubtype.folder}
</p>
{/if}

{switch expr=$code}
    {case expr='main'}
        {assign var='tag1' value='index.php?module=Clip&type=user&func=main&tid=1&template=welcome'|safehtml}
        {assign var='tpl1' value='main_welcome.tpl'}
        {capture assign='tag2'}{ldelim}clip_func func='main' tid=$cliptids.blog template='categories'{rdelim}{/capture}
        {assign var='tpl2' value='main_categories.tpl'}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>main_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed. For instance, <code>%1$s</code> will use <strong>%2$s</strong>, and <code>%3$s</code> will use <strong>%4$s</strong>.' tag1=$tag1 tag2=$tpl1 tag3=$tag2|safehtml tag4=$tpl2}
        </p>
    {/case}
    {case expr='list'}
        {assign var='tag1' value='index.php?module=Clip&type=user&func=list&tid=2&template=detailed'|safehtml}
        {assign var='tpl1' value='list_detailed.tpl'}
        {capture assign='tag2'}{ldelim}clip_func func='list' tid=$cliptids.pages template='mini'{rdelim}{/capture}
        {assign var='tpl2' value='list_mini.tpl'}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>list_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed. For instance, <code>%1$s</code> will use <strong>%2$s</strong>, and <code>%3$s</code> will use <strong>%4$s</strong>.' tag1=$tag1 tag2=$tpl1 tag3=$tag2|safehtml tag4=$tpl2}
        </p>
    {/case}
    {case expr='display'}
        {assign var='tag1' value='index.php?module=Clip&type=user&func=display&tid=1&pid=5&template=xml'|safehtml}
        {assign var='tpl1' value='display_xml.tpl'}
        {capture assign='tag2'}{ldelim}clip_func func='display' tid=$cliptids.blog pid=5 template='snippet'{rdelim}{/capture}
        {assign var='tpl2' value='display_snippet.tpl'}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>display_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed. For instance, <code>%1$s</code> will use <strong>%2$s</strong>, and <code>%3$s</code> will use <strong>%4$s</strong>.' tag1=$tag1 tag2=$tpl1 tag3=$tag2|safehtml tag4=$tpl2}
        </p>
    {/case}
    {case expr='edit'}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>form_STATE_TEMPLATE.tpl</strong> depending of the publication workflow state and the <var>template</var> parameter passed; or have individual templates according to the current state of the publication workflow <strong>form_STATE.tpl</strong>, for instance: form_initial.tpl, form_approved.tpl, etc.'}
        </p>
    {/case}
    {case expr='blocklist'}
        {assign var='tpl' value='list_block'}
        <p class="z-informationmsg">
            {gt text='If you setup a <var>template</var> in the block, it will require <strong>%s_TEMPLATE.tpl</strong>.' tag1=$tpl}
        </p>
    {/case}
    {case expr='blockpub'}
        {assign var='tpl' value='display_block'}
        <p class="z-informationmsg">
            {gt text='If you setup a <var>template</var> in the block, it will require <strong>%s_TEMPLATE.tpl</strong>.' tag1=$tpl}
        </p>
    {/case}
{/switch}

{* HTML workaround: SCRIPT is the unique that do not scape HTML nor any Chars *}
<script id="clip_generatorcode" type="text/html">{{$output}}</script>

<pre class="clip-generatorcode">
    {$output|safetext}
</pre>
