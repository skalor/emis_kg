<?= $this->Html->script('OpenEmis.../plugins/tableCheckable/jquery.tableCheckable', ['block' => true]) ?>

<?php if ($action == 'add' || $action == 'edit') : ?>

<div class="input clearfix">
	<label><?= isset($attr['label']) ? $attr['label'] : $attr['field'] ?></label>
	<div class="table-wrapper">
		<div class="table-in-view">
			<table class="table table-checkable">
				<thead>
					<tr>
						<th class="checkbox-column"><input type="checkbox" class="no-selection-label" kd-checkbox-radio/></th>
						<th><?= __('Code') ?></th>
						<th><?= __('Name') ?></th>
					</tr>
                    <tr>
                        <th colspan="3" class="searchProgram">
                            <input type="text" class="search-input" placeholder="<?= __('Search subjects') ?>"
                                   style="width: 100%!important">
                        </th>
                    </tr>
				</thead>

				<?php if (isset($attr['data'])) : ?>

				<tbody>
					<?php foreach ($attr['data'] as $i => $obj) : ?>
					<tr>
						<td class="checkbox-column">
							<?php
							$checkboxOptions = ['class' => 'no-selection-label', 'kd-checkbox-radio' => ''];
							$checkboxOptions['value'] = $obj->id;
								if (!empty($attr['exists']) && in_array($obj->id, $attr['exists'])) {
									$checkboxOptions['disabled'] = 'disabled';
									$checkboxOptions['checked'] = 'checked';
								}
							echo $this->Form->checkbox("grades.education_grade_subject_id.$i", $checkboxOptions);

							?>
						</td>
						<td><?= $obj->code ?></td>
						<td><?= $obj->name ?></td>
					</tr>
					<?php endforeach ?>
				</tbody>

				<?php endif ?>

			</table>
		</div>
	</div>
</div>
<script>
    var searchProgram = $('.searchProgram'),
        searchProgramInput = searchProgram.children('.search-input');

    searchProgramInput.keydown(function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
        }
    });

    searchProgramInput.keyup(function (e) {
        var searchText = searchProgramInput.val().toLowerCase(),
            programs = searchProgram.closest('.table').children('tbody').children('tr');

        programs.each(function (i, v) {
            var containText = $(v).children('td').text().toLowerCase();

            if (containText.indexOf(searchText) >= 0) {
                $(v).removeClass('hidden');
                $(v).find('td').first().addClass('checkbox-column');
            } else {
                $(v).addClass('hidden');
                $(v).find('td').first().removeClass('checkbox-column');
            }
        });
    });
</script>

<?php elseif ($action == 'view') : ?>

<div class="input clearfix">

	<div class="table-wrapper">
		<div class="table-in-view">
			<table class="table table-checkable">
				<thead>
					<tr>
                                            <th><?= __('Code') ?></th>
						<th><?= __('Name') ?></th>
					</tr>
				</thead>

				<?php if (isset($attr['data'])) : ?>

				<tbody>
					<?php foreach ($attr['data'] as $i => $obj) : ?>
					<tr>
						<td><?= $obj->code ?></td>
						<td><?= $obj->name ?></td>
					</tr>
					<?php endforeach ?>
				</tbody>

				<?php endif ?>

			</table>
		</div>
	</div>
</div>
<?php endif ?>
