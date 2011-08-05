
{clip_hitcount pid=$pubdata.core_pid tid=$pubdata.core_tid}

{* Open Graph tags
{ogtag prop='title' content=$pubdata.core_title}
{ogtag prop='type' content='article'}
{ogtag prop='image' content=''}
{ogtag prop='url' content=$returnurl}
{ogtag prop='site_name' content=$modvars.ZConfig.defaultpagetitle}
*}

<div class="clip-display clip-display-{$pubtype.urltitle}">
    {include file='generic_navbar.tpl'}

    <div class="clip-page clip-page-{$pubdata.core_pid} z-floatbox">
        <h2 class="clip-page-title">
            {$pubdata.core_title|safetext}
        </h2>

        <div class="clip-page-content">
            {$pubdata.content|safehtml|notifyfilters:"clip.hook.`$pubtype.tid`.ui.filter"}
        </div>

        {if $pubdata.displayinfo}
        <div class="clip-page-info">
            {capture assign='author'}<span class="author vcard">{$pubdata.core_author|profilelinkbyuid}</span>{/capture}
            <span class="clip-page-date">{gt text='Posted by %1$s on %2$s' tag1=$author tag2=$pubdata.core_publishdate|dateformat:'datelong'}</span>

            <br />
            {clip_accessblock tid=$pubtype.tid pid=$pubdata context='edit'}
            {strip}
            <span class="clip-page-edit-link">
                <span class="z-nowrap">
                    <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">{gt text='Edit'}</a>
                </span>
            </span>
            {/strip}

            <span class="text_separator">|</span>
            {/clip_accessblock}

            <span class="clip-page-reads">{gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount}</span>

            <span class="text_separator">|</span>

            {strip}
            <span class="clip-page-category">
            {if $pubdata.category.id}
                {capture assign='category'}
                <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid filter="category:sub:`$pubdata.category.id`"}" title="{gt text='View all posts in %s' tag1=$pubdata.category.fullTitle}">
                    {$pubdata.category.fullTitle}
                </a>
                {/capture}
                {gt text='Category: %s' tag1=$category}
            {else}
                <a href="{modurl modname='Clip' type='user' func='list' tid=$pubtype.tid filter="category:null"}" title="{gt text='View all uncategorized pages'}">
                    {gt text='Uncategorized'}
                </a>
            {/if}
            </span>
            {/strip}
            {*
            <span class="text_separator">|</span>
            {sharethis id=$pubdata.core_uniqueid url=$returnurl title=$pubdata.core_title __text='Share'}

            <span class="text_separator">|</span>
            {twitter url=$returnurl title=$pubdata.core_title count='horizontal'}
            {fblike url=$returnurl action='recommend' layout='horizontal' rel='display'}
            *}
            {*sexybookmarks url=$returnurl title=$pubdata.core_title*}
        </div>
        {/if}
    </div>

    <div class="clip-hooks-display">
        {notifydisplayhooks eventname="clip.ui_hooks.pubtype`$pubtype.tid`.display_view" id=$pubdata.core_uniqueid}
    </div>
</div>
