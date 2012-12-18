
<form class="z-form z-clip-mini" action="{modurl modname='Clip' type='admin' func='generator' tid=$pubtype.tid code='filter'}" method="post">
    <fieldset>
        <legend>{gt text='Filter template generation options'}</legend>
        {foreach from=$pubfields item='pubfield'}
            <div class="z-formrow">
                <div>
                    <input id="gen_{$pubfield.name|safetext}" name="gen_{$pubfield.name|safetext}" type="checkbox" value="1"{if $pubfield.gen} checked="checked"{/if} />
                    <label for="gen_{$pubfield.name|safetext}">{gt text="Enable filtering for '%s'" tag1=$pubfield.title|safetext}</label>
                </div>
            </div>
            {* TODO check the available templates here *}
        {/foreach}
        <div class="z-buttons">
            {button src='run.png' set='icons/extrasmall' __alt='Generate' __title='Generate' __text='Generate' class='z-bt-small'}
        </div>
    </fieldset>
</form>
