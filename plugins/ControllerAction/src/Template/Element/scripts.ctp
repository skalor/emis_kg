<?php echo $this->Html->script('ControllerAction.controller.action'); ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css"
      rel="stylesheet"/>
<style>
    .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn) {
        width: 100%;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>
<script>
    $(function() {
        setInterval(function () {
            $('.dropdown-menu.inner').css('display', '');
        }, 500);
    });
</script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/css/bootstrap-tokenfield.min.css" rel="stylesheet"/>
<style>
    .tokenfield {
        display: inline-block;
        min-width: 25%;
        min-height: 32px;
    }
    .tokenfield .token-input {
        width: 100% !important;
    }
    .tokenfield .token .token-label {
        font-size: 13.5px;
    }
    .chosen-drop {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .chosen-drop .btn {
        margin: 5px;
    }
    .chosen-results {
        border-bottom: 1px solid #eee;
        width: 100%;
    }
    @media only screen and (max-width: 800px) {
        .tokenfield {
            width: 100%;
        }
    }
    @media only screen and (min-width: 800px) and (max-width: 1240px) {
        .tokenfield {
            width: 30%;
        }
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tokenfield/0.12.0/bootstrap-tokenfield.min.js"></script>
<script>
    $(document).ready(function() {
        $('.tokenfield').tokenfield();
        setTimeout(function () {
            $('.chosen-drop')
                .append('<button type="button" class="btn chosen-toggle selectAll"><?= __("Select all") ?></button>')
                .append('<button type="button" class="btn chosen-toggle deselectAll"><?= __("Deselect all") ?></button>')
                .on('click', '.chosen-toggle', function () {
                    var isSelectAll = $(this).hasClass('selectAll'),
                        parent = $(this).parent().parent().parent();
                    parent.find('option').prop('selected', isSelectAll).parent().trigger('chosen:updated');
                });
        }, 1111);
    });
</script>
