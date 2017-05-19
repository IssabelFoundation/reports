<form method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">

<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td align="left">
          {if $mode eq 'input'}
          <input class="button" type="submit" name="save_default" value="{$SAVE}" >
          <input class="button" type="submit" name="cancel" value="{$CANCEL}"></td>
	  <td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
          {else}
          <img src="images/1x1.gif" border="0" align="absmiddle">&nbsp;&nbsp;
          <input class="button" type="submit" name="edit_default" value="{$EDIT}"></td>
          {/if}          
     </tr>
   </table>
  </td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
      <tr>
	<td width="15%">{$default_rate.LABEL}: {if $mode eq 'input'}<span  class="required">*</span>{/if}</td>
	<td width="35%">{$default_rate.INPUT}</td>
      </tr>
      <tr>
	<td width="15%">{$default_rate_offset.LABEL}: {if $mode eq 'input'}<span  class="required">*</span>{/if}</td>
	<td width="35%">{$default_rate_offset.INPUT}</td>
      </tr>
    </table>
  </td>
</tr>
</table>
</form>