Патч: training access + progress foundation

Что внутри:
1. Вкладка "Доступ" в карточке курса.
2. CRUD доступов по пользователям и группам MODX.
3. Синхронизация training_user_courses по активным доступам.
4. Сервис TrainingProgressService:
   - hasCourseAccess()
   - collectAccessibleUserIds()
   - syncUserCourses()
   - ensureUserCourse()
   - getCourseModules()
   - saveModuleProgress()
   - recalculateUserCourse()

Что заменить вручную:
- training/controllers/home.class.php
- training/js/mgr/widgets/course.tabs.js

Что добавить:
- training/js/mgr/widgets/course.access.grid.js
- training/model/training/services/trainingprogress.class.php
- training/processors/mgr/course/access/*

Важно:
- Я не менял схему БД: текущих таблиц достаточно.
- В сервисе прогресс курса считается по обязательным активным модулям, а если их нет — по всем активным.
- В syncUserCourses() пользователи без актуального доступа получают status=revoked, если курс еще не completed.

Следующий шаг после установки этого патча:
1. Проверить вкладку Доступ в CMP.
2. Проверить syncusers.
3. Добавить web-процессоры progress/ping и course/getplayerdata на основе TrainingProgressService.
