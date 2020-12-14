<?php /* <?php
$actionItem = '<li role="presentation"><a href="%s" role="menuitem" tabindex="-1"><i class="%s"></i>%s</a></li>';
$rowActions = !is_array($data) ? $data->rowActions : $data['rowActions'];
?>

<div class="dropdown">
    <button class="btn btn-dropdown action-toggle" type="button" id="action-menu" data-toggle="dropdown" aria-expanded="true">
        <?= __('Select') ?><span class="caret-down"></span>
    </button>

    <ul class="dropdown-menu action-dropdown" role="menu" aria-labelledby="action-menu">
        <div class="dropdown-arrow"><i class="fa fa-caret-up"></i></div>

        <?php
foreach ($rowActions as $action) {
    $action['href'] = array_key_exists('url', $action) ? $this->Page->getUrl($action['url']) : '';
    echo sprintf($actionItem, $action['href'], $action['icon'], $action['title']);
}
?>
    </ul>
</div> */ ?>

<?php
$actionItem = '<a href="%s" class="%s" style="%s" role="menuitem" tabindex="-1" data-toggle="tooltip" data-placement="bottom" data-original-title="%s">%s</a>';
$rowActions = !is_array($data) ? $data->rowActions : $data['rowActions'];
?>

<div class="Div">
    <?php $buttonIcons = array(
        'view'=>['fa fa-eye', 'color:#ff9e15;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M0 8.12123C1.25818 4.92851 4.36364 2.66669 8 2.66669C11.6364 2.66669 14.7418 4.92851 16 8.12123C14.7418 11.314 11.6364 13.5758 8 13.5758C4.36364 13.5758 1.25818 11.314 0 8.12123ZM14.4143 8.12125C13.2143 5.67034 10.7561 4.12125 7.99974 4.12125C5.24338 4.12125 2.7852 5.67034 1.5852 8.12125C2.7852 10.5722 5.24338 12.1213 7.99974 12.1213C10.7561 12.1213 13.2143 10.5722 14.4143 8.12125ZM8.00009 6.30304C9.00372 6.30304 9.81827 7.11758 9.81827 8.12122C9.81827 9.12485 9.00372 9.9394 8.00009 9.9394C6.99645 9.9394 6.18191 9.12485 6.18191 8.12122C6.18191 7.11758 6.99645 6.30304 8.00009 6.30304ZM4.7273 8.12124C4.7273 6.3176 6.19639 4.84851 8.00003 4.84851C9.80367 4.84851 11.2728 6.3176 11.2728 8.12124C11.2728 9.92488 9.80367 11.394 8.00003 11.394C6.19639 11.394 4.7273 9.92488 4.7273 8.12124Z" fill="#FF9E15"/> </svg>'],
        'edit'=>['fa kd-edit', 'color:#009966;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#clip0)"> <path d="M1.6 11.04L0 16L4.96 14.4L1.6 11.04Z" fill="#009966"/> <path d="M10.5301 2.08406L2.72375 9.89038L6.11781 13.2844L13.9241 5.47812L10.5301 2.08406Z" fill="#009966"/> <path d="M15.7601 2.48L13.5201 0.24C13.2001 -0.08 12.7201 -0.08 12.4001 0.24L11.6801 0.96L15.0401 4.32L15.7601 3.6C16.0801 3.28 16.0801 2.8 15.7601 2.48Z" fill="#009966"/> </g> <defs> <clipPath id="clip0"> <rect width="16" height="16" fill="white"/> </clipPath> </defs> </svg>'],
        'remove'=>['fa kd-trash', 'color:#c71100;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.66675 14.2222C2.66675 15.2045 3.46229 16 4.44454 16H11.5557C12.5379 16 13.3334 15.2045 13.3334 14.2222V3.55554H2.66675V14.2222Z" fill="#C71100"/> <path d="M11.1112 0.888875L10.2222 0H5.77783L4.88892 0.888875H1.77783V2.66667H14.2222V0.888875H11.1112Z" fill="#C71100"/> </svg>'],
        'delete'=>['fa kd-trash', 'color:#c71100;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M2.66675 14.2222C2.66675 15.2045 3.46229 16 4.44454 16H11.5557C12.5379 16 13.3334 15.2045 13.3334 14.2222V3.55554H2.66675V14.2222Z" fill="#C71100"/> <path d="M11.1112 0.888875L10.2222 0H5.77783L4.88892 0.888875H1.77783V2.66667H14.2222V0.888875H11.1112Z" fill="#C71100"/> </svg>'],
        'history'=>['fa fa-history', 'color:#2d7ed6;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M0 0V6.22222H6.22222L3.6 3.6C4.72889 2.48 6.28444 1.77778 8 1.77778C11.4311 1.77778 14.2222 4.56889 14.2222 8C14.2222 11.4311 11.4311 14.2222 8 14.2222C4.56889 14.2222 1.77778 11.4311 1.77778 8H0C0 12.4178 3.58222 16 8 16C12.4178 16 16 12.4178 16 8C16 3.58222 12.4178 0 8 0C5.78667 0 3.78667 0.897778 2.34667 2.34667L0 0ZM8.889 8.8889V4.44446H7.55566V8.13335L4.42677 9.99112L5.11122 11.1289L8.889 8.8889Z" fill="#2D7ED6"/> </svg>'],
        'editProfile'=>['fa fa-pencil-square', 'color:#009966;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#clip0)"> <path d="M1.6 11.04L0 16L4.96 14.4L1.6 11.04Z" fill="#009966"/> <path d="M10.5301 2.08406L2.72375 9.89038L6.11781 13.2844L13.9241 5.47812L10.5301 2.08406Z" fill="#009966"/> <path d="M15.7601 2.48L13.5201 0.24C13.2001 -0.08 12.7201 -0.08 12.4001 0.24L11.6801 0.96L15.0401 4.32L15.7601 3.6C16.0801 3.28 16.0801 2.8 15.7601 2.48Z" fill="#009966"/> </g> <defs> <clipPath id="clip0"> <rect width="16" height="16" fill="white"/> </clipPath> </defs> </svg>'],
        'editRelation'=>['fa fa-pencil-square-o', 'color:#1A4E87;font-size: 17px;margin-left: 10px;','<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <g clip-path="url(#clip0)"> <path d="M1.6 11.04L0 16L4.96 14.4L1.6 11.04Z" fill="#009966"/> <path d="M10.5301 2.08406L2.72375 9.89038L6.11781 13.2844L13.9241 5.47812L10.5301 2.08406Z" fill="#009966"/> <path d="M15.7601 2.48L13.5201 0.24C13.2001 -0.08 12.7201 -0.08 12.4001 0.24L11.6801 0.96L15.0401 4.32L15.7601 3.6C16.0801 3.28 16.0801 2.8 15.7601 2.48Z" fill="#009966"/> </g> <defs> <clipPath id="clip0"> <rect width="16" height="16" fill="white"/> </clipPath> </defs> </svg>'],
        'download'=>['fa fa-arrow-down', 'color:#ff0000;font-size: 17px;margin-left: 10px;','<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13.5775 7.74404L10.8334 10.4881V1.66663H9.16675V10.4881L6.42267 7.74404L5.24416 8.92255L10.0001 13.6785L14.756 8.92255L13.5775 7.74404ZM18.3334 16.6666V13.3333H16.6667V16.6666H3.33341V13.3333H1.66675V16.6666C1.66675 17.5871 2.41294 18.3333 3.33341 18.3333H16.6667C17.5872 18.3333 18.3334 17.5871 18.3334 16.6666Z" fill="#004A51"/> </svg>'],
        'download_hash'=>['fa fa-download', 'color:#ff0000;font-size: 17px;margin-left: 10px;','<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13.5775 7.74404L10.8334 10.4881V1.66663H9.16675V10.4881L6.42267 7.74404L5.24416 8.92255L10.0001 13.6785L14.756 8.92255L13.5775 7.74404ZM18.3334 16.6666V13.3333H16.6667V16.6666H3.33341V13.3333H1.66675V16.6666C1.66675 17.5871 2.41294 18.3333 3.33341 18.3333H16.6667C17.5872 18.3333 18.3334 17.5871 18.3334 16.6666Z" fill="#004A51"/> </svg>']
    ); ?>
    <?php
    foreach ($rowActions as $key => $action) {
        $style = 'color:#2d7ed6;font-size: 17px;';
        if (isset($buttonIcons[$key])) {
            $action['icon'] = $buttonIcons[$key][0];
            $action['svg'] = $buttonIcons[$key][2];
            $style = $buttonIcons[$key][1];
        }
        $action['href'] = array_key_exists('url', $action) ? $this->Page->getUrl($action['url']) : '';
        echo sprintf($actionItem, $action['href'], "", $style, $action['title'],$action['svg']);
    }
    ?>
</div>
