<tr class="networkingdetail">
	<td>
		<? print $this->name; ?><br />
		<input type=text name="<? print $this->nameName ;?>" value="<? print $this->nameValue; ?>" size=24>
	</td>
	<td>
		<? print $this->type; ?><br />
		<? print  $this->typeDropdown; ?>
	</td>
</tr>

<tr class="networkingdetail">
	<td>
		<? print $this->location; ?><br />
		<? print $this->locationDropdown; ?>
	</td>
	<td>
		<? print $this->ram ?><br />
		<input type=text name=<? print $this->nameRam ;?> value="<? print $this->ramValue ?>" size=5>
	</td>
</tr>

<tr class="networkingdetail">
<td>
	<? print $this->serial ;?><br />
	<input type=text name=<? print $this->nameSerial ;?> size=35 value="<? print $this->serialValue ;?>">
</td>

<td>
	<? print $this->otherSerial ;?><br />
	<input type=text name=<? print $this->nameOtherSerial ;?> value="<? print $this->otherSerialValue ;?>" size=25>
</td>
</tr>

<tr class="networkingdetail">
<td>
	<? print $this->ip ?>:<br />
	<input type=text name="<? print $this->nameIP ?>" value="<? print $this->ipValue ?>" size=20>
</td>

<td>
	<? print $this->mac ?><br />
	<input type=text name="<? print $this->nameMAC ?>" value="<? print $this->macValue ?>" size=20>
</td>
</tr>

<tr class="networkingdetail">
<td>
	<? print $this->contact ?><br />
	<input type=text name="<? print $this->nameContact ?>" value="<? print $this->contactValue ?>" size="20">
</td>
<td>
	<? print $this->contactNumber ?><br />
	<input type=text name="<? print $this->nameContactNumber ?>" value="<? print $this->contactNumberValue ?>" size="20">
</td>
</tr>

<tr class="networkingdetail">
<td colspan=2>
	<? print $this->comments ?><br />
	<textarea cols=40 rows=5 name=<? print $this->nameComments ?>  wrap=soft><? print $this->commentsValue ?></textarea>
</td>
</tr>
