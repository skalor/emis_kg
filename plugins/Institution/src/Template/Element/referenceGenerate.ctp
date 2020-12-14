<span class="referenceGenerate dropdown">
    <span class="" data-toggle="dropdown">
        <a href="#" class="btn btn-xs btn-default icon-big" data-toggle="tooltip" data-placement="bottom" data-original-title="<?= __('Reference') ?>">
            <span><svg width="33" height="40" viewBox="0 0 26 15" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M4 1H22C23.1 1 24 1.9 24 3V17C24 18.1 23.1 19 22 19H4C2.9 19 2 18.1 2 17V3C2 1.9 2.9 1 4 1ZM3.49985 17.5H22.2499V2.50002H3.49985V17.5ZM5.9999 8.5L14.9999 8.49998V9.99998H5.9999V8.5ZM14.9999 5.5H5.9999V6.99999H14.9999V5.5Z" fill="#293845"/>
</svg>
</span>
        </a>
    </span>
    <ul class="dropdown-menu" style="min-width: 300px">
        <?php
            foreach ($referenceTypes as $key => $value):
                $url['referenceType'] = $key;
                $referenceUrl = $this->Url->build($url);
        ?>
            <li><a href="<?= $referenceUrl ?>"><?= $value ?></a></li>
        <?php endforeach; ?>
    </ul>
</span>
