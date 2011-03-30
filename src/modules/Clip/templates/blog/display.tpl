{pagesetvar name="title" value="`$pubdata.core_title` - `$pubtype.title` - `$modvars.ZConfig.sitename`"}
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{include file='clip_generic_navbar.tpl' section='display'}

<div id="clip-post-{$pubdata.core_pid}" class="clip-post clip-post-{$pubdata.core_pid}">
    <h2 class="clip-post-title">
        {gt text=$pubdata.core_title}
    </h2>
    <div class="clip-post-meta">
        {usergetvar name='uname' uid=$pubdata.core_author assign='uname'}
        {capture assign='author'}<span class="author vcard">{$uname|userprofilelink}</span>{/capture}
        <span class="clip-post-date">{gt text='Posted on %1$s by %2$s' tag1=$pubdata.core_publishdate|dateformat:'datebrief' tag2=$author}</span>
        <span class="clip-post-reads">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
    </div>
    <div class="clip-post-content">
        {$pubdata.content|safehtml}
    </div>
    <div class="clip-post-utility">
        {if $pubdata.category}
        <span class="clip-post-category">
            {capture assign='category'}
            <a href="{modurl modname='Clip' func='view' tid=$pubtype.tid filter="category:sub:`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                {$pubdata.category.fullTitle|safetext}
            </a>
            {/capture}
            {gt text='Posted in %s' tag1=$category}
        </span>

        <span class="text_separator">|</span>
        {/if}
        <span class="clip-post-permalink">
            {gt text='Permalink to %s' tag1=$pubdata.core_title assign='bookmark_title'}
            {modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid assign='bookmark_url'}
            {gt text='Bookmark the <a rel="bookmark" title="%1$s" href="%2$s">permalink</a>' tag1=$bookmark_title tag2=$bookmark_url}
        </span>

        <span class="text_separator">|</span>

        <span class="clip-post-edit-link">
            {checkpermissionblock component='clip:input:' instance="$pubtype.tid::" level=ACCESS_ADD}
                <span class="z-nowrap">
                    <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
                </span>
            {/checkpermissionblock}
        </span>
    </div>
</div>

{*notifydisplayhooks eventname='clip.hook.item.ui.view' area='module_area.clip.item' subject=$pubdata module='Clip'*}