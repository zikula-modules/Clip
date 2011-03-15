<div class="z-formrow">
    {formlabel for='tid' __text='Publication type'}
    {clip_pubtypeselector id="tid" group="data" mandatory=true}
</div>

<div class="z-formrow">
    {formlabel for='pid' __text='Publication PID'}
    {formtextinput id="pid" group='data' maxLength='5'}
</div>

<div class="z-formrow">
    {formlabel for='tpl' __text='Template format for displaying the publication'}
    {formtextinput id='tpl' group='data' maxLength='64'}
</div>
