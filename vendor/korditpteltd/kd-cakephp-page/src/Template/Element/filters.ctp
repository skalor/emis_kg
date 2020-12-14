<?php if (isset($filters)) : ?>

<div class="toolbar-responsive panel-toolbar">
    <div class="toolbar-wrapper">
        <?php
        $template = $this->Page->getFormTemplate();
        $this->Form->templates($template);
        $request = $this->request;

        foreach ($filters as $name => $filter) {
            $default = $this->Page->getQueryString($name) !== false ? $this->Page->getQueryString($name) : $filter['value'];
            foreach($filter['options'] as $key => $option) {
                if (is_array($option)) {
                    $option['text'] = __($option['text']);
                } else {
                    $option = __($option);
                }
                $filter['options'][$key] = $option;
            }
            $inputOptions = [
                'class' => 'form-control',
                'label' => false,
                'options' => $filter['options'],
                'default' => $default,
                'onchange' => "Page.querystring('$name', this.value, this)",
                'id' => $name
            ];

            if (isset($filter['dependentOn']) && $filter['dependentOn']) {
                if (is_string($filter['dependentOn'])) {
                    $inputOptions['dependenton'] = [$filter['dependentOn']];
                } elseif (is_array($filter['dependentOn'])) {
                    $inputOptions['dependenton'] = $filter['dependentOn'];
                }
            }
            echo $this->Form->input($name, $inputOptions);
        }
        ?>
    </div>
</div>

<?php endif ?>
