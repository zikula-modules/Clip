
<input type="hidden" id="active_clip" name="active[Clip]" value="1" checked="checked" />

{foreach from=$pubtypes item='pubtype'}
<div>
    <input type="checkbox" id="active_cliptid{$pubtype.tid}" name="search_cliptid[{$pubtype.tid}]" value="1" checked="checked" />
    <label for="Clip{$pubtype.tid}">{$pubtype.title|safetext}</label>
</div>
{/foreach}
