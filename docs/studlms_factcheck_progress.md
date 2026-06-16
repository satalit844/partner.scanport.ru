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

### PC-04 — структура курса на нешироком ПК

Статус: `done-host`

Файл:
- `theme/css/training.css`

Что сделано:
- на экранах до 1600px подпись типа материала (`Учебный материал`, `Тест`, `Практическое задание`) переносится под название урока/активности.
- разметка не менялась, потому что `.cs-item__type` уже находится внутри `.cs-item__txt`.

Рабочая CSS-логика:

```css
@media (max-width: 1600px) and (min-width: 1200px) {
    .course-structure-block .cs-item__txt {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        min-width: 0;
    }

    .course-structure-block .cs-item__title {
        width: 100%;
        min-width: 0;
    }

    .course-structure-block .cs-item__type {
        width: 100%;
        margin-left: 0;
        text-align: left;
    }
}
```

### PC-05 — модалка возобновления теста, названия кнопок

Статус: `done-host`

Что сделано:
- подписи кнопок в модалке возобновления теста приведены к нужному виду.

Ожидание по макету:
- `Сначала`
- `Продолжить`

### PC-06 / TABLET-03 — иконка скачать в сертификатах

Статус: `done-host`

Что сделано:
- иконка у кнопки `Скачать` в сертификатах приведена к дизайну.

Проверенные места:
- карточка сертификата;
- модалка сертификата.

### PC-07 / MOBILE-10 — модалка сертификата: кнопка скачать ниже сертификата

Статус: `done-host`

Что сделано:
- кнопка `Скачать` вынесена ниже сертификата и не лежит поверх превью.
- модалка сертификата приведена к нужному виду на ПК/мобилке.

### PC-08 — История: поиск справа

Статус: `ok-no-change`

Решение:
- по текущей проверке всё нормально, поиск справа не добавляем.

### PC-09 / TABLET-11 — Практическое задание: селектор табов

Статус: `done-host`

Файл:
- `theme/css/training.css`

Что сделано:
- селектор `Мои попытки / Комментарии` приведён к макету.
- активная вкладка стала фиолетовой, неактивная — белой с фиолетовой обводкой.

Дополнительно по странице практики:
- карточка задания приведена ближе к макету;
- медиа-блок задания выставлен квадратом `220×220` на ПК;
- на мобилке медиа-блок уменьшен.

### PC-10 — Практическое задание: `Показать больше`

Статус: `done-host`

Файлы:
- `theme/css/training.css`
- `theme/js/app-training.js`

Что сделано:
- высота свернутого описания задания выставлена `144px`;
- кнопка `Показать больше` показывается только если реальная высота текста больше `144px`;
- если текста мало, кнопка скрывается и затемнение не показывается;
- исправлено переключение текста кнопки: `Показать больше` ↔ `Скрыть`;
- убран конфликт двух обработчиков раскрытия, из-за которого кнопка переключалась неправильно.

### PC-11 — общий заголовок страницы

Статус: `ok-no-change`

Решение:
- по текущей проверке всё нормально, правки не нужны.

### PC-12 — модалка `Запросить курс`, жирность кнопки

Статус: `ok-no-change`

Решение:
- по текущей проверке всё нормально, правки не нужны.

### PC-13 — select/dropdown

Статус: `skipped-cancelled`

Решение:
- правку отменили;
- пока пропускаем;
- оставлено в списке, чтобы при необходимости вернуться позже.

### PC-14 — тест: расстояние между заголовком и подзаголовком

Статус: `ok-no-change`

Решение:
- по текущей проверке всё нормально, правки не нужны.

### PC-15 — инструкция перед тестом

Статус: `done-host`

Файлы:
- `core/components/training/elements/chunks/training/activity/instruction.tpl`
- `theme/css/usertest-factcheck-v4.css`

Что сделано:
- экран инструкции использует отдельный класс `tests-block--instruction`;
- текст инструкции выведен списком, а не одним абзацем;
- пункты `“Ответить”` и `Список вопросов` выделены жирным;
- `Проходной балл` и `Попытки` оставлены ниже списка без изменений;
- кнопки внизу не трогались.

## Следующий пункт

### PC-16 — модалка списка вопросов

Статус: `next`

Проблема:
- проверить модалку списка вопросов: сетку, колонки, закрытие, текущий вопрос.

Вероятные места:
- `theme/css/usertest-factcheck-v4.css`
- `theme/css/training.css`
- UserTest templates
