<form method="<? echo $this->formMethod ?>" action="<? echo $this->formAction ?>">
<? echo $this->hiddenInputs ?>

<table class="computers">
	<tr class="<? echo $this->cssheader ?>">
		<td colspan="3"><? echo $this->lableHeader ?></td>
	</tr>

	<tr class="<? echo $this->cssdetail ?>">
		<td><? echo $this->lablePriority ?></td>
		<td><? echo $this->inputPriority ?></td>
		<td><? echo $this->lablePriorityDescription ?></td>
	</tr>

	<tr class="<? echo $this->cssdetail ?>">
		<td><? echo $this->lableName ?></td>
		<td><? echo $this->inputName ?></td>
		<td><? echo $this->lableEmailDescription ?></td>
	</tr>

	<tr class="<? echo $this->cssdetail ?>">
		<td><? echo $this->lableEmail ?></td>
		<td><? echo $this->inputEmail ?></td>
		<td><? echo $this->lableOtherEmailDescription ?></td>
	</tr>

	<tr class="<? echo $this->cssdetail ?>">
		<td><? echo $this->lableOtherEmail ?></td>
		<td colspan=2><? echo $this->inputOtherEmail ?></td>
	</tr>
