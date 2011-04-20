
{pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=9 processUrls=false useThemeView=false template='pagerajax.tpl'}

<table class="z-admintable">
    <thead>
        <tr>
            <th>
                <a class="{$pubtype.orderby|clip_orderby:'core_pid':'class'}" href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, orderby:'{$pubtype.orderby|clip_orderby:'core_pid'}'{rdelim}, 'publist')">
                    {gt text='PID'}
                </a>
            </th>
            <th>
                <a class="{$pubtype.orderby|clip_orderby:'core_title':'class'}" href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, orderby:'{$pubtype.orderby|clip_orderby:'core_title'}'{rdelim}, 'publist')">
                    {gt text='Title'}
                </a>
            </th>
            <th>
                {gt text='Revision'}
            </th>
            <th>
                {gt text='State'}
            </th>
            <th>
                <a class="{$pubtype.orderby|clip_orderby:'core_author':'class'}" href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, orderby:'{$pubtype.orderby|clip_orderby:'core_author'}'{rdelim}, 'publist')">
                    {gt text='Author'}
                </a>
            </th>
            <th>
                {gt text='Online'}
            </th>
            <th>
                <a class="{$pubtype.orderby|clip_orderby:'cr_date':'class'}" href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, orderby:'{$pubtype.orderby|clip_orderby:'cr_date'}'{rdelim}, 'publist')">
                    {gt text='Creation date'}
                </a>
            </th>
            <th>
                <a class="{$pubtype.orderby|clip_orderby:'lu_date':'class'}" href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, orderby:'{$pubtype.orderby|clip_orderby:'lu_date'}'{rdelim}, 'publist')">
                    {gt text='Update date'}
                </a>
            </th>
            <th>
                {gt text='Options'}
            </th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$publist item='pubitem'}
        <tr class="{cycle values='z-odd,z-even'}">
            <td>
                {$pubitem.core_pid|safetext}
            </td>
            <td>
                <strong>{$pubitem[$pubtype.titlefield]|safetext}</strong>
            </td>
            <td>
                {$pubitem.core_revision|safetext}
            </td>
            <td class="z-sub">
                {$pubitem.__WORKFLOW__.state}
            </td>
            <td class="z-sub">
                <a href="{modurl modname='Users' type='admin' func='modify' userid=$pubitem.core_author}">
                    {usergetvar name="uname" uid=$pubitem.core_author}
                </a>
            </td>
            <td>
                {$pubitem.core_online|yesno}
            </td>
            <td class="z-sub">
                {$pubitem.cr_date|dateformat:'datetimebrief'}
            </td>
            <td class="z-sub">
                {$pubitem.lu_date|dateformat:'datetimebrief'}
            </td>
            <td>
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid id=$pubitem.id goto='referer'}" title="{gt text='Edit'}">{img modname='core' src='xedit.png' set='icons/extrasmall' __title='Edit'}</a>&nbsp;
                <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, id:{$pubitem.id}{rdelim}, 'display')" title="{gt text='View'}">{img modname='core' src='demo.png' set='icons/extrasmall' __title='View'}</a>&nbsp;
                <a href="javascript:Zikula.Clip.AjaxRequest({ldelim}tid:{$pubtype.tid}, pid:{$pubitem.core_pid}{rdelim}, 'history')" title="{gt text='History'}">{img modname='core' src='clock.png' set='icons/extrasmall' __title='History'}</a>
            </td>
        </tr>
        {foreachelse}
        <tr class="z-admintableempty">
            <td colspan="9">{gt text='No publications found.'}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
