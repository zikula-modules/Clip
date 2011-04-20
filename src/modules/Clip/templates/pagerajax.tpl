
{assign var='separator' value='&ndash;'}

<div class="{$pagerPluginArray.class} z-pagercss">
    {if $pagerPluginArray.currentPage > 1}
    <a href="javascript:Zikula.Clip.AjaxRequest({$pagerPluginArray.firstUrl.args|@json_encode|replace:'"':''}, '{$pagerPluginArray.firstUrl.func}')" title="{gt text="First page"}" class="z-pagercss-first">&laquo;</a>
    <a href="javascript:Zikula.Clip.AjaxRequest({$pagerPluginArray.prevUrl.args|@json_encode|replace:'"':''}, '{$pagerPluginArray.prevUrl.func}')" title="{gt text="Previous page"}" class="z-pagercss-prev">&lsaquo;</a>
    {else}
    <span class="z-pagercss-first" title="{gt text="First page"}">&laquo;</span>
    <span class="z-pagercss-prev" title="{gt text="Previous page"}">&lsaquo;</span>
    {/if}

    {foreach name="pages" item="currentPage" key="currentItem" from=$pagerPluginArray.pages}
    {if $currentPage.isCurrentPage}
    <span class="z-pagercss-current">{$currentItem}</span>
    {else}
     <a href="javascript:Zikula.Clip.AjaxRequest({$currentPage.url.args|@json_encode|replace:'"':''}, '{$currentPage.url.func}')" class="z-pagercss-item">{$currentItem}</a>
    {/if}
    {/foreach}

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
    <a href="javascript:Zikula.Clip.AjaxRequest({$pagerPluginArray.nextUrl.args|@json_encode|replace:'"':''}, '{$pagerPluginArray.nextUrl.func}')" title="{gt text="Next page"}" class="z-pagercss-next">&rsaquo;</a>
    <a href="javascript:Zikula.Clip.AjaxRequest({$pagerPluginArray.lastUrl.args|@json_encode|replace:'"':''}, '{$pagerPluginArray.lastUrl.func}')" title="{gt text="Last page"}" class="z-pagercss-last">&raquo;</a>
    {else}
    <span class="z-pagercss-next" title="{gt text="Next page"}">&rsaquo;</span>
    <span class="z-pagercss-last" title="{gt text="Last page"}">&raquo;</span>
    {/if}
</div>
