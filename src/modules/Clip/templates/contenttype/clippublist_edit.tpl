<div class="z-formrow">
    {formlabel for='tid' __text='Publication type'}
    {clip_pubtypeselector id="tid" group="data" mandatory=true}
</div>

<div class="z-formrow">
    {formlabel for='numpubs' __text='Number of publications (leave empty for default)'}
    {formtextinput id='numpubs' group='data' maxLength='5'}
</div>

<div class="z-formrow">
    {formlabel for='offset' __text='First publication number to show (leave empty for first in list)'}
    {formtextinput id='offset' group='data' maxLength='5'}
</div>

<div class="z-formrow">
    {formlabel for='filter' __text='List filter as used in URL, separated by "&", but without "filter=" (e.g. "country:eq:DK")'}
    {formtextinput id='filter' group='data' maxLength='64'}
</div>

<div class="z-formrow">
    {formlabel for='order' __text='Orderby clause as used in URL. Should be a comma separated list of field names without "orderby=" (e.g. "core.lastUpdated:desc,title")'}
    {formtextinput id='order' group='data' maxLength='64'}
</div>

<div class="z-formrow">
    {formlabel for='tpl' __text='Template format for displaying list items'}
    {formtextinput id='tpl' group='data' maxLength='64'}
</div>
