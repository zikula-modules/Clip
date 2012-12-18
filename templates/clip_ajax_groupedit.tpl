{if $mode eq 'edit'}
    {gt text='Edit group' assign='windowtitle'}
{else}
    {gt text='Create new group' assign='windowtitle'}
{/if}
<div id="clip_ajax_form_container" style="display: none;" title="{$windowtitle}">
    <form id="clip_ajax_form" class="z-form" action="#" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" id="group_mode_pos" name="pos" value="{$pos}" />
        {if isset($group.parent)}
        <input type="hidden" id="group_parent" name="group[parent]" value="{$group.parent}" />
        {/if}
        {if isset($group.gid)}
        <input type="hidden" id="group_gid" name="group[gid]" value="{$group.gid}" />
        {/if}
        <fieldset>
            <legend>{$windowtitle}</legend>
            <div class="z-formrow">
                <label>{gt text='Name'}</label>
                {foreach item='language' from=$languages}
                <div class="z-formlist">
                    <input id="group_name_{$language}" name="group[name][{$language}]" value="{$group.name[$language]|default:''}" type="text" size="50" maxlength="255" />
                    <label for="group_name_{$language}">({$language})</label>
                </div>
                {/foreach}
            </div>
            <div class="z-formrow">
                <label>{gt text='Description'}</label>
                {foreach item='language' from=$languages}
                <div class="z-formlist">
                    <textarea id="group_desc_{$language}" name="group[description][{$language}]" rows="4" cols="56">{$group.description[$language]|default:''}</textarea>
                    <label for="group_desc_{$language}">({$language})</label>
                </div>
                {/foreach}
            </div>
        </fieldset>
    </form>
</div>
