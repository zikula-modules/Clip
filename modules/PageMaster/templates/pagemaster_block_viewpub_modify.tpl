{* $Id$ *}
<div class="z-formrow">
    <label for="viewpub_pubtype">{gt text='Publication type'}</label>
    {html_options name='tid' options=$pubtypes selected=$vars.tid}
</div>
<div class="z-formrow">
    <label for="viewpub_pid">{gt text='Publication ID'}</label>
    <input id="viewpub_pid" name="pid" type="text" size="30" maxlength="255" value="{$vars.pid|safetext}" />
</div>
<div class="z-formrow">
    <label for="viewpub_template">{gt text='Template'}</label>
    <input id="viewpub_template" name="template" type="text" size="30" maxlength="255" value="{$vars.template|safetext}" />
</div>
<div class="z-formrow">
    <label for="viewpub_cachelt">{gt text='Cache lifetime'}</label>
    <input id="viewpub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
