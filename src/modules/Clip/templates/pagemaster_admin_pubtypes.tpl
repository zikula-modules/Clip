{* $Id$ *}

{include file='pagemaster_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='editcopy.gif' set='icons/large' __alt='Publication types'}</div>

    <h2>{gt text='Publication types'}</h2>

    {modurl modname='PageMaster' type='admin' func='pubtype' assign='urlcreate'}
    {assign var='urlcreate' value=$urlcreate|safetext}
    <p>{gt text='Here the list of the existing Publication types (pubtypes). If you don\'t have one yet, go to the <a href="%s">New publication type form</a> and create one; after that, you\'ll be able to add the <strong>Fields</strong> for the publications within that type, and once you have the fields you want, click the corresponding <strong>DB Update</strong> link. Now you will be able to add <strong>New publications</strong> and customize the templates for your pubtype, starting of the code that PageMaster generate for you. Enjoy!' tag1=$urlcreate}</p>

    <table class="z-admintable">
        <thead>
            <tr>
                <th>{gt text='Pubtype'}</th>
                <th>{gt text='Options'}</th>
                <th>{gt text='Show publists'}</th>
                <th>{gt text='Show code'}</th>
            </tr>
        </thead>

        <tbody>
            {foreach from=$pubtypes item='pubtype'}
            <tr class="{cycle values='z-odd,z-even'}">
                <td>
                    <strong>{gt text=$pubtype.title}</strong>
                    {if $pubtype.description neq ''}
                    <br />
                    {gt text=$pubtype.description assign='pubtypedesc'}
                    {$pubtypedesc|truncate:30:'...'}<br />
                    {/if}
                </td>
                <td>
                    <a href="{modurl modname='PageMaster' type='admin' func='pubtype' tid=$pubtype.tid}" title="{gt text='Edit this publication type'}">
                        {img modname='core' src='db_status.gif' set='icons/extrasmall' alt=''} {gt text='Edit'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='admin' func='pubfields' tid=$pubtype.tid}" title="{gt text='Add, edit or modify the fields of this pubtype'}">
                        {img modname='core' src='db_comit.gif' set='icons/extrasmall' alt=''} {gt text='Fields'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='admin' func='dbupdate' tid=$pubtype.tid}" title="{gt text='Update the database table of this pubtype to any recent change in the fields'}">
                        {img modname='core' src='db_update.gif' set='icons/extrasmall' alt=''} {gt text='DB update'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='user' func='edit' tid=$pubtype.tid}" title="{gt text='Add new publications to this pubtype'}">
                        {img modname='core' src='db_add.gif' set='icons/extrasmall' alt=''} {gt text='New pub' comment='Abbreviated text for the admin main screen'}
                    </a>
                </td>
                <td>
                    <a href="{modurl modname='PageMaster' type='admin' func='publist' tid=$pubtype.tid}" title="{gt text='Go to the Admin publication list'}">
                        {img modname='core' src='14_layer_visible.gif' set='icons/extrasmall' alt=''} {gt text='Admin'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='user' func='view' tid=$pubtype.tid}" title="{gt text='Go to the public publication list'}">
                        {img modname='core' src='14_layer_visible.gif' set='icons/extrasmall' alt=''} {gt text='Public'}
                    </a>
                </td>
                <td>
                    <a href="{modurl modname='PageMaster' type='admin' func='showcode' mode='input' tid=$pubtype.tid}" title="{gt text='Get the input form code of this pubtype'}">
                        {img modname='core' src='runprog.gif' set='icons/extrasmall' alt=''} {gt text='Form'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='admin' func='showcode' mode='outputlist' tid=$pubtype.tid}" title="{gt text='Get the publist code of this pubtype'}">
                        {img modname='core' src='runprog.gif' set='icons/extrasmall' alt=''} {gt text='List'}
                    </a>
                    &nbsp;
                    <a href="{modurl modname='PageMaster' type='admin' func='showcode' mode='outputfull' tid=$pubtype.tid}" title="{gt text='Get the pubview code of this pubtype'}">
                        {img modname='core' src='runprog.gif' set='icons/extrasmall' alt=''} {gt text='Display'}
                    </a>
                </td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="4">{gt text='No publication types found.'}</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>
