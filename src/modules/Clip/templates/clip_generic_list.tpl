
{if !$homepage}{pagesetvar name="title" value="`$pubtype.title` - `$modvars.ZConfig.sitename`"}{/if}

{include file='clip_generic_navbar.tpl'}

<h2>{$pubtype.title}</h2>

{if $pubtype.description neq ''}
    <div class="clip-pubtype-desc">{$pubtype.description}</div>
{/if}

{*clip_pagerabc*}

<table class="z-datatable clip-pub-list">
    <tbody>
        {foreach from=$publist item='pubdata'}
        <tr class="{cycle values='z-even,z-odd'}">
            <td>
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink}">{$pubdata.core_title}</a>
                <span class="z-sub z-floatright">({gt text='%s read' plural='%s reads' count=$pubdata.core_hitcount tag1=$pubdata.core_hitcount})</span>
            </td>
            <td class="z-right z-nowrap">
                {strip}
                <a href="{modurl modname='Clip' type='user' func='display' tid=$pubtype.tid pid=$pubdata.core_pid title=$pubdata.core_title|formatpermalink}">
                    {img modname='core' src='demo.png' set='icons/extrasmall' __title='View' __alt='View'}
                </a>
                {clip_accessblock tid=$pubtype.tid pid=$pubdata context='edit'}
                &nbsp;
                <a href="{modurl modname='Clip' type='user' func='edit' tid=$pubtype.tid pid=$pubdata.core_pid}">
                    {img modname='core' src='edit.png' set='icons/extrasmall' __title='Edit' __alt='Edit'}
                </a>
                {/clip_accessblock}
                {/strip}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty">
            <td>{gt text='No publications found.'}</td>
        </tr>
        {/foreach}
    </tbody>
</table>

{if $pager.itemsperpage neq $modvars.Clip.maxperpage}
    {pager display='page' posvar='page' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7}
    {*pager display='startnum' posvar='startnum' rowcount=$pager.numitems limit=$pager.itemsperpage maxpages=7*}
{/if}

