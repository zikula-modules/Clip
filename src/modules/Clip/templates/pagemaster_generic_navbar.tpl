
<div class="z-menu">
    <span class="z-menuitem-title pm-menu">
        {strip}
        <span>&raquo;</span>
        {if $section neq 'list'}
            <span>
                <a href="{modurl modname='PageMaster' tid=$pubtype.tid}">
                    {gt text=$pubtype.title}
                </a>
            </span>
        {else}
            <span class="pm-breadtext">
                {gt text=$pubtype.title}
            </span>
        {/if}
        {checkpermissionblock component='pagemaster:input:' instance="`$pubtype.tid`::" level=ACCESS_EDIT}
        <span>
            <a href="{modurl modname='PageMaster' type='user' func='edit' tid=$pubtype.tid}">
                {img width='12' height='12' modname='core' src='filenew.gif' set='icons/extrasmall' alt='' __title='Add a publication'}
            </a>
        </span>
        {/checkpermissionblock}

        {if $section neq 'list' and $section neq 'pending'}
            <span class="text_separator">&raquo;</span>

            {if $section neq 'display'}
                {* edit check *}
                {if isset($id)}
                <span>
                    <a href="{modurl modname='PageMaster' type='user' func='display' tid=$pubtype.tid pid=$core_pid}">
                        {$core_title}
                    </a>
                </span>
                {/if}
            {else}
                <span class="pm-breadtext">
                    {$pubdata[$pubtype.titlefield]}
                </span>
                {checkpermissionblock component='pagemaster:input:' instance="`$pubtype.tid`::" level=ACCESS_ADD}
                <span>
                    <a href="{modurl modname='PageMaster' type='user' func='edit' tid=$pubdata.core_tid pid=$pubdata.core_pid}">
                        {img width='12' height='12' modname='core' src='edit.gif' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                    </a>
                </span>
                {/checkpermissionblock}
            {/if}

            {if $section neq 'display'}
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
{if isset($clip_generic_tpl) and $zcore.PageMaster.devmode|default:true}
    {* excludes simple templates *}
    {if $section neq 'pending'}

    {if $section eq 'display'}{zdebug}{/if}

    {checkpermissionblock component='pagemaster::' instance='::' level=ACCESS_ADMIN}
    <div class="z-warningmsg">
        {switch expr=$section}
            {case expr='list'}
                {modurl modname='PageMaster' type='admin' func='showcode' mode='outputlist' tid=$pubtype.tid assign='urlplcode'}
                {gt text='This is a generic template. Your can <a href="%1$s">get the list template code</a> of this publication type, create the <strong>list.tpl</strong> file, customized it, and store it in the the config directory: <strong>/config/templates/PageMaster/%2$s/list.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/%2$s/list.tpl</strong>.' tag1=$urlplcode|safetext tag2=$pubtype.outputset|safetext}
            {/case}
            {case expr='display'}
                {modurl modname='PageMaster' type='admin' func='showcode' mode='outputfull' tid=$pubtype.tid assign='urlpvcode'}
                {gt text='This is a generic template. Your can <a href="%1$s">get the display template code</a> of this publication type, create the <strong>display.tpl</strong> file, customize it, and store it in the the config directory: <strong>/config/templates/PageMaster/%2$s/display.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/%2$s/display.tpl</strong>.' tag1=$urlpvcode|safetext tag2=$pubtype.outputset|safetext}
            {/case}
            {case expr='form'}
                {modurl modname='PageMaster' type='admin' func='showcode' mode='input' tid=$pubtype.tid assign='urlpecode'}
                {gt text='This is a generic template. Your can <a href="%1$s">get the form template code</a> of this publication type, and create individual templates (<strong>form_<em>STEPNAME</em>.tpl</strong> or a general <strong>form_all.tpl</strong>), then store them in the the config directory: <strong>/config/templates/PageMaster/%2$s/form_<em>STEPNAME</em>.tpl</strong> or within your theme: <strong>/templates/modules/PageMaster/%2$s/form_<em>STEPNAME</em>.tpl</strong>.' tag1=$urlpecode|safetext tag2=$pubtype.inputset|safetext}
            {/case}
        {/switch}
        {modurl modname='PageMaster' type='admin' func='modifyconfig' assign='urlconfig'}
        <br /><br />
        {gt text='You can hide this message <a href="%s">disabling the development mode</a>.' tag1=$urlconfig|safetext}
    </div>
    {/checkpermissionblock}

    {/if}
{/if}
