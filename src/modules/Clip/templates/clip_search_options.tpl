
<input type="hidden" id="active_clip" name="active[Clip]" value="1" checked="checked" />

{foreach from=$pubtypes item=pubtype}
<div>
    <input type="checkbox" id="Clip{$pubtype.tid}" name="search_tid[{$pubtype.tid}]" value="1" checked="checked" />
    <label for="Clip{$pubtype.tid}">{gt text=$pubtype.title}</label>
</div>
{/foreach}
