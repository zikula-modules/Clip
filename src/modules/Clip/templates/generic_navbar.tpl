{clip_access assign='auth_admin'}

{* breadcrumbs *}
<div class="z-menu">
    <span class="z-menuitem-title clip-breadcrumbs">
        {strip}
        <span class="clip-breadtext">
            <a href="{$baseurl}">{gt text='Home'}</a>
        </span>

        {* action icons *}
        {if $auth_admin and $pubtype|default:false}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='admin' func='main' fragment="`$pubtype.tid`/pubtypeinfo"}">
                {img width='12' height='12' modname='core' src='configure.png' set='icons/extrasmall' alt='' __title='Administration panel'}
            </a>
        </span>
        {/if}
        {if $type neq 'editor'}
        {clip_accessblock context='editor'}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='editor' func='list' tid="`$pubtype.tid`"}">
                {img width='12' height='12' modname='core' src='lists.png' set='icons/extrasmall' alt='' __title='Editor panel'}
            </a>
        </span>
        {/clip_accessblock}
        {/if}
        {if $pubtype|default:false}
        {clip_accessblock context='submit'}
        <span class="clip-breadlink">
            <a href="{clip_url type='user' func='edit'}">
                {img width='12' height='12' modname='core' src='filenew.png' set='icons/extrasmall' alt='' __title='Submit a publication'}
            </a>
        </span>
        {/clip_accessblock}
        {/if}

        {if $type eq 'editor'}
            <span class="text_separator">&raquo;</span>

            {if $func neq 'main'}
                <span class="clip-breadtext">
                    <a href="{modurl modname='Clip' type='editor' func='main'}">
                        {gt text="Editor's Panel"}
                    </a>
                </span>
            {else}
                <span class="clip-breadtext">
                    {gt text="Editor's Panel"}
                </span>
            {/if}
        {/if}

        <span class="text_separator">&raquo;</span>

        {if $func eq 'main'}
            <span class="clip-breadtext">
                {$pubtype.title|safetext}
            </span>
        {else}
            {if $func neq 'list'}
                <span class="clip-breadtext">
                    <a href="{clip_url func='list'}">
                        {$pubtype.title|safetext}
                    </a>
                </span>
            {else}
                <span class="clip-breadtext">
                    <a href="{clip_url func='main'}">
                        {$pubtype.title|safetext}
                    </a>
                </span>
            {/if}

            {if $func neq 'list' and !isset($clip_simple_tpl)}
                <span class="text_separator">&raquo;</span>

                {if $func neq 'display'}
                    {* edit check *}
                    {if $pubdata.id}
                    <span class="clip-breadtext">
                        <a href="{clip_url func='display' pub=$pubdata}" title="{$pubdata.core_title|safetext}">
                            {$pubdata.core_title|truncate:40|safetext}
                        </a>
                    </span>
                    {/if}
                {else}
                    <span class="clip-breadtext" title="{$pubdata.core_title}">
                        {$pubdata.core_title|truncate:40|safetext}
                    </span>
                    {clip_accessblock pub=$pubdata context='edit'}
                    <span class="clip-breadlink">
                        <a href="{clip_url func='edit' pub=$pubdata}">
                            {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit this publication' __alt='Edit'}
                        </a>
                    </span>
                    {/clip_accessblock}
                {/if}

                {if $func neq 'display'}
                    {if $pubdata.id}
                    <span class="text_separator">&raquo;</span>
                    {/if}

                    <span class="clip-breadtext">
                        {if $pubdata.id}
                            {gt text='Edit'}
                        {else}
                            {gt text='Submit'}
                        {/if}
                    </span>
                {/if}
            {/if}
        {/if}
        {/strip}
    </span>
</div>

{* resolves the page title *}
{if !$homepage}
    {assign var='pageseparator' value=' - '}
    {assign var='pagetitle' value=''}

    {switch expr=$func}
        {case expr='edit'}
            {if $pubdata.id}
                {assign_concat name='pagetitle' __1='Edit' 2=$pageseparator 3=$pubdata.core_title 4=$pageseparator 5=$pubtype.title 6=$pageseparator}
            {else}
                {assign_concat name='pagetitle' __1='Submit' 2=$pageseparator 3=$pubtype.title 4=$pageseparator}
            {/if}
        {/case}
        {case expr='display'}
            {if $pubdata.id}
                {assign_concat name='pagetitle' 1=$pubdata.core_title 2=$pageseparator 3=$pubtype.title 4=$pageseparator}
            {/if}
        {/case}
        {case expr='list'}
            {* here can be a filter title *}
            {assign_concat name='pagetitle' 1=$pubtype.title 2=$pageseparator}
        {/case}
        {case expr='main'}
            {assign_concat name='pagetitle' 1=$pubtype.title 2=$pageseparator}
        {/case}
    {/switch}
    {assign_concat name='pagetitle' 1=$pagetitle 2=$modvars.ZConfig.defaultpagetitle}

    {pagesetvar name='title' value=$pagetitle}
{/if}

{* status messages *}
{insert name='getstatusmsg'}

{* Clip developer notices *}
{if isset($clip_generic_tpl) and $modvars.Clip.devmode|default:true}
    {* excludes simple templates *}
    {if !isset($clip_simple_tpl)}

    {if $func eq 'display'}{zdebug}{/if}

    {if $auth_admin and ($func eq 'main' or $func eq 'list' or $func eq 'display' or $func eq 'edit')}
    <div class="z-warningmsg">
        {modurl modname='Clip' type='admin' func='modifyconfig' fragment='devmode' assign='urlconfig'}
        {modurl modname='Clip' type='admin' func='generator' code=$func assign='urlcode'}
        {gt text='This is a generic template used when <a href="%1$s">development mode</a> is enabled. You can build your template starting with <a href="%2$s">the autogenerated code</a>, and reading instructions of where to place it.' tag1=$urlconfig|safetext tag2=$urlcode|safetext}
    </div>
    {/if}

    {/if}
{/if}
