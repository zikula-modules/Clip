{ajaxheader filename='clip_tree_grouptypes.js' ui=true}
{pageaddvar name='stylesheet' value='system/Theme/style/pagercss.css'}

{include file='clip_admin_header.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='utilities.png' set='icons/large' __alt='Publications list'}</div>

    <h2>{gt text='Clip Admin Panel'}</h2>

    <div id="clip_cols_container">
        <div id="clip_cols_sidecol">
            <p id="groupControls" class="z-hide">
                <a href="#" id="groupNew">{gt text='Add new'}</a>
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
                <div class="z-informationmsg">{gt text='Click any publication type to get its publication list, or right click the tree elements to manage them.'}</div>
            </div>
        </div>
    </div>
</div>
