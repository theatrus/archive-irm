<p><? _("New devices must not included spaces in the name.") ?></p>
<table border=1>
<tr class="setupheader">
<td colspan="3"><? print $this->addNewDevice; ?></td>
</tr>

<tr class="setupdetail">
<td>
<form method ="post" action="setup-devices.php" >
<input type="text" name="newdevice">
</td>
<td colspan="2">
<input type="submit" value="<? print $this->addDevice; ?>">
</form>
</td>
</tr>

<tr class="setupheader">
<td colspan="3"><? print $this->existingDevice; ?></td>
</tr>
</table>
