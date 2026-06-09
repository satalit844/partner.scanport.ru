<div class="sert-item {$certificate_state_class}" data-cert-state="{$certificate_state}" data-valid-to="{$certificate_valid_to_iso}">
    <div class="sert-item__head">
        <div class="sert-item__logo">
            <img src="theme/images/training/sert/sert-logo.svg" class="img-svg" alt="">
        </div>
        <div class="sert-item__body">
            <div class="sert-item__title">{$certificate_title}</div>
            <div class="sert-status">{$certificate_status_text}</div>
        </div>
    </div>

    <button type="button"
            class="sert-btn"
            data-img="{$certificate_preview}"
            data-file="{$certificate_file}"
            data-title="{$certificate_title}">
        <span>Скачать</span>
        <img src="theme/images/download-sert.svg" class="img-svg" alt="">
    </button>
</div>
