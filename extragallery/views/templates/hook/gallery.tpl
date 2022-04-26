<h1>
    {$labelText}
</h1>
{if $data != 0}

{foreach from=$data item=entry name=foo}
    {if $smarty.foreach.foo.index+1 > $maxImg}
        {break}
     {/if}
    <img src="/modules/extragallery/uploads/{$entry.image}" class="img-thumbnail" style="width: 300px; height: 300px; object-fit: cover;">
{/foreach}

{else}
<p>
 No images uploaded yet.
</p>
{/if}
    
