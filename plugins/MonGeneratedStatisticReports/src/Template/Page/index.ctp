<?php
$this->extend('Page.Layout/container');
$this->start('contentBody');
?>

<?php
echo $this->Html->script('Page.../plugins/jasny/js/jasny-bootstrap.min', ['block' => true]);

$tableClass = 'table table-curved table-sortable table-checkable';
$displayReorder = !in_array('reorder', $disabledActions) && $data->count() > 1;
$displayAction = true;

if ($displayReorder) {
    echo $this->Html->script('Page.reorder', ['block' => true]);
    $action = ($this->request->param('action') == 'index') ? 'reorder' : $this->request->param('action');
    $baseUrl = $this->Page->getUrl(['action' => $action]);
}

$tableHeaders = $this->Signing->getTableHeaders();
if ($data->count() >= 1) {
    array_unshift($tableHeaders, '<input type="checkbox" class="signCheckAll" style="opacity:1;transform:translate(-50%, -75%)">', '');
}
$tableData = $this->Signing->getTableData();
echo $this->Form->create('Signing', [
    'id' => "Signing",
    'url' => ['action' => 'sign'],
    'class' => 'form-horizontal'
]);
?>

<div class="table-wrapper" ng-class="disableElement">
    <div class="table-responsive">
        <table class="forActionShrink <?= $tableClass ?>" <?= $displayReorder ? 'id="sortable" url="' . $baseUrl . '"' : '' ?>>
            <thead><?= $this->Html->tableHeaders($tableHeaders) ?></thead>
            <tbody <?= $displayAction ? 'data-link="row"' : '' ?>>
                <?php echo $this->Html->tableCells($tableData) ?>
                <?php if ($data->count() >= 1) : ?>
                    <tr>
                        <td colspan="10">
                            <?= $this->Form->button(__('Sign'), ['type' => 'submit', 'name' => 'submit', 'value' => 'sign', 'class' => 'hidden']) ?>
                            <?= $this->Form->button(__('Sign'), ['type' => 'button', 'class' => 'signPopupBtn btn btn-default']) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="signPopupWrapper hidden">
    <div class="signPopupInner">
        <div class="signPopupHeader">
            <h3><?= __('Please type your pin code') ?></h3>
            <?= $this->Form->button('<i class="fa fa-times"></i>', ['type' => 'button', 'class' => 'signPopupBtn btn']) ?>
        </div>
        <div class="signPopupBody">
            <?= $this->Form->control('pin_code', ['type' => 'number', 'class' => 'form-control', 'min' => 999, 'max' => 999999]) ?>
            <?= $this->Form->button(__('Submit'), ['type' => 'button', 'class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<script>
    <?php if (isset($controllerName) && $controllerName): ?>
    function getGeneratingStatus(id, ms = 1000) {
        $.get('/<?= $controllerName ?>/checkGeneratingProgress/' + id, function (data) {
            if (data != 0) {
                var checkbox = $('input#' + id);
                checkbox.closest('tr').find('.spinner').css('display', 'none');
                if (!checkbox.is(':checked')) {
                    checkbox.removeAttr('disabled');
                }
            } else {
                setTimeout(function () {
                    getGeneratingStatus(id);
                }, ms);
            }
        });
    }
    $('.signCheck').each(function (i, v) {
        var id = $(v).attr('id');
        getGeneratingStatus(id);
    });
    <?php endif; ?>

    $('.signCheckAll').change(function () {
        if ($(this).is(':checked')) {
            $('.signCheck:enabled').prop('checked', true);
        } else {
            $('.signCheck:enabled').prop('checked', false);
        }
    });

    $('form').on('click', '.signCheck, .signPopupBtn, button[type=submit]', function (event) {
        event.stopPropagation();
    });

    $('.signPopupBtn').click(function () {
        var popup = $('.signPopupWrapper');
        if (popup.hasClass('hidden')) {
            popup.removeClass('hidden');
        } else {
            popup.addClass('hidden');
        }
    });

    $('.signPopupWrapper .signPopupBody button.btn').click(function () {
        $('#Signing button[type=submit]').click();
    });
</script>

<?php
echo $this->Form->end();
$params = $this->Paginator->params();
$totalRecords = array_key_exists('count', $params) ? $params['count'] : 0;
?>

<?php if ($totalRecords > 0) : ?>
<div class="pagination-wrapper" ng-class="disableElement">
    <?php
    $totalPages = $params['pageCount'];

    if ($totalPages > 1) :
    ?>
    <ul class="pagination">
        <?php
        echo $this->Page->getPaginatorButtons('prev');
        echo $this->Page->getPaginatorNumbers();
        echo $this->Page->getPaginatorButtons('next');
        ?>
    </ul>
    <?php endif ?>
    <div class="counter">
        <?php
        $defaultLocale = $this->Page->locale();
        $this->Page->locale('en_US');
        ?>
        <?php
            $paginateCountString = $this->Paginator->counter([
                'format' => '{{start}} {{end}} {{count}}'
            ]);

            $paginateCountArray = explode(' ', $paginateCountString);
            $this->Page->locale($defaultLocale);
            echo sprintf(__('Showing %s to %s of %s records'), $paginateCountArray[0], $paginateCountArray[1], $paginateCountArray[2])
        ?>
    </div>
    <div class="display-limit">
        <span><?= __('Display') ?></span>
        <?= $this->Page->getLimitOptions() ?>
        <p><?= __('records') ?></p>
    </div>
</div>
<?php endif ?>

<?php
$this->end();
?>

