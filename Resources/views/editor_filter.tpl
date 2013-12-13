
{clip_filter_form}

    <span class="z-nowrap core_title_wrapper">
        {clip_filter_plugin p='Label' field='core_title' id='core_title_label' for='core_title_op' __text='Title'}
        {clip_filter_plugin p='OpString' field='core_title' id='core_title_op' enabled='search,likefirst,eq'}
        {clip_filter_plugin p='String' id='core_title'}
    </span>

    <div class="z-nowrap core_author_wrapper">
        <span class="z-label">{gt text='Author'}</span>
        {clip_filter_plugin p='Author' id='core_author' multiple=false}
    </div>

    <span class="z-nowrap core_online_wrapper">
        {clip_filter_plugin p='Label' field='core_online' id='core_online_label' for='core_online' __text='Online'}
        {clip_filter_plugin p='YesNo' id='core_online'}
    </span>

    <span class="z-nowrap core_intrash_wrapper">
        {clip_filter_plugin p='Label' field='core_intrash' id='core_intrash_label' for='core_intrash' __text='In trash'}
        {clip_filter_plugin p='YesNo' id='core_intrash'}
    </span>

{/clip_filter_form}
