<?php
$options = [
    'toArray' => true,
    'urlParams' => isset($urlParams) ? $urlParams : true
];
$href = $this->Page->getUrl($url, $options);

$_linkOptions = [
    'class' => 'btn btn-xs btn-default',
    'title' => '',
    'data-toggle' => 'tooltip',
    'data-placement' => 'bottom',
    'escape' => false
];

if (isset($linkOptions)) {
    $_linkOptions = array_merge($_linkOptions, $linkOptions);
}
$_linkOptions['data-original-title'] = $_linkOptions['title'];
if ($svgIcon){
    echo $this->Html->link($svgIcon, $href, $_linkOptions);
}else {
    echo $this->Html->link('<i class="' . $iconClass . ' f"></i>', $href, $_linkOptions);
}

?>
