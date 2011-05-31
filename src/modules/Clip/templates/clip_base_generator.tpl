
<h2>{$pubtype.title} &raquo; {gt text='Template code'}</h2>

{clip_submenu tid=$pubtype.tid code=$code}

<hr />

{switch expr=$code}
    {case expr='list'}
        <h3>{gt text='List template'}</h3>
        {assign var='cliptplname' value='list.tpl'}
        {assign var='clipfolder' value=$pubtype.outputset}
    {/case}
    {case expr='display'}
        <h3>{gt text='Display template'}</h3>
        {assign var='cliptplname' value='display.tpl'}
        {assign var='clipfolder' value=$pubtype.outputset}
    {/case}
    {case expr='form'}
        <h3>{gt text='Form template'}</h3>
        {assign var='cliptplname' value='form_all.tpl'}
        {assign var='clipfolder' value=$pubtype.inputset}
    {/case}
    {case expr='blocklist'}
        <h3>{gt text='List block template'}</h3>
        {assign var='cliptplname' value='list_block_XYZ.tpl'}
        {assign var='clipfolder' value=$pubtype.outputset}
    {/case}
    {case expr='blockpub'}
        <h3>{gt text='Pub block template'}</h3>
        {assign var='cliptplname' value='display_block_XYZ.tpl'}
        {assign var='clipfolder' value=$pubtype.outputset}
    {/case}
{/switch}

{if isset($cliptplname) AND isset($clipfolder)}
<p class="z-informationmsg">
    {gt text='Create a template file named <strong>%1$s</strong> with this code, and store it in the the config directory: <strong>/config/templates/Clip/%2$s/%1$s</strong>, or within your theme in the <strong>/themes/YourTheme/templates/modules/Clip/%2$s/%1$s</strong>.' tag1=$cliptplname tag2=$clipfolder}
</p>
{/if}

{switch expr=$code}
    {case expr='list'}
        {assign var='tag1' value='index.php?module=Clip&type=user&func=list&tid=1&template=categories'|safehtml}
        {capture assign='tag2'}{ldelim}modfunc modname='Clip' type='user' func='list' tid=1 template='snippet'{rdelim}{/capture}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>list_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed. For instance, <code>%1$s</code> will use <strong>list_categories.tpl</strong>, and <code>%2$s</code> will use <strong>list_snippet.tpl</strong>.' tag1=$tag1 tag2=$tag2|safehtml}
        </p>
    {/case}
    {case expr='display'}
        {assign var='tag1' value='index.php?module=Clip&type=user&func=display&tid=1&pid=1&template=mini'|safehtml}
        {capture assign='tag2'}{ldelim}modfunc modname='Clip' type='user' func='display' tid=1 pid=1 template='snippet'{rdelim}{/capture}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>display_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed. For instance, <code>%1$s</code> will use <strong>display_mini.tpl</strong>, and <code>%2$s</code> will use <strong>display_snippet.tpl</strong>.' tag1=$tag1 tag2=$tag2|safehtml}
        </p>
    {/case}
    {case expr='form'}
        <p class="z-informationmsg">
            {gt text='You can also have custom templates like <strong>form_custom_TEMPLATE.tpl</strong> depending of the <var>template</var> parameter passed; or have individual templates according to the current state of the publication workflow <strong>form_STATE.tpl</strong>, for instance: form_initial.tpl, form_approved.tpl, etc.'}
        </p>
    {/case}
    {case expr='blocklist'}
        <p class="z-informationmsg">
            {gt text='<strong>XYZ</strong> is the template name chosen on your block configuration.'}
        </p>
    {/case}
    {case expr='blockpub'}
        <p class="z-informationmsg">
            {gt text='<strong>XYZ</strong> is the template name chosen on your block configuration.'}
        </p>
    {/case}
{/switch}

<pre class="clip-generatorcode">{$output}</pre>