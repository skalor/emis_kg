<?php
	$alias = $ControllerAction['table']->alias();
	$tableHeaders = isset($attr['tableHeaders']) ? $attr['tableHeaders'] : [];
	$tableCells = isset($attr['tableCells']) ? $attr['tableCells'] : [];
	$reorder = isset($attr['reorder']) ? $attr['reorder'] : [];
	$label = isset($attr['customElementLabel']) ? $attr['customElementLabel'] : [];
	$chosenSelectInput = isset($attr['chosenSelectInput']) ? $attr['chosenSelectInput'] : [];
	$sectionField = isset($attr['sectionField']) ? $attr['sectionField'] : $alias.'sectionTxt';
?>

<?php if ($ControllerAction['action'] == 'index') : ?>
	<?= isset($attr['value']) ? $attr['value'] : ''; ?>
<?php elseif ($ControllerAction['action'] == 'view') : ?>
	<div class="table-wrapper">
		<div class="table-in-view">
			<table class="table">
				<thead><?= $this->Html->tableHeaders($tableHeaders) ?></thead>
				<tbody><?= $this->Html->tableCells($tableCells) ?></tbody>
			</table>
		</div>
	</div>
<?php elseif ($ControllerAction['action'] == 'edit' || $ControllerAction['action'] == 'add') : ?>
	<?= $this->Html->script('CustomField.custom.form', ['block' => true]); ?>
	<?php
		$displayReorder = isset($reorder) && $reorder && count($tableCells) > 0;
		if ($displayReorder) {
			echo $this->Html->script('ControllerAction.reorder', ['block' => true]);
			$tableHeaders[] = [__('Reorder') => ['class' => 'cell-reorder']];
		} else {
			$tableHeaders[] = [__('') => ['class' => 'cell-reorder']];
			$displayReorder = true;
		}
	?>

		<hr>
		<h3><?= __($label)?></h3>

			<?php
				echo $this->HtmlField->chosenSelectInput($chosenSelectInput, ['label' => __($chosenSelectInput['label']), 'multiple' => false, 'onchange' => "$('#reload').val('addField').click();"]);
			?>
			<?=
				$this->Form->input($sectionField, [
					'label' => __('Add Section'),
					'type' => 'text',
					'id' => 'sectionTxt',
					'maxLength' => 250
				]);
			?>
			<div class="form-buttons no-margin-top">
				<div class="button-label"></div><button onclick="CustomForm.addSection('#sectionTxt', 2);" type="button" class="btn btn-default btn-xs">
					<span><i class="fa fa-plus"></i><?=__('Add Section')?></span>
				</button>
			</div>
			<br/>
	<div class="table-wrapper">
		<div class="table-responsive">
			<table class="table table-curved table-input table-checkable" <?= $displayReorder ? 'id="sortable"' : '' ?>>
				<thead><?= $this->Html->tableHeaders($tableHeaders) ?></thead>
				<tbody><?= $this->Html->tableCells($tableCells) ?></tbody>
			</table>
		</div>
	</div>
<?php endif ?>