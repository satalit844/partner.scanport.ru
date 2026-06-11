# StudLMS factcheck — прогресс

Фиксируем только то, что проверено на хосте.

## Готово на хосте

### PC-01 — левое меню: стрелка раскрытого раздела `Обучение`

Статус: `done-host`

Файл:
- `theme/css/style.css`

Что сделано:
- свернутое меню: стрелка вниз;
- раскрытый/active раздел: стрелка вверх.

Рабочая логика:

```css
.nav-item.menu-toggle .menu-button .button-menu {
    transform: rotate(180deg);
}

.nav-item.menu-toggle.active .menu-button .button-menu {
    transform: rotate(0deg);
}
```

### PC-02 — активный подпункт в левом меню

Статус: `done-host`

Файл:
- `theme/css/style.css`

Что сделано:
- если внутри раскрытого раздела `Обучение` активна страница, например `Сертификаты`, подпункт подсвечивается.

HTML-признак:

```html
<li class="is-active">
    <a href="obuchenie/sertifikaty/" aria-current="page">Сертификаты</a>
</li>
```

Рабочая CSS-логика:

```css
.nav-item.menu-toggle.active .sumbenu li.is-active > a,
.nav-item.menu-toggle.active .sumbenu a[aria-current="page"] {
    color: #A861C1;
    font-weight: 400;
}

.nav-item.menu-toggle.active .sumbenu li.is-active > a:hover,
.nav-item.menu-toggle.active .sumbenu a[aria-current="page"]:hover {
    color: #A861C1;
}
```

## Следующий пункт

### PC-03 / TABLET-01 / MOBILE-02 — описание курса, расстояние между абзацами

Статус: `next`

Файл:
- `theme/css/training.css`

Задача:
- уменьшить расстояние между абзацами в описании курса примерно в 2 раза.
