Что заменять:
- training/js/mgr/widgets/course.access.grid.js
- training/processors/mgr/course/access/create.class.php
- training/processors/mgr/course/access/update.class.php
- training/processors/mgr/course/access/getlist.class.php
- training/processors/mgr/course/access/users.class.php
- training/processors/mgr/course/access/groups.class.php
- training/model/training/services/trainingprogress.class.php
- training/model/training/mysql/trainingcourseaccess.map.inc.php
- training/model/training/mysql/trainingusercourse.map.inc.php
- training/model/schema/training.mysql.schema.xml

SQL:
- выполнить training/_migrations/20260324_access_role.sql

Что исправлено:
- кнопки Редактировать / Удалить неактивны без выбора строки
- исправлено сохранение окна доступа
- расширен выбор групп/пользователей
- добавлены права: Директор / Сотрудник
- syncusers теперь синхронизирует и access_role в training_user_courses
