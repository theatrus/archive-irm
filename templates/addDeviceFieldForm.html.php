<tr class="setupheader">
<td colspan="3"><? print $this->addNewField ?></td>
</tr>

<form method="post">
<tr class="setupdetail">
<td><input type="text" name="field_name"></td>
<input type="hidden" name="devicetype" value="<? print $this->devicetype ?>">

<td>
<select name="datatype">
<option value="string"><? print $this->string ?></option>
<option value="textarea"><? print $this->textarea ?></option>
<option value="boolean"><? print $this->boolean ?></option>
<option value="datetime"><? print $this->datetime ?></option>
</select>
</td>

<td><input type="submit" value="<? print $this->addField ?>"></td>
</form>
