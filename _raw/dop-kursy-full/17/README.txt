Пакет: web-processors под назначение курсов и scope директора

Что внутри:
- обновленный service: model/training/services/trainingprogress.class.php
- processors/web/_helpers.php
- processors/web/course/assignableusers.class.php
- processors/web/course/assign.class.php
- processors/web/course/unassign.class.php
- processors/web/course/mycourses.class.php

Что дает:
1. Директор может назначать курс только себе и своим сотрудникам из training_user_manager_link.
2. Админ может назначать курс любому пользователю.
3. Можно получать список пользователей, которым текущий пользователь вообще может назначать курсы.
4. Можно получать список моих курсов для фронта.

Как вызывать через connector.php:
- action=web/course/assignableusers
- action=web/course/assign
- action=web/course/unassign
- action=web/course/mycourses

Примеры:
1) Мои доступные пользователи:
   POST assets/components/training/connector.php
   action=web/course/assignableusers
   course_id=1

2) Назначить курс:
   POST assets/components/training/connector.php
   action=web/course/assign
   course_id=1
   user_id=1320
   access_role=employee
   active_from=2026-03-24 00:00:00
   active_to=2026-04-24 23:59:59
   is_active=1

3) Снять прямой доступ:
   POST assets/components/training/connector.php
   action=web/course/unassign
   course_id=1
   user_id=1320

4) Получить мои курсы:
   POST assets/components/training/connector.php
   action=web/course/mycourses
   recalculate=1
