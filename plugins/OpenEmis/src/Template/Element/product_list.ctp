<div class="btn-group">
    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
        <img src="/img/notifications.svg" class="navbarIcons" >
        <span class="label" ><?=count($notices)?></span>
    </a>

    <div aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu product-lists col-xs-12">


        <div class="product-wrapper">
            <div class="dropdown-close">
            <i class="only_pc fa fa-close"></i>
                <i class="only_mobile closer"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.8334 2.75169L23.2484 0.166687L13 10.415L2.75169 0.166687L0.166687 2.75169L10.415 13L0.166687 23.2484L2.75169 25.8334L13 15.585L23.2484 25.8334L25.8334 23.2484L15.585 13L25.8334 2.75169Z" fill="black" fill-opacity="0.54"/>
                    </svg>
                </i>
            </div>
            <div class="product-menu col-md-12">

                <ul>
                    <li class="product-menu-title">
                        <?=__('Notices')?>
                    </li>
                    <?php foreach($notices as $notice): ?>
                        <li  class="head-bell-li">
                            <div class="notificationMessage">
                                <?= $notice['message']?>
                            </div>
                            <div class="createdAt">
                                <?= $notice['created']->i18nFormat('dd-MM-yyyy - hh:mm:ss')?>
                            </div>
                        </li>
                        <li class="divider"></li>
                    <?php endforeach; ?>
                    <?php /*<li class="product-menu-title notif">
                        <?=__('Workbench')?>
                    </li>
                    <li class="surveysList">
                        <div class="surveysText">
                            Опросы учреждения
                        </div>
                        <div class="date">
                            <i class="fa fa-calendar" aria-hidden="navbarIcons"></i>
                            <span class="label">2</span>
                        </div>
                    </li>*/ ?>
                </ul>
            </div>
        </div>
    </div>
</div>
