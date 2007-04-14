<form method="post" action="device-fields-add.php">
<tr class="setupdetail">

<td>
<? print $this->value ?>
<input type="hidden" name="devicetype" value="<? print $this->value ?>">
</td>

<td>
<input type="submit" value="<? print $this->editFields; ?>">
</form>
</td>

<td>
<form method="post">
<input type="hidden" name="delete" value="<? print $this->value ?>">
<input type="submit" value="<? print $this->delete; ?>">
</form>
</td>
</tr>
