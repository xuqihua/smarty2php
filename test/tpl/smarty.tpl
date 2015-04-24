{if !empty($array.key)}
    {foreach from=$array.key key=key item=value}
        {$varlue.content}
    {/foreach}
{elseif $array.key2 == 2}
    {* Comment Test *}
    {$array.key2}
{/if}
{literal}
<scrip type="text/javascript">

</scrip>
{/literal}