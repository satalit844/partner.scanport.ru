<div class="section-block training-type certificates-page" data-certificates-page>
    <div class="certificates-page__top">
        <div class="certificates-page__title title-training">{$page_title}</div>
        {$toolbar_html}
    </div>

    <div class="certificates-page__filters" role="tablist" aria-label="Фильтр сертификатов">
        <button type="button" class="cert-filter__chip is-active" data-cert-filter="all" aria-selected="true">Все</button>
        <button type="button" class="cert-filter__chip" data-cert-filter="active" aria-selected="false">Действует</button>
        <button type="button" class="cert-filter__chip" data-cert-filter="expired" aria-selected="false">Истек</button>
    </div>

    <div class="certificates-page__subtitle subtitle">{$page_subtitle}</div>

    <div class="sert-items certificates-page__items">
        {$items_html}
    </div>

    {$modal_html}
</div>
