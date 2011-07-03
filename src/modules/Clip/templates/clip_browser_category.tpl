{assign var='depth' value=0}

<ul class="clip-browser-category clip-bc-{$options.urltitle}-{$options.field|safetext}">
    {foreach from=$cats item='cat' name='clipcb'}
    {if $smarty.foreach.clipcb.first neq true}
        {if $depth gt $cat.depth}
            {section name='k' loop=$depth-$cat.depth}
                </li></ul>
            {/section}
        {elseif $depth lt $cat.depth}
            {section name='k' loop=$cat.depth-$depth}
                <ul>
            {/section}
        {else}
            </li>
        {/if}
        {assign var='depth' value=$cat.depth}
    {/if}
    <li>
        {if $cat.selected eq 1}<strong>{/if}
            <a href="{$cat.url}">
                {$cat.fullTitle}
                {if $options.count and isset($cat.count)}
                    ({$cat.count})
                {/if}
            </a>
        {if $cat.selected eq 1}</strong>{/if}
    {/foreach}
    {section name='k' loop=$depth}
        </li></ul>
    {/section}
    </li>
</ul>

{if $options.selected AND $options.togglediv}
<script type="text/javascript">
    {{*
    {{ajaxheader effects=1}}
    Effect.toggle('{{$options.togglediv}}', 'blind', {duration:0.0});
    *}}
    document.getElementById('{{$options.togglediv}}').style.display = 'block';
</script>
{/if}
