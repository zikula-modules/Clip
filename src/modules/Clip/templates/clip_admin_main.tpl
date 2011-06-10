{pageaddvar name='stylesheet' value='system/Theme/style/pagercss.css'}
{ajaxheader filename='Zikula.Clip.Container.js' ui=true}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">

    <div id="clip_cols_container">
        <div id="clip_cols_sidecol">
            <p id="groupControls" class="z-hide">
                <a href="#" id="groupNew">{gt text='Add group'}</a>
                |
                <a href="#" id="groupExpand">{gt text='Expand'}</a>
                |
                <a href="#" id="groupCollapse">{gt text='Collapse'}</a>
            </p>
            {$treejscode}
        </div>

        <div id="clip_cols_maincol">
            <div id="clip_cols_indicator" class="z-window-indicator" style="display: none"></div>
            <div id="clip_cols_maincontent">
                <div class="z-informationmsg">{gt text='Right click the tree elements to manage them.'}</div>
                <p>{gt text='Click on a publication type to get its details and available options.'}</p>
                {modurl modname='Clip' type='admin' func='pubtype' assign='urlcreate'}
                <p>{gt text='You can also create a new <a href="%s"><strong>Publication Type</strong></a> and after that, you will be able to add <strong>Fields</strong> to it, to define the data of the publications within that Publication Type. Then you will be able to add new <strong>Publications</strong> and customize the templates for them, starting with the code that Clip generates for you. Enjoy!' tag1=$urlcreate|safetext}</p>
            </div>
        </div>
    </div>

    <div class="z-right">
        <span class="z-sub">Clip  v{$modinfo.version}</span>
    </div>
</div>
