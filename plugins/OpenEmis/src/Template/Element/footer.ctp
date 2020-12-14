<footer>
    <?php /*
    <?php if (!$footerText) : ?>
    <?= __('Copyright') ?> &copy; 2015 - <?= date('Y') ?>  <!--?=$footerBrand ?-->. <?= __('All rights reserved.') ?>
    <?php else: ?>
    <?= str_replace('{{currentYear}}', date('Y'), $footerText) ?>
    <?php endif; ?>
    | <?= __('Version') . ' ' . $SystemVersion ?>
    */ ?>

    <?php echo __('MES KR 2020') ?>
</footer>

<button type="button" class="btn btn-danger iframe-close hide">
    <i class="fa fa-close"></i>
</button>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-160191256-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-160191256-1');
</script>
