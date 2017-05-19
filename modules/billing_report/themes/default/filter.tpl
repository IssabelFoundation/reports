<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr class="letra12">
        <td align="right">{$date_start.LABEL}:</td>
        <td align="left" nowrap>{$date_start.INPUT}</td>
        <td align="right">{$date_end.LABEL}:</td>
        <td align="left" nowrap>{$date_end.INPUT}</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td align="right">{$filter_field.LABEL}:</td>
        <td align="left">{$filter_field.INPUT}</td>
        <td colspan="2">
            <span id="textfield" {$style_text}>{$filter_value.INPUT}</span>
            <span id="duration" {$style_time}>
                    {$horas.INPUT}&nbsp;H&nbsp;
                    {$minutos.INPUT}&nbsp;M&nbsp;
                    {$segundos.INPUT}&nbsp;S&nbsp;
            </span>
        </td>
        <td align="left"><input class="button" type="submit" name="show" value="{$SHOW}" /></td>
    </tr>
</table>
