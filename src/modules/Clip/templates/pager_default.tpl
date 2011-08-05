{assign var='separator' value='&ndash;'}

<div class="{$pagerPluginArray.class} z-pagercss">
    {if $pagerPluginArray.currentPage > 1}
    <a href="{clip_url func=$pagerPluginArray.firstUrl.func args=$pagerPluginArray.firstUrl.args}" title="{gt text="First page"}" class="z-pagercss-first">&laquo;</a>
    <a href="{clip_url func=$pagerPluginArray.prevUrl.func args=$pagerPluginArray.prevUrl.args}" title="{gt text="Previous page"}" class="z-pagercss-prev">&lsaquo;</a>
    {else}
    <span class="z-pagercss-first" title="{gt text="First page"}">&laquo;</span>
    <span class="z-pagercss-prev" title="{gt text="Previous page"}">&lsaquo;</span>
    {/if}

    {foreach name="pages" item="currentPage" key="currentItem" from=$pagerPluginArray.pages}
    {if $currentPage.isCurrentPage}
    <span class="z-pagercss-current">{$currentItem}</span>
    {else}
     <a href="{clip_url func=$currentPage.url.func args=$currentPage.url.args}" class="z-pagercss-item">{$currentItem}</a>
    {/if}
    {/foreach}

    {if $pagerPluginArray.currentPage < $pagerPluginArray.countPages}
    <a href="{clip_url func=$pagerPluginArray.nextUrl.func args=$pagerPluginArray.nextUrl.args}" title="{gt text="Next page"}" class="z-pagercss-next">&rsaquo;</a>
    <a href="{clip_url func=$pagerPluginArray.lastUrl.func args=$pagerPluginArray.lastUrl.args}" title="{gt text="Last page"}" class="z-pagercss-last">&raquo;</a>
    {else}
    <span class="z-pagercss-next" title="{gt text="Next page"}">&rsaquo;</span>
    <span class="z-pagercss-last" title="{gt text="Last page"}">&raquo;</span>
    {/if}
</div>
