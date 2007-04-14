<tr class="computerdetail">
<td><? echo $this->lableName ?><br /><? echo $this->name ?></td>
<td><? echo $this->lableType ?><br /><? echo $this->type ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableLocation ?><br /><? echo $this->location ?></td>	
<td><? echo $this->lableOS ?><br /><? echo $this->os ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableOSVersion ?><br /><? echo $this->osver ?></td>
<td><? echo $this->lableProcessor ?><br /><? echo $this->processor ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableProcessorSpeed ?><br /><? echo $this->processorSpeed ?></td>
<td><? echo $this->lableSerialNumber ?><br /><? echo $this->serialNumber ?></td>
</tr>
	
<tr class="computerdetail">
<td><? echo $this->lableOtherSerialNumber ?><br /><? echo $this->otherSerialNumber ?></td>
<td><? echo $this->lableHardDriveSpace ?><br /><? echo $this->HardDriveSpace ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableRamType ?><br /><? echo $this->ramType ?></td>
<td><? echo $this->lableRamAmount ?><br /><? echo $this->ram ?></td>
</tr>
			
<tr class="computerdetail">
<td><? echo $this->lableNetworkCard ?><br /><? echo $this->networkCard ?></td>
<td><? echo $this->lableIPAddress ?><br /><? echo $this->IPAddress ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableMAC ?><br /><? echo $this->mac ?></td>
<td><? echo $this->lableComments ?><br /><? echo $this->comments ?></td>
</tr>

<tr class="computerdetail">
<td><? echo $this->lableContactPerson ?><br /><? echo $this->contactPerson ?></td>
<td><? echo $this->lableContactNumber ?><br /><? echo $this->contactNumber ?></td>
</tr>

<tr class="computerdetail">
<td colspan=2>
<input type="hidden" name="flags_server">
<? echo $this->server . $this->lableServer ?>
</td>
</tr>

<tr class="computerdetail">
<td colspan=2>
<input type="hidden" name="flags_surplus">
<? echo $this->surplus . $this->lableSurplus ?>
</td>
</td>
</tr>
