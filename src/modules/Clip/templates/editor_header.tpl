{clip_access assign='auth_admin'}
{clip_access permlvl=ACCESS_ADMIN assign='clip_admin'}

{switch expr=$func}
    {case expr='main'}
        <div class="clip-editorpanelicon">{img modname='core' src='package_editors.png' set='icons/large' alt=''}</div>
    {/case}
    {case expr='list'}
        <div class="clip-editorpanelicon">{img modname='core' src='lists.png' set='icons/large' alt=''}</div>
    {/case}
{/switch}

{* hidden as navbar includes an icon to the Admin Panel
    <div id="clip_editor_buttons" class="z-floatright z-buttons">
        <a class="clip-bt-configure" title="{gt text='Switch to the admin panel'}" href="{modurl modname='Clip' type='admin' func='main'}">
            {gt text='Admin Panel'}
        </a>
    </div>
*}

<h2>{$pagetitle}</h2>

{* breadcrumbs *}
<div class="z-menu">
    <span class="z-menuitem-title clip-breadcrumbs">
        {strip}
        <span class="clip-breadtext">
            <a href="{$baseurl}">{gt text='Home'}</a>
        </span>

        {* action icons *}
        {if $clip_admin}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='admin' func='main'}">
                {img width='12' height='12' modname='core' src='configure.png' set='icons/extrasmall' alt='' __title='Administration panel'}
            </a>
        </span>
        {/if}
        {if $pubtype.tid|default:false}
        {if !$clip_admin and $auth_admin}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='admin' func='main' fragment="`$pubtype.tid`/pubtypeinfo"}">
                {img width='12' height='12' modname='core' src='configure.png' set='icons/extrasmall' alt='' __title='Administration panel'}
            </a>
        </span>
        {/if}
        {clip_accessblock context='submit'}
        <span class="clip-breadlink">
            <a href="{clip_url type='user' func='edit'}">
                {img width='12' height='12' modname='core' src='filenew.png' set='icons/extrasmall' alt='' __title='Submit a publication'}
            </a>
        </span>
        {/clip_accessblock}
        {/if}

        <span class="text_separator">&raquo;</span>

        {if $func eq 'main'}
            <span class="clip-breadtext">
                {gt text="Editor's Panel"}
            </span>
        {else}
            <span class="clip-breadtext">
                <a href="{modurl modname='Clip' type='editor' func='main'}">
                    {gt text="Editor's Panel"}
                </a>
            </span>

            <span class="text_separator">&raquo;</span>

            <span class="clip-breadtext">
                {$pubtype.title|safetext}
            </span>
        {/if}
        {/strip}
    </span>
</div>

{* resolves the page title *}
{assign var='pageseparator' value=' - '}
{assign var='pagetitle' value=''}

{switch expr=$func}
    {case expr='main'}
        {assign_concat name='pagetitle' __1='Editor Panel' 2=$pageseparator}
    {/case}
    {case expr='list'}
        {* here can be a filter title *}
        {assign_concat name='pagetitle' 1=$pubtype.title 2=$pageseparator}
    {/case}
{/switch}
{assign_concat name='pagetitle' 1=$pagetitle 2=$modvars.ZConfig.defaultpagetitle}

{pagesetvar name='title' value=$pagetitle}

{* status messages *}
{insert name='getstatusmsg'}
