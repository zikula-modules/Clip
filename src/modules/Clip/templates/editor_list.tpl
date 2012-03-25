{pageaddvar name='stylesheet' value='system/Theme/style/pagercss.css'}
{gt text="Editor's List" assign='pagetitle'}
{pagesetvar name='title' value="`$pagetitle` - `$modvars.ZConfig.defaultpagetitle`"}
{assign var='pagetitle' value="`$pubtype.title` &raquo; `$pagetitle`"}

<div class="clip-editorpanel">
    {include file='editor_header.tpl'}

    <table class="z-datatable">
        <thead>
            <tr>
                <th class="z-w05 z-center">
                    <a href="{clip_url func='list' orderby=$clipargs.getallapi.orderby|clip_orderby:'core_pid'}" class="{$clipargs.getallapi.orderby|clip_orderby:'core_pid':'class'}">
                        {gt text='PID'}
                    </a>
                </th>
                <th>
                    <a href="{clip_url func='list' orderby=$clipargs.getallapi.orderby|clip_orderby:'core_title'}" class="{$clipargs.getallapi.orderby|clip_orderby:'core_title':'class'}">
                        {gt text='Title'}
                    </a>
                </th>
                <th>
                    <a href="{clip_url func='list' orderby=$clipargs.getallapi.orderby|clip_orderby:'core_author'}" class="{$clipargs.getallapi.orderby|clip_orderby:'core_author':'class'}">
                        {gt text='Author'}
                    </a>
                </th>
                <th>
                    {gt text='Online'}
                </th>
                <th class="z-w15 z-right">
                    <a href="{clip_url func='list' orderby=$clipargs.getallapi.orderby|clip_orderby:'cr_date'}" class="{$clipargs.getallapi.orderby|clip_orderby:'cr_date':'class'}">
                        {gt text='Creation date'}
                    </a>
                </th>
                <th class="z-w15 z-right">
                    <a href="{clip_url func='list' orderby=$clipargs.getallapi.orderby|clip_orderby:'lu_date'}" class="{$clipargs.getallapi.orderby|clip_orderby:'lu_date':'class'}">
                        {gt text='Last modified'}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$publist item='pubitem'}
            <tr class="{cycle values='z-even,z-odd'}">
                <td class="z-center">
                    {$pubitem.core_pid|safetext}
                </td>
                <td>
                    <strong><a href="{clip_url type='user' func='display' pub=$pubitem}">{$pubitem.core_title|safetext}</a></strong>
                    {clip_editoractions pub=$pubitem}
                </td>
                <td class="z-sub">
                    <a href="{modurl modname='Users' type='admin' func='modify' userid=$pubitem.core_author}">
                        {usergetvar name='uname' uid=$pubitem.core_author}
                    </a>
                </td>
                <td>
                    {$pubitem.core_online|yesno}
                </td>
                <td class="z-sub z-right">
                    {$pubitem.cr_date|dateformat:'datebrief'}
                </td>
                <td class="z-sub z-right">
                    {$pubitem.lu_date|dateformat:'datebrief'}
                    <br />
                    {$pubitem.__WORKFLOW__.statetitle}
                </td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty">
                <td colspan="9">{gt text='No publications found.'}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=9 owner='Clip' template='pager_default.tpl' processUrls=false}
</div>
