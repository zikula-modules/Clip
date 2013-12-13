
<h3>{gt text='History'}</h3>

{clip_adminmenu}

<table class="z-admintable">
    <thead>
        <tr>
            <th>{gt text='PID'}</th>
            <th>{gt text='Title'}</th>
            <th>{gt text='Revision'}</th>
            <th>{gt text='State'}</th>
            <th>{gt text='Author'}</th>
            <th>{gt text='Online'}</th>
            <th>{gt text='In depot'}</th>
            <th>{gt text='Update date'}</th>
            <th>{gt text='Options'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$publist item='pubitem'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                {$pubitem.core_pid}
            </td>
            <td>
                {$pubitem.core_title|safetext}
            </td>
            <td>
                {$pubitem.core_revision}
            </td>
            <td class="z-sub">
                {$pubitem.__WORKFLOW__.state}
            </td>
            <td class="z-sub">
                {usergetvar name='uname' uid=$pubitem.core_author}
            </td>
            <td>
                {$pubitem.core_online|yesno}
            </td>
            <td>
                {$pubitem.core_intrash|yesno}
            </td>
            <td class="z-sub">
                {$pubitem.lu_date|dateformat:'datetimebrief'}
            </td>
            <td>
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid id=$pubitem.id goto='referer'}" title="{gt text='Edit'}">{img modname='core' src='xedit.png' set='icons/extrasmall' __title='Edit'}</a>&nbsp;
            </td>
        </tr>
        {foreachelse}
        <tr class="z-admintableempty">
            <td colspan="7">{gt text='No publications found.'}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
