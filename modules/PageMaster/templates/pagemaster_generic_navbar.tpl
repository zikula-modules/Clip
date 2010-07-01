{* $Id: $ *}

<div class="z-menu">
    <span class="z-menuitem-title pm-menu">
        {strip}
        <span>&raquo;</span>
        {if $section neq 'publist'}
        <span>
            <a href="{modurl modname='PageMaster' tid=$pubtype.tid}">
                {gt text=$pubtype.title}
            </a>
        </span>
        {else}
        <span class="pm-breadtext">
            {gt text=$pubtype.title}
        </span>
        {secauthaction_block component='pagemaster:input:' instance="$pubtype.tid::" level=ACCESS_EDIT}
        <span>
            <a href="{modurl modname='PageMaster' type='user' func='pubedit' tid=$pubtype.tid}">
                {img width='12' height='12' modname='core' src='filenew.gif' set='icons/extrasmall' alt='' __title='Add a publication'}
            </a>
        </span>
        {/secauthaction_block}
        {/if}

        {if $section neq 'publist' and $section neq 'pending'}
        <span class="text_separator">&raquo;</span>

        {if $section neq 'pubview'}
        {if isset($id)}
        <span>
            <a href="{modurl modname='PageMaster' type='user' func='viewpub' tid=$pubtype.tid pid=$core_pid}">
                {$title}
            </a>
        </span>
        {/if}
        {else}
        <span class="pm-breadtext">
            {$title}
        </span>
        {secauthaction_block component='pagemaster:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
        <span>
            <a href="{modurl modname='PageMaster' type='user' func='pubedit' tid=$pubtype.tid pid=$core_pid}">
                {img width='12' height='12' modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
            </a>
        </span>
        {/secauthaction_block}
        {/if}

        {if $section neq 'pubview'}
        {if isset($id)}
        <span class="text_separator">&raquo;</span>
        {/if}

        <span class="pm-breadtext">
            {if isset($id)}
            {gt text='Edit form'}
            {else}
            {gt text='Create form'}
            {/if}
        </span>
        {/if}
        {/if}
        {/strip}
    </span>
</div>

{insert name='getstatusmsg'}

{* PageMaster developer notices*}
{if $pncore.PageMaster.devmode|default:true}
{if $section eq 'pubview'}{debug}{/if}

{secauthaction_block component='pagemaster::' instance='::' level=ACCESS_ADMIN}
<div class="z-warningmsg">
    {switch expr=$section}
    {case expr='publist'}
    {modurl modname='PageMaster' type='admin' func='showcode' mode='outputlist' tid=$pubtype.tid assign='urlplcode'}
    {gt text='This is a generic template. Your can <a href="%1$s">get the publist code</a> and create a customized template (<strong>publist_%2$s.tpl</strong>), then store it in the the config directory: <strong>/config/templates/PageMaster/output/publist_%2$s.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/output/publist_%2$s.tpl</strong>.' tag1=$urlplcode|safetext tag2=$pubtype.filename|safetext}
    {/case}
    {case expr='pubview'}
    {modurl modname='PageMaster' type='admin' func='showcode' mode='outputfull' tid=$pubtype.tid assign='urlpvcode'}
    {gt text='This is a generic template. Your can <a href="%1$s">get the pubview code</a> and create a customized template (<strong>viewpub_%2$s.tpl</strong>), then store it in the the config directory: <strong>/config/templates/PageMaster/output/viewpub_%2$s.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/output/viewpub_%2$s.tpl</strong>.' tag1=$urlpvcode|safetext tag2=$pubtype.filename|safetext}
    {/case}
    {case expr='pubedit'}
    {modurl modname='PageMaster' type='admin' func='showcode' mode='input' tid=$pubtype.tid assign='urlpecode'}
    {gt text='This is a generic template. Your can <a href="%1$s">get the form code</a> and create individual templates (<strong>pubedit_%2$s_STEPNAME.tpl</strong> or <strong>pubedit_%2$s_all.tpl</strong>), then store it in the the config directory: <strong>/config/templates/PageMaster/input/pubedit_%2$s_STEPNAME.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/input/pubedit_%2$s_STEPNAME.tpl</strong>.' tag1=$urlpecode|safetext tag2=$pubtype.formname|safetext}
    {/case}
    {/switch}
    {modurl modname='PageMaster' type='admin' func='modifyconfig' assign='urlconfig'}
    {assign var='urlconfig' value=$urlconfig|safetext}
    <br />
    {gt text='You can hide this message <a href="%s">disabling the development mode</a>.' tag1=$urlconfig|safetext}
</div>
{/secauthaction_block}
{/if}
