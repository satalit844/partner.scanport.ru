# Training: уведомления о завершении курса

Дата фиксации: 2026-08-28.

## Что реализовано на production

- Настройки уведомления находятся в карточке курса, вкладка `Общие`.
- Для курса можно включить/выключить уведомление, задать получателей, тему и HTML-шаблон.
- Поддерживаемые плейсхолдеры:
  - `[[+user.id]]`
  - `[[+user.username]]`
  - `[[+user.fullname]]`
  - `[[+user.email]]`
  - `[[+course.id]]`
  - `[[+course.name]]`
  - `[[+course.url]]`
  - `[[+course.completedon]]`
- Отправка происходит только при реальном переходе `TrainingUserCourse` в `status=completed`.
- Ошибка почты не ломает завершение курса.
- Используется стандартный MODX `mail.modPHPMailer` и текущие системные SMTP-настройки.
- Защита от дублей: таблица `training_completion_notifications`, уникальный `user_course_id`.
- Уже завершившие курс пользователи не получают ретроактивные письма.
- `[[+certificate.url]]` намеренно не добавлен: выдача сертификата не является гарантированной частью события завершения курса.

## Изменённые runtime-файлы

Installer `training_completion_notification_apply_v3.php` вносит изменения в:

- `core/components/training/model/training/services/trainingprogress.class.php`
- `core/components/training/model/training/mysql/trainingcourse.map.inc.php`
- `core/components/training/model/schema/training.mysql.schema.xml`
- `core/components/training/processors/mgr/course/get.class.php`
- `core/components/training/processors/mgr/course/update.class.php`
- `assets/components/training/js/mgr/widgets/course.tabs.js`
- создаёт `core/components/training/model/training/services/trainingcompletionnotification.class.php`

Также добавляются поля курса:

- `completion_notify_enabled`
- `completion_notify_emails`
- `completion_notify_subject`
- `completion_notify_body`

и таблица `training_completion_notifications`.

## Проверка production

Контролируемый end-to-end тест был успешно выполнен 2026-08-28:

- production `recalculateUserCourse()` увидел переход `in_progress -> completed`;
- notification service сформировал тему/HTML из реальных данных пользователя и курса;
- `modPHPMailer` вернул успешную отправку;
- журнал получил `status=sent`;
- набор адресатов в журнале совпал с настройкой курса;
- временно изменённая строка `training_user_courses` была восстановлена полностью.

## Важное про MODX Console

MODX Console выполняет код через `eval()` и может удалить буквальный PHP opening tag, находящийся внутри текста исполняемого скрипта. Поэтому installer v3 формирует opening tag нового service-файла динамически через `chr(60) . chr(63) . 'php'`.

Не возвращать старый вариант installer v2, где service source начинался буквальным PHP opening tag внутри nowdoc.
