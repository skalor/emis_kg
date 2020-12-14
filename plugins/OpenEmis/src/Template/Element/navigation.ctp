<?php
$navigations = [];
if (isset($_navigations)) {
	$navigations = $_navigations;
}

$selectedLink = '';
if (isset($ControllerAction) && array_key_exists('selectedLink', $ControllerAction)) {
	$selectedLink = implode('-', $ControllerAction['selectedLink']);
}
?>
<?= $this->Html->css([
    'owl.carousel.min',
    'owl.theme.default.min',
    'slick',
    'slick-theme',
    //'custom',   # Переведено в styles.ctp
    //'customCSS' # Переведено в styles.ctp
]) ?>

<div class="left-menu">
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="sidebar-head">
            <a class="only_pc" href="<?=$this->Url->build('/')?>">
                <?php echo $this->Html->image($htmlLang=='ru'?'isou-logo.svg':'isou-logo_kg.svg', ['alt' => 'CakePHP', 'class' => 'sidebar-logo']); ?>
                
            </a>
            <a class="only_mobile" href="<?=$this->Url->build('/')?>">
                <svg  width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M49.8762 7.76197L44.5178 38.5863C44.5178 38.5863 9.50268 31.8057 0 50C9.38431 29.5612 42.3781 33.5058 42.3781 33.5058L45.9701 9.46811H42.7211V7.37676C46.313 6.85354 49.8762 7.76197 49.8762 7.76197Z" fill="#FF6C6C"></path>
                    <path d="M19.6363 18.1989L19.4436 28.096C18.0384 28.4631 16.6089 28.8756 15.1748 29.3487L15.3994 18.1367L19.6363 18.1989Z" fill="#FF6C6C"></path>
                    <path d="M27.3153 12.0495L27.0376 26.5404C25.6855 26.7512 24.256 27.0075 22.7886 27.3184L23.0921 11.9858L27.3153 12.0495Z" fill="#FF6C6C"></path>
                    <path d="M35.0094 5.27463L34.5996 25.6831C33.4099 25.765 31.9713 25.8924 30.3506 26.0835L30.7634 5.21094L35.0094 5.27463Z" fill="#FF6C6C"></path>
                    <path d="M41.6133 0L39.8135 31.3794C39.8135 31.3794 7.34629 28.8786 0 48.3787C6.98057 26.672 37.1154 26.672 37.1154 26.672L38.0153 3.13779C38.0153 3.13779 9.89723 3.92186 1.26409 19.3681C1.21707 19.474 1.1844 19.5857 1.16697 19.7003C1.19287 19.4029 1.254 19.1097 1.34907 18.8267C10.3479 0 41.6133 0 41.6133 0Z" fill="#1A4E87"></path>
                </svg>
            </a>
            <i class="fa fa-tidmes closeSidebar" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.8334 2.75169L23.2484 0.166687L13 10.415L2.75169 0.166687L0.166687 2.75169L10.415 13L0.166687 23.2484L2.75169 25.8334L13 15.585L23.2484 25.8334L25.8334 23.2484L15.585 13L25.8334 2.75169Z" fill="white" fill-opacity="1"></path>
                </svg>
            </i>

        </div>
		<?php echo $this->Navigation->render($navigations) ?>
	</div>

</div>

<script type="text/javascript">
    $(function() {
        var uah_min = $("#user_age_handler_min");
        var uah_max = $("#user_age_handler_max");
        $("#user_age").slider({
            range: true,
            min: 0,
            max: 150,
            values: [0, 150],
            create: function() {
                uah_min.text(0);
                uah_max.text(150);
            }
        });
    });

    $(document).ready(function() {
        $('#accordion').on('show.bs.collapse', function (e) {
            var target = e.target;
            var level = $(target).attr('data-level');
            var id = $(target).attr('id');
            $('[data-level=' + level + ']').each(function() {
                if ($(this).attr('id') != id && $(this).hasClass('in') == true) {
                    $(this).collapse('hide');
                }
            });
        });

        var action = '<?= $selectedLink ?>';
        $('#' + action).addClass('nav-active');


        var ul = $('#' + action).parents('ul');

        ul.each(function() {
            $(this).addClass('in');
            $(this).siblings('a.accordion-toggle').removeClass('collapsed');
        });
    });
</script>
