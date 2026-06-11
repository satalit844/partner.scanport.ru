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

### PC-03 / TABLET-01 / MOBILE-02 — описание курса, расстояние между абзацами

Статус: `done-host`

Файл:
- сниппет `trainingCoursePage`

Что нашли:
- описание хранится не в `content`, а в TV `desc` (`tv_id=24`, caption `Описание`, type `richtext`) ресурса курса.
- в TV уже хранится HTML с `<p>...</p>`.
- лишний разрыв на фронте давала строка `nl2br($courseDescription)`, которая превращала переносы между `<p>` в `<br><br>`.

Рабочая логика:

```php
$courseDescription = trim((string)$resource->getTVValue('desc'));
$courseDescriptionIsRichtext = $courseDescription !== '';

if ($courseDescription === '') {
    $courseDescription = trim((string)$resource->get('description'));
}

if ($courseDescription === '') {
    $courseDescription = 'Описание курса пока не заполнено.';
}

if (!$courseDescriptionIsRichtext) {
    $courseDescription = nl2br($courseDescription);
}
```

## Следующий пункт

### PC-04 — структура курса на нешироком ПК

Статус: `next`

Проблема:
- при раскрытом списке курсов на не самом широком экране некорректно отображаются подписи `Учебные материалы`, `Практическое задание`, `Тест`.

Ожидание:
- на небольших ПК сделать как на планшете: подпись под названием модуля.

Вероятный файл:
- `theme/css/training.css`
