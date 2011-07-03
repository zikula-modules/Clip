{clip_access tid=$pubtype.tid assign='auth_admin'}

<div class="z-menu">
    <span class="z-menuitem-title clip-breadcrumbs">
        {strip}
        <span class="clip-breadtext">
            <a href="{$baseurl}">{gt text='Home'}</a>
        </span>

        {* action icons *}
        {if $auth_admin}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='admin' func='main' fragment="`$pubtype.tid`/pubtypeinfo"}">
                {img width='12' height='12' modname='core' src='configure.png' set='icons/extrasmall' alt='' __title='Administration panel'}
            </a>
        </span>
        {/if}
        {if $type neq 'editor'}
        {clip_accessblock tid=$pubtype.tid context='editor'}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='editor' func='list' tid=$pubtype.tid}">
                {img width='12' height='12' modname='core' src='lists.png' set='icons/extrasmall' alt='' __title='Editor panel'}
            </a>
        </span>
        {/clip_accessblock}
        {/if}
        {clip_accessblock tid=$pubtype.tid context='submit'}
        <span class="clip-breadlink">
            <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid}">
                {img width='12' height='12' modname='core' src='filenew.png' set='icons/extrasmall' alt='' __title='Submit a publication'}
            </a>
        </span>
        {/clip_accessblock}

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
                {$pubtype.title}
            </span>
        {else}
            {if $func neq 'list'}
                <span class="clip-breadtext">
                    <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid}">
                        {$pubtype.title}
                    </a>
                </span>
            {else}
                <span class="clip-breadtext">
                    <a href="{modurl modname='Clip' type='user' func='main' tid=$pubtype.tid}">
                        {$pubtype.title}
                    </a>
                </span>
            {/if}

            {if $func neq 'list' and !isset($clip_simple_tpl)}
                <span class="text_separator">&raquo;</span>

                {if $func neq 'display'}
                    {* edit check *}
                    {if isset($pubdata.id)}
                    <span class="clip-breadtext">
                        <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink}" title="{$pubdata.core_title}">
                            {$pubdata.core_title|truncate:40}
                        </a>
                    </span>
                    {/if}
                {else}
                    <span class="clip-breadtext" title="{$pubdata.core_title}">
                        {$pubdata.core_title|truncate:40}
                    </span>
                    {clip_accessblock tid=$pubtype.tid pid=$pubdata.core_pid context='edit'}
                    <span class="clip-breadlink">
                        <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubdata.core_tid id=$pubdata.id}">
                            {img width='12' height='12' modname='core' src='edit.png' set='icons/extrasmall' __title='Edit this publication' __alt='Edit'}
                        </a>
                    </span>
                    {/clip_accessblock}
                {/if}

                {if $func neq 'display'}
                    {if isset($pubdata.id)}
                    <span class="text_separator">&raquo;</span>
                    {/if}

                    <span class="clip-breadtext">
                        {if isset($pubdata.id)}
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

{insert name='getstatusmsg'}

{* Clip developer notices *}
{if isset($clip_generic_tpl) and $modvars.Clip.devmode|default:true}
    {* excludes simple templates *}
    {if !isset($clip_simple_tpl)}

    {if $func eq 'display'}{zdebug}{/if}

    {if $auth_admin and ($func eq 'list' or $func eq 'display' or $func eq 'edit')}
    <div class="z-warningmsg">
            {modurl modname='Clip' type='admin' func='modifyconfig' fragment='devmode' assign='urlconfig'}
            {modurl modname='Clip' type='admin' func='generator' code=$func tid=$pubtype.tid assign='urlcode'}
            {gt text='This is a generic template used when <a href="%s">development mode</a> is enabled. You can build your template starting with <a href="%s">the autogenerated code</a>, and reading instructions of where to place it.' tag1=$urlconfig|safetext tag2=$urlcode|safetext}
    </div>
    {/if}

    {/if}
{/if}
