<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center" class="tabForm">
<tr>
    <td>
        <form method="POST" style="margin-bottom:0;" action="?menu={$menu}">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                <tr class="letra12">{$contentFilter}</tr>
            </table>
        </form>
    </td>
</tr>
<tr>
<td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
        <p align='center'><img alt="Graphic" src="{$URL_GRAPHIC}" /></p>
        </td>
    </tr>
    {if $mostrarSumario}
    <tr>
        <td>
        <table class="table_data" align="center" cellspacing="0" cellpadding="0">
        <tr class="table_title_row">
            <td align='center' class="table_title_row borderLeft" style="background:none;">{$Rate_Name}</td>
            <td align='center' class="table_title_row">{$Title_Criteria}</td>
            <td align='center' class="table_title_row borderRight">%</td>
        </tr>
        {foreach name=outer item=fila from=$results}
        <tr>
            {foreach key=key item=item from=$fila name=data}
	    <td class="table_data" align="right">{$item}</td>
            {/foreach}
        </tr>
        {/foreach}
        </table>
        </td>
    </tr>
    {/if}
    </table>
</td>
</tr>
</table>