<form method=<? echo $this->method ?>  action="<? echo $this->action ?>">
<? echo $this->hiddenInputs ?>
<tr class="<? echo $this->cssheader ?>">
	<td colspan=2><? echo $this->requestOptionHeader ?></td>
</tr>

<tr class="<? echo $this->cssdetail ?>">
	<td><? echo $this->requestOption ?></td>
	<td>
		<? echo $this->input ?>
		<input type=submit value="<? echo $this->submitText ?>">
	</td>
</tr>
</form>

