Что входит в патч:
- новая xPDO-модель TrainingUserManagerLink
- новый грид на главной странице CMP: Руководители и сотрудники
- CRUD процессоры для связей директор -> сотрудник
- методы сервиса:
  - isAdminUser()
  - getManagedUserIds()
  - isManagerOfUser()
  - canAssignCourseToUser()
  - getAssignableUserIds()

Что заменить:
- training/controllers/home.class.php
- training/js/mgr/widgets/home.panel.js
- training/model/training/metadata.mysql.php
- training/model/training/services/trainingprogress.class.php

Что добавить:
- training/js/mgr/widgets/manager.link.grid.js
- training/processors/mgr/managerlink/*
- training/model/training/trainingusermanagerlink.class.php
- training/model/training/mysql/trainingusermanagerlink.class.php
- training/model/training/mysql/trainingusermanagerlink.map.inc.php
- training/_migrations/20260324_manager_links.sql

После установки:
1. создать таблицу из SQL-миграции
2. очистить кэш MODX
3. жёстко обновить менеджер
4. на главной странице Training появится второй грид под курсами
