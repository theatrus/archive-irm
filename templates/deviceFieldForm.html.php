<tr class="setupdetail">
<td><? print $this->field ?></td>
<td><? print $this->fieldType  ?></td>
<td>
<form method="post">
<input type="hidden" name="delete" value="<? print $this->field ?>">
<input type="hidden" name="devicetype" value="<? print $this->devicetype  ?>">
<input type="submit" value="<? print $this->delete ?>">
</form>
</td>
</tr>
