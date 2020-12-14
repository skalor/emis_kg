<style>
    .toolbar.toolbar-search {
        width: 100%;
    }

    .toolbar-search {
        padding-right: 175px !important;
    }

    .toolbar .search {
        position: absolute;
         right: auto;
    }

    .export-dropdown {
        float: right;
        display: inline-block!important;
        width: 550px;
    }

    .input-select-wrapper, .form-control {
        width: auto;
        /*min-width: 149px;*/
    }

    .label-export {
        display: inline-block!important;
        margin-right: 20px;
        font-size: 18px;
    }

    .generate-report {
        width: 150px!important;
        margin-top: -4px!important;
        min-width: 150px;
    }

    [name="generated_report"] {
        min-width: 150px!important;
        max-width: 151px!important;
    }
</style>
<div class="input select export-dropdown">
    <label class="label-export" for="<?php echo $linkOptions['id']?>"><b><?php echo $linkOptions['title']?></b></label>
    <div class="input-select-wrapper">
        <select name="<?php echo $linkOptions['name']?>" maxlength="11" id="<?php echo $linkOptions['id']?>">
            <?php
            echo "<option value='' selected>".__('Select option')."</option>";
            foreach ($linkOptions['options'] as $key => $value) {
                echo "<option value='{$key}'>{$value}</option>";
            }
            ?>
        </select>
    </div>
    <button class="btn btn-dark generate-report <?php echo $linkOptions['action_event']?>" style="width: 150px!important;" name="generate-report">Генерировать отчет</button>
</div>
