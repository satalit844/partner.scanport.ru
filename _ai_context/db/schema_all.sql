-- AI context schema export v2
-- Generated: 2026-06-10T13:21:36+03:00



-- --------------------------------------------------------
-- Table: modx_partnersaccess_actiondom; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_actiondom` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_actions; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_actions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_category; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_context; rows: 5; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_context` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_elements; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_elements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_media_source; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_media_source` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_menus; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_namespace; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_namespace` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_permissions; rows: 243; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template` int unsigned NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `value` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `template` (`template`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_policies; rows: 13; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_policies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_general_ci,
  `parent` int unsigned NOT NULL DEFAULT '0',
  `template` int unsigned NOT NULL DEFAULT '0',
  `class` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `data` text COLLATE utf8mb4_general_ci,
  `lexicon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'permissions',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `parent` (`parent`),
  KEY `class` (`class`),
  KEY `template` (`template`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_policy_template_groups; rows: 6; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_policy_template_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_policy_templates; rows: 8; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_policy_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template_group` int unsigned NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_general_ci,
  `lexicon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'permissions',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_resource_groups; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_resource_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`,`target`,`principal`,`authority`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_resources; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_resources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersaccess_templatevars; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersaccess_templatevars` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `principal_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modPrincipal',
  `principal` int unsigned NOT NULL DEFAULT '0',
  `authority` int unsigned NOT NULL DEFAULT '9999',
  `policy` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `principal_class` (`principal_class`),
  KEY `principal` (`principal`),
  KEY `authority` (`authority`),
  KEY `policy` (`policy`),
  KEY `context_key` (`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersactiondom; rows: 9; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersactiondom` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `set` int NOT NULL DEFAULT '0',
  `action` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `xtype` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `container` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `rule` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_general_ci NOT NULL,
  `constraint` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `constraint_field` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `constraint_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `for_parent` tinyint unsigned NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `set` (`set`),
  KEY `action` (`action`),
  KEY `name` (`name`),
  KEY `active` (`active`),
  KEY `for_parent` (`for_parent`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersactions; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `controller` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `haslayout` tinyint unsigned NOT NULL DEFAULT '1',
  `lang_topics` text COLLATE utf8mb4_general_ci NOT NULL,
  `assets` text COLLATE utf8mb4_general_ci NOT NULL,
  `help_url` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `namespace` (`namespace`),
  KEY `controller` (`controller`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersactions_fields; rows: 80; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersactions_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'field',
  `tab` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `form` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `other` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `type` (`type`),
  KEY `tab` (`tab`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersactive_users; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersactive_users` (
  `internalKey` int NOT NULL DEFAULT '0',
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `lasthit` int NOT NULL DEFAULT '0',
  `id` int DEFAULT NULL,
  `action` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ip` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`internalKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscategories; rows: 40; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent` int unsigned DEFAULT '0',
  `category` varchar(45) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category` (`parent`,`category`),
  KEY `parent` (`parent`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscategories_closure; rows: 80; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscategories_closure` (
  `ancestor` int unsigned NOT NULL DEFAULT '0',
  `descendant` int unsigned NOT NULL DEFAULT '0',
  `depth` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor`,`descendant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersclass_map; rows: 9; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersclass_map` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `class` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `parent_class` varchar(120) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name_field` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'name',
  `path` tinytext COLLATE utf8mb4_general_ci,
  `lexicon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core:resource',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`),
  KEY `parent_class` (`parent_class`),
  KEY `name_field` (`name_field`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersclientconfig_context_value; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersclientconfig_context_value` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting` int NOT NULL DEFAULT '0',
  `context` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'web',
  `value` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersclientconfig_group; rows: 3; sensitive_data_exported: no
CREATE TABLE `modx_partnersclientconfig_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `sortorder` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersclientconfig_setting; rows: 9; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersclientconfig_setting` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `label` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `xtype` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `sortorder` int NOT NULL DEFAULT '0',
  `value` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `default` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `group` int DEFAULT '0',
  `options` mediumtext COLLATE utf8mb4_general_ci,
  `process_options` tinyint(1) NOT NULL DEFAULT '0',
  `source` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscollection_resource_template; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscollection_resource_template` (
  `collection_template` int unsigned NOT NULL,
  `resource_template` int unsigned NOT NULL,
  PRIMARY KEY (`collection_template`,`resource_template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscollection_selections; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscollection_selections` (
  `collection` int unsigned NOT NULL,
  `resource` int unsigned NOT NULL,
  `menuindex` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`collection`,`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscollection_settings; rows: 25; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscollection_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `collection` int unsigned NOT NULL,
  `template` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection` (`collection`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscollection_template_columns; rows: 11; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscollection_template_columns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `template` int unsigned NOT NULL,
  `label` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `hidden` tinyint unsigned NOT NULL DEFAULT '0',
  `sortable` tinyint unsigned NOT NULL DEFAULT '0',
  `width` int unsigned NOT NULL DEFAULT '100',
  `editor` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `renderer` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `php_renderer` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `position` int unsigned NOT NULL DEFAULT '0',
  `sort_type` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscollection_templates; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscollection_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `global_template` int NOT NULL DEFAULT '0',
  `bulk_actions` int NOT NULL DEFAULT '0',
  `allow_dd` int NOT NULL DEFAULT '1',
  `page_size` int NOT NULL DEFAULT '20',
  `sort_field` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'id',
  `sort_dir` varchar(4) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'asc',
  `sort_type` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `child_template` int unsigned DEFAULT NULL,
  `child_resource_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modDocument',
  `resource_type_selection` int NOT NULL DEFAULT '1',
  `tab_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'collections.children',
  `button_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'collections.children.create',
  `content_place` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'original',
  `view_for` int unsigned NOT NULL DEFAULT '0',
  `link_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'selections.create',
  `context_menu` varchar(512) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'view,edit,duplicate,publish,unpublish,-,delete,undelete,remove,-,unlink',
  `buttons` varchar(512) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open,view,edit,duplicate,publish:orange,unpublish,delete,undelete,remove,unlink',
  `allowed_resource_types` varchar(512) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `back_to_collection_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'collections.children.back_to_collection_label',
  `back_to_selection_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'selections.back_to_selection_label',
  `selection_create_sort` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'id:desc',
  `child_hide_from_menu` int DEFAULT NULL,
  `child_published` int DEFAULT NULL,
  `child_cacheable` int DEFAULT NULL,
  `child_searchable` int DEFAULT NULL,
  `child_richtext` int DEFAULT NULL,
  `child_content_type` int NOT NULL DEFAULT '0',
  `parent` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `child_content_disposition` int DEFAULT NULL,
  `permanent_sort_before` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `permanent_sort_after` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `selection_link_condition` text COLLATE utf8mb4_general_ci,
  `search_query_exclude_tvs` int NOT NULL DEFAULT '0',
  `search_query_exclude_tagger` int NOT NULL DEFAULT '0',
  `search_query_title_only` int NOT NULL DEFAULT '0',
  `show_quick_create` tinyint(1) NOT NULL DEFAULT '1',
  `quick_create_label` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'collections.children.quick_create',
  `fred_default_blueprint` varchar(36) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscontent_type; rows: 8; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscontent_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `description` tinytext COLLATE utf8mb4_general_ci,
  `mime_type` tinytext COLLATE utf8mb4_general_ci,
  `file_extensions` tinytext COLLATE utf8mb4_general_ci,
  `headers` mediumtext COLLATE utf8mb4_general_ci,
  `binary` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscontext; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscontext` (
  `key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` tinytext COLLATE utf8mb4_general_ci,
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`),
  KEY `name` (`name`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscontext_resource; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscontext_resource` (
  `context_key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `resource` int unsigned NOT NULL,
  PRIMARY KEY (`context_key`,`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerscontext_setting; rows: 4; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerscontext_setting` (
  `context_key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_general_ci,
  `xtype` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'textfield',
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `area` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editedon` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`context_key`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersdashboard; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersdashboard` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `hide_trees` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `hide_trees` (`hide_trees`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersdashboard_widget; rows: 5; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersdashboard_widget` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_general_ci,
  `namespace` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `lexicon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core:dashboards',
  `size` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'half',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `namespace` (`namespace`),
  KEY `lexicon` (`lexicon`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersdashboard_widget_placement; rows: 5; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersdashboard_widget_placement` (
  `dashboard` int unsigned NOT NULL DEFAULT '0',
  `widget` int unsigned NOT NULL DEFAULT '0',
  `rank` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`dashboard`,`widget`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersdocument_groups; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersdocument_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `document_group` int NOT NULL DEFAULT '0',
  `document` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `document_group` (`document_group`),
  KEY `document` (`document`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersdocumentgroup_names; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersdocumentgroup_names` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `private_memgroup` tinyint unsigned NOT NULL DEFAULT '0',
  `private_webgroup` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersef_categories; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersef_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tab_id` tinyint unsigned DEFAULT '0',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_templates` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_parents` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_resources` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_user_group` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_users` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `menuindex` tinyint unsigned DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersef_field_abs; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersef_field_abs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `field_id` tinyint unsigned DEFAULT '0',
  `caption` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `help` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `values` mediumtext COLLATE utf8mb4_general_ci,
  `sort` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dir` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `where` mediumtext COLLATE utf8mb4_general_ci,
  `number_allownegative` tinyint(1) DEFAULT '1',
  `number_minvalue` int DEFAULT '0',
  `number_maxvalue` int DEFAULT '0',
  `columns` tinyint unsigned DEFAULT '1',
  `disabled_dates` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `disabled_days` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `hide_time` tinyint(1) DEFAULT '0',
  `default` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `xtype` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `source` smallint unsigned DEFAULT '0',
  `source_path` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `table_id` tinyint unsigned DEFAULT '0',
  `tab_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `category_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `areas` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `index` tinyint unsigned NOT NULL DEFAULT '0',
  `ab_templates` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_parents` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_resources` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_user_group` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_users` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `menuindex` tinyint unsigned DEFAULT '0',
  `required` tinyint unsigned DEFAULT '1',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersef_fields; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersef_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `field_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `field_type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `field_null` tinyint unsigned NOT NULL DEFAULT '1',
  `field_default` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `menuindex` smallint NOT NULL DEFAULT '0',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersef_tabs; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersef_tabs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `index` tinyint unsigned DEFAULT '0',
  `ab_templates` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_parents` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_resources` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_user_group` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `ab_users` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `menuindex` tinyint unsigned DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerselement_property_sets; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerselement_property_sets` (
  `element` int unsigned NOT NULL DEFAULT '0',
  `element_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `property_set` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`element`,`element_class`,`property_set`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersextension_packages; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersextension_packages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `path` text COLLATE utf8mb4_general_ci,
  `table_prefix` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `service_class` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `service_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `namespace` (`namespace`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersfc_profiles; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersfc_profiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `rank` (`rank`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersfc_profiles_usergroups; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersfc_profiles_usergroups` (
  `usergroup` int NOT NULL DEFAULT '0',
  `profile` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`usergroup`,`profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersfc_sets; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersfc_sets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile` int NOT NULL DEFAULT '0',
  `action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `template` int NOT NULL DEFAULT '0',
  `constraint` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `constraint_field` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `constraint_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `profile` (`profile`),
  KEY `action` (`action`),
  KEY `active` (`active`),
  KEY `template` (`template`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersformit_forms; rows: 407; sensitive_data_exported: no
CREATE TABLE `modx_partnersformit_forms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `form` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `values` text COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` int NOT NULL DEFAULT '0',
  `encrypted` tinyint(1) NOT NULL DEFAULT '0',
  `encryption_type` int NOT NULL DEFAULT '1',
  `hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=446 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerslexicon_entries; rows: 34; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerslexicon_entries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_general_ci NOT NULL,
  `topic` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'default',
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `language` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en',
  `createdon` datetime DEFAULT NULL,
  `editedon` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `topic` (`topic`),
  KEY `namespace` (`namespace`),
  KEY `language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmanager_log; rows: 25259; sensitive_data_exported: no
CREATE TABLE `modx_partnersmanager_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user` int unsigned NOT NULL DEFAULT '0',
  `occurred` datetime DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `classKey` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `item` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_occurred` (`user`,`occurred`)
) ENGINE=InnoDB AUTO_INCREMENT=25260 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmedia_sources; rows: 4; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmedia_sources` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `class_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'sources.modFileMediaSource',
  `properties` mediumtext COLLATE utf8mb4_general_ci,
  `is_stream` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `class_key` (`class_key`),
  KEY `is_stream` (`is_stream`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmedia_sources_contexts; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmedia_sources_contexts` (
  `source` int NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'web',
  PRIMARY KEY (`source`,`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmedia_sources_elements; rows: 26; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmedia_sources_elements` (
  `source` int unsigned NOT NULL DEFAULT '0',
  `object_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modTemplateVar',
  `object` int unsigned NOT NULL DEFAULT '0',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'web',
  PRIMARY KEY (`source`,`object`,`object_class`,`context_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmember_groups; rows: 1421; sensitive_data_exported: no
CREATE TABLE `modx_partnersmember_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_group` int unsigned NOT NULL DEFAULT '0',
  `member` int unsigned NOT NULL DEFAULT '0',
  `role` int unsigned NOT NULL DEFAULT '1',
  `rank` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `role` (`role`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB AUTO_INCREMENT=1616 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmembergroup_names; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersmembergroup_names` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `parent` int unsigned NOT NULL DEFAULT '0',
  `rank` int unsigned NOT NULL DEFAULT '0',
  `dashboard` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `parent` (`parent`),
  KEY `rank` (`rank`),
  KEY `dashboard` (`dashboard`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmenus; rows: 65; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmenus` (
  `text` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `parent` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `action` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `menuindex` int unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8mb4_general_ci NOT NULL,
  `handler` text COLLATE utf8mb4_general_ci NOT NULL,
  `permissions` text COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  PRIMARY KEY (`text`),
  KEY `parent` (`parent`),
  KEY `action` (`action`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmigx_config_elements; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmigx_config_elements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int NOT NULL DEFAULT '0',
  `element_id` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  `createdby` int NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  `editedby` int NOT NULL DEFAULT '0',
  `editedon` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  `deletedon` datetime DEFAULT NULL,
  `deletedby` int NOT NULL DEFAULT '0',
  `published` tinyint unsigned NOT NULL DEFAULT '0',
  `publishedon` datetime DEFAULT NULL,
  `publishedby` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmigx_configs; rows: 6; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmigx_configs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `formtabs` text COLLATE utf8mb4_general_ci NOT NULL,
  `contextmenus` text COLLATE utf8mb4_general_ci NOT NULL,
  `actionbuttons` text COLLATE utf8mb4_general_ci NOT NULL,
  `columnbuttons` text COLLATE utf8mb4_general_ci NOT NULL,
  `filters` text COLLATE utf8mb4_general_ci NOT NULL,
  `extended` text COLLATE utf8mb4_general_ci NOT NULL,
  `permissions` text COLLATE utf8mb4_general_ci NOT NULL,
  `fieldpermissions` text COLLATE utf8mb4_general_ci NOT NULL,
  `columns` text COLLATE utf8mb4_general_ci NOT NULL,
  `createdby` int NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  `editedby` int NOT NULL DEFAULT '0',
  `editedon` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  `deletedon` datetime DEFAULT NULL,
  `deletedby` int NOT NULL DEFAULT '0',
  `published` tinyint unsigned NOT NULL DEFAULT '0',
  `publishedon` datetime DEFAULT NULL,
  `publishedby` int NOT NULL DEFAULT '0',
  `category` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmigx_elements; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmigx_elements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `createdby` int NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  `editedby` int NOT NULL DEFAULT '0',
  `editedon` datetime DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  `deletedon` datetime DEFAULT NULL,
  `deletedby` int NOT NULL DEFAULT '0',
  `published` tinyint unsigned NOT NULL DEFAULT '0',
  `publishedon` datetime DEFAULT NULL,
  `publishedby` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmigx_formtab_fields; rows: 22; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmigx_formtab_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int NOT NULL DEFAULT '0',
  `formtab_id` int NOT NULL DEFAULT '0',
  `field` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `pos` int NOT NULL DEFAULT '0',
  `description_is_code` tinyint unsigned NOT NULL DEFAULT '0',
  `inputTV` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `inputTVtype` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `validation` text COLLATE utf8mb4_general_ci NOT NULL,
  `configs` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `restrictive_condition` text COLLATE utf8mb4_general_ci NOT NULL,
  `display` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sourceFrom` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sources` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `inputOptionValues` text COLLATE utf8mb4_general_ci NOT NULL,
  `default` text COLLATE utf8mb4_general_ci NOT NULL,
  `extended` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmigx_formtabs; rows: 5; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmigx_formtabs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `config_id` int NOT NULL DEFAULT '0',
  `caption` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `pos` int NOT NULL DEFAULT '0',
  `print_before_tabs` tinyint unsigned NOT NULL DEFAULT '0',
  `extended` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_category_options; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_category_options` (
  `option_id` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `required` tinyint unsigned NOT NULL DEFAULT '0',
  `value` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`option_id`,`category_id`),
  KEY `rank` (`rank`),
  KEY `active` (`active`),
  KEY `required` (`required`),
  FULLTEXT KEY `value_ft` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_customer_profiles; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_customer_profiles` (
  `id` int unsigned NOT NULL,
  `account` decimal(12,2) DEFAULT '0.00',
  `spent` decimal(12,2) DEFAULT '0.00',
  `createdon` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `referrer_id` int unsigned DEFAULT '0',
  `referrer_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `referrer_code` (`referrer_code`),
  KEY `referrer_id` (`referrer_id`),
  KEY `spent` (`spent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_deliveries; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_deliveries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` varchar(11) COLLATE utf8mb4_general_ci DEFAULT '0',
  `weight_price` decimal(12,2) DEFAULT '0.00',
  `distance_price` decimal(12,2) DEFAULT '0.00',
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rank` tinyint unsigned DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `class` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `properties` text COLLATE utf8mb4_general_ci,
  `requires` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'email,receiver',
  `free_delivery_amount` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_delivery_payments; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_delivery_payments` (
  `delivery_id` int unsigned NOT NULL,
  `payment_id` int unsigned NOT NULL,
  PRIMARY KEY (`delivery_id`,`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_links; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_options; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `caption` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `measure_unit` tinytext COLLATE utf8mb4_general_ci,
  `category` int unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `key` (`type`),
  KEY `category` (`category`),
  FULLTEXT KEY `caption_ft` (`caption`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_order_addresses; rows: 13894; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_order_addresses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  `receiver` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `index` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `metro` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `building` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entrance` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `floor` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `text_address` text COLLATE utf8mb4_general_ci,
  `comment` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13970 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_order_logs; rows: 3817; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_order_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `order_id` int unsigned NOT NULL DEFAULT '0',
  `timestamp` datetime DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `entry` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `ip` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3857 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_order_products; rows: 19539; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_order_products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `order_id` int unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `count` int unsigned DEFAULT '1',
  `price` decimal(12,2) DEFAULT '0.00',
  `weight` decimal(13,3) DEFAULT '0.000',
  `cost` decimal(12,2) DEFAULT '0.00',
  `options` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_order_statuses; rows: 5; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_order_statuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `color` char(6) COLLATE utf8mb4_general_ci DEFAULT '000000',
  `email_user` tinyint(1) DEFAULT '0',
  `email_manager` tinyint(1) DEFAULT '0',
  `subject_user` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `subject_manager` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `body_user` int DEFAULT '0',
  `body_manager` int DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `final` tinyint(1) DEFAULT '0',
  `fixed` tinyint(1) DEFAULT '0',
  `rank` int unsigned DEFAULT '0',
  `editable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_orders; rows: 13890; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `session_id` varchar(32) COLLATE utf8mb4_general_ci DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  `num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT '0.00',
  `cart_cost` decimal(12,2) DEFAULT '0.00',
  `delivery_cost` decimal(12,2) DEFAULT '0.00',
  `weight` decimal(13,3) DEFAULT '0.000',
  `status` int unsigned DEFAULT '0',
  `delivery` int unsigned DEFAULT '0',
  `payment` int unsigned DEFAULT '0',
  `context` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'web',
  `order_comment` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  `type` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=13970 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_payments; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersms2_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` varchar(11) COLLATE utf8mb4_general_ci DEFAULT '0',
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rank` int unsigned DEFAULT '0',
  `active` tinyint unsigned DEFAULT '1',
  `class` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_product_categories; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_product_categories` (
  `product_id` int unsigned NOT NULL,
  `category_id` int unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_product_files; rows: 66; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_product_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `source` int unsigned DEFAULT '1',
  `parent` int unsigned DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdon` datetime DEFAULT NULL,
  `createdby` int unsigned DEFAULT '0',
  `rank` tinyint unsigned DEFAULT '0',
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `properties` text COLLATE utf8mb4_general_ci,
  `hash` char(40) COLLATE utf8mb4_general_ci DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `type` (`type`),
  KEY `parent` (`parent`),
  KEY `hash` (`hash`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_product_links; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_product_links` (
  `link` int unsigned NOT NULL,
  `master` int unsigned NOT NULL,
  `slave` int unsigned NOT NULL,
  PRIMARY KEY (`link`,`master`,`slave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_product_options; rows: 93; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_product_options` (
  `product_id` int unsigned NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  KEY `product` (`product_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_products; rows: 19; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `article` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(12,2) DEFAULT '0.00',
  `old_price` decimal(12,2) DEFAULT '0.00',
  `weight` decimal(13,3) DEFAULT '0.000',
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor` int unsigned DEFAULT '0',
  `made_in` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `new` tinyint unsigned DEFAULT '0',
  `popular` tinyint unsigned DEFAULT '0',
  `favorite` tinyint unsigned DEFAULT '0',
  `tags` text COLLATE utf8mb4_general_ci,
  `color` text COLLATE utf8mb4_general_ci,
  `size` text COLLATE utf8mb4_general_ci,
  `source` int unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `article` (`article`),
  KEY `price` (`price`),
  KEY `old_price` (`old_price`),
  KEY `vendor` (`vendor`),
  KEY `new` (`new`),
  KEY `favorite` (`favorite`),
  KEY `popular` (`popular`),
  KEY `made_in` (`made_in`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersms2_vendors; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersms2_vendors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rank` int unsigned DEFAULT '0',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `resource` int unsigned DEFAULT '0',
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fax` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsexportusersexcel_profile; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersmsexportusersexcel_profile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `namespace_path` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `classKey` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `tab` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `limit` int unsigned DEFAULT '0',
  `start` int unsigned DEFAULT '0',
  `sort` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dir` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `last_start_run` int DEFAULT NULL,
  `last_end_run` int DEFAULT NULL,
  `date_process` tinyint unsigned DEFAULT '0',
  `date_format` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '-',
  `classExport` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `classExportList` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'xls,xlsx,csv,json',
  `area` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `delimiter` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '',
  `source` int unsigned DEFAULT '0',
  `path` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dependent_profile` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `processor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `filename` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `download` tinyint unsigned DEFAULT '0',
  `remove` tinyint unsigned DEFAULT '0',
  `line_grouping` tinyint unsigned DEFAULT '0',
  `line_grouping_show` tinyint unsigned DEFAULT '0',
  `head_process` tinyint(1) DEFAULT '0',
  `head_color` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `head_all` tinyint(1) DEFAULT '0',
  `head_freezepane` tinyint(1) DEFAULT '0',
  `hide_colump` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `height` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `width` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `where` text COLLATE utf8mb4_general_ci,
  `select` text COLLATE utf8mb4_general_ci,
  `leftjoin` text COLLATE utf8mb4_general_ci,
  `innerjoin` text COLLATE utf8mb4_general_ci,
  `style` text COLLATE utf8mb4_general_ci,
  `relatedObjects` text COLLATE utf8mb4_general_ci,
  `json_process` tinyint unsigned DEFAULT '0',
  `groupby` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `having` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsexportusersexcel_profile_fields; rows: 18; sensitive_data_exported: no
CREATE TABLE `modx_partnersmsexportusersexcel_profile_fields` (
  `profile_id` int unsigned NOT NULL,
  `field` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `width` int unsigned DEFAULT '20',
  `handler` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `alignment_horizontal` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `alignment_vertical` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `rank` int unsigned DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`profile_id`,`field`),
  KEY `profile_id` (`profile_id`),
  KEY `field` (`field`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsie_cron; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsie_cron` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `preset_id` int NOT NULL DEFAULT '0',
  `schedule` text COLLATE utf8mb4_general_ci,
  `description` text COLLATE utf8mb4_general_ci,
  `settings` text COLLATE utf8mb4_general_ci,
  `date_last_run` int NOT NULL DEFAULT '0',
  `active` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `preset_id` (`preset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsie_preset; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsie_preset` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `mode` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `service` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fields` text COLLATE utf8mb4_general_ci,
  `settings` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `service` (`service`),
  KEY `mode` (`mode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsie_task; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsie_task` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `preset_id` int NOT NULL,
  `cron_id` int NOT NULL DEFAULT '0',
  `pid` int NOT NULL DEFAULT '0',
  `restarted` int NOT NULL DEFAULT '0',
  `status` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'initiated',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `label` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `settings` text COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  `priority` int NOT NULL DEFAULT '0',
  `start_time` decimal(16,6) DEFAULT '0.000000',
  `finish_time` decimal(16,6) DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `preset_id` (`preset_id`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsop_modification_images; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsop_modification_images` (
  `mid` int unsigned NOT NULL DEFAULT '0',
  `image` int unsigned NOT NULL DEFAULT '0',
  `rank` tinyint unsigned DEFAULT '0',
  PRIMARY KEY (`mid`,`image`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsop_modification_options; rows: 93; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsop_modification_options` (
  `mid` int unsigned NOT NULL DEFAULT '0',
  `rid` int unsigned NOT NULL DEFAULT '0',
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`mid`,`rid`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersmsop_modifications; rows: 93; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersmsop_modifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `rid` int unsigned DEFAULT '0',
  `type` tinyint unsigned NOT NULL DEFAULT '1',
  `price` varchar(11) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `old_price` varchar(11) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `article` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '',
  `weight` decimal(13,3) NOT NULL DEFAULT '0.000',
  `count` int NOT NULL DEFAULT '0',
  `image` int unsigned DEFAULT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `rank` int unsigned NOT NULL DEFAULT '0',
  `sync_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sync_service` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `rid` (`rid`),
  KEY `article` (`article`),
  KEY `image` (`image`),
  KEY `active` (`active`),
  KEY `sync_id` (`sync_id`(250)),
  KEY `sync_service` (`sync_service`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersnamespaces; rows: 34; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersnamespaces` (
  `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `path` text COLLATE utf8mb4_general_ci,
  `assets_path` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersproperty_set; rows: 0; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersproperty_set` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category` int NOT NULL DEFAULT '0',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersregister_messages; rows: 336; sensitive_data_exported: no
CREATE TABLE `modx_partnersregister_messages` (
  `topic` int unsigned NOT NULL,
  `id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `created` datetime NOT NULL,
  `valid` datetime NOT NULL,
  `accessed` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `accesses` int unsigned NOT NULL DEFAULT '0',
  `expires` int NOT NULL DEFAULT '0',
  `payload` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `kill` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic`,`id`),
  KEY `created` (`created`),
  KEY `valid` (`valid`),
  KEY `accessed` (`accessed`),
  KEY `accesses` (`accesses`),
  KEY `expires` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersregister_queues; rows: 4; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersregister_queues` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersregister_topics; rows: 4; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersregister_topics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `queue` int unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `options` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssession; rows: 15540; sensitive_data_exported: no
CREATE TABLE `modx_partnerssession` (
  `id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `access` int unsigned NOT NULL,
  `data` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_content; rows: 118; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_content` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'document',
  `contentType` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'text/html',
  `pagetitle` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `longtitle` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `alias` varchar(191) COLLATE utf8mb4_general_ci DEFAULT '',
  `alias_visible` tinyint unsigned NOT NULL DEFAULT '1',
  `link_attributes` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `published` tinyint unsigned NOT NULL DEFAULT '0',
  `pub_date` int NOT NULL DEFAULT '0',
  `unpub_date` int NOT NULL DEFAULT '0',
  `parent` int NOT NULL DEFAULT '0',
  `isfolder` tinyint unsigned NOT NULL DEFAULT '0',
  `introtext` text COLLATE utf8mb4_general_ci,
  `content` mediumtext COLLATE utf8mb4_general_ci,
  `richtext` tinyint unsigned NOT NULL DEFAULT '1',
  `template` int NOT NULL DEFAULT '0',
  `menuindex` int NOT NULL DEFAULT '0',
  `searchable` tinyint unsigned NOT NULL DEFAULT '1',
  `cacheable` tinyint unsigned NOT NULL DEFAULT '1',
  `createdby` int NOT NULL DEFAULT '0',
  `createdon` int NOT NULL DEFAULT '0',
  `editedby` int NOT NULL DEFAULT '0',
  `editedon` int NOT NULL DEFAULT '0',
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  `deletedon` int NOT NULL DEFAULT '0',
  `deletedby` int NOT NULL DEFAULT '0',
  `publishedon` int NOT NULL DEFAULT '0',
  `publishedby` int NOT NULL DEFAULT '0',
  `menutitle` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `donthit` tinyint unsigned NOT NULL DEFAULT '0',
  `privateweb` tinyint unsigned NOT NULL DEFAULT '0',
  `privatemgr` tinyint unsigned NOT NULL DEFAULT '0',
  `content_dispo` tinyint(1) NOT NULL DEFAULT '0',
  `hidemenu` tinyint unsigned NOT NULL DEFAULT '0',
  `class_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'msProduct',
  `context_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'web',
  `content_type` int unsigned NOT NULL DEFAULT '1',
  `uri` text COLLATE utf8mb4_general_ci,
  `uri_override` tinyint(1) NOT NULL DEFAULT '0',
  `hide_children_in_tree` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_tree` tinyint(1) NOT NULL DEFAULT '1',
  `properties` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `published` (`published`),
  KEY `pub_date` (`pub_date`),
  KEY `unpub_date` (`unpub_date`),
  KEY `parent` (`parent`),
  KEY `isfolder` (`isfolder`),
  KEY `template` (`template`),
  KEY `menuindex` (`menuindex`),
  KEY `searchable` (`searchable`),
  KEY `cacheable` (`cacheable`),
  KEY `hidemenu` (`hidemenu`),
  KEY `class_key` (`class_key`),
  KEY `context_key` (`context_key`),
  KEY `uri` (`uri`(191)),
  KEY `uri_override` (`uri_override`),
  KEY `hide_children_in_tree` (`hide_children_in_tree`),
  KEY `show_in_tree` (`show_in_tree`),
  KEY `cache_refresh_idx` (`parent`,`menuindex`,`id`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`longtitle`,`description`,`introtext`,`content`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_htmlsnippets; rows: 106; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_htmlsnippets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` int unsigned NOT NULL DEFAULT '0',
  `property_preprocess` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Chunk',
  `editor_type` int NOT NULL DEFAULT '0',
  `category` int NOT NULL DEFAULT '0',
  `cache_type` tinyint(1) NOT NULL DEFAULT '0',
  `snippet` mediumtext COLLATE utf8mb4_general_ci,
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  `static` tinyint unsigned NOT NULL DEFAULT '0',
  `static_file` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`),
  KEY `locked` (`locked`),
  KEY `static` (`static`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_plugin_events; rows: 114; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_plugin_events` (
  `pluginid` int NOT NULL DEFAULT '0',
  `event` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '0',
  `propertyset` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pluginid`,`event`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_plugins; rows: 37; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_plugins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` int unsigned NOT NULL DEFAULT '0',
  `property_preprocess` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editor_type` int NOT NULL DEFAULT '0',
  `category` int NOT NULL DEFAULT '0',
  `cache_type` tinyint(1) NOT NULL DEFAULT '0',
  `plugincode` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  `disabled` tinyint unsigned NOT NULL DEFAULT '0',
  `moduleguid` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `static` tinyint unsigned NOT NULL DEFAULT '0',
  `static_file` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`),
  KEY `locked` (`locked`),
  KEY `disabled` (`disabled`),
  KEY `static` (`static`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_snippets; rows: 111; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_snippets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` int unsigned NOT NULL DEFAULT '0',
  `property_preprocess` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editor_type` int NOT NULL DEFAULT '0',
  `category` int NOT NULL DEFAULT '0',
  `cache_type` tinyint(1) NOT NULL DEFAULT '0',
  `snippet` mediumtext COLLATE utf8mb4_general_ci,
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  `moduleguid` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `static` tinyint unsigned NOT NULL DEFAULT '0',
  `static_file` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`),
  KEY `locked` (`locked`),
  KEY `moduleguid` (`moduleguid`),
  KEY `static` (`static`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_templates; rows: 17; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` int unsigned NOT NULL DEFAULT '0',
  `property_preprocess` tinyint unsigned NOT NULL DEFAULT '0',
  `templatename` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Template',
  `editor_type` int NOT NULL DEFAULT '0',
  `category` int NOT NULL DEFAULT '0',
  `icon` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template_type` int NOT NULL DEFAULT '0',
  `content` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  `static` tinyint unsigned NOT NULL DEFAULT '0',
  `static_file` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `templatename` (`templatename`),
  KEY `category` (`category`),
  KEY `locked` (`locked`),
  KEY `static` (`static`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_tmplvar_access; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnerssite_tmplvar_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tmplvarid` int NOT NULL DEFAULT '0',
  `documentgroup` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tmplvar_template` (`tmplvarid`,`documentgroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_tmplvar_contentvalues; rows: 146; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_tmplvar_contentvalues` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tmplvarid` int NOT NULL DEFAULT '0',
  `contentid` int NOT NULL DEFAULT '0',
  `value` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tv_cnt` (`tmplvarid`,`contentid`),
  KEY `tmplvarid` (`tmplvarid`),
  KEY `contentid` (`contentid`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_tmplvar_templates; rows: 44; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_tmplvar_templates` (
  `tmplvarid` int NOT NULL DEFAULT '0',
  `templateid` int NOT NULL DEFAULT '0',
  `rank` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`tmplvarid`,`templateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssite_tmplvars; rows: 26; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssite_tmplvars` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `source` int unsigned NOT NULL DEFAULT '0',
  `property_preprocess` tinyint unsigned NOT NULL DEFAULT '0',
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `caption` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editor_type` int NOT NULL DEFAULT '0',
  `category` int NOT NULL DEFAULT '0',
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `elements` text COLLATE utf8mb4_general_ci,
  `rank` int NOT NULL DEFAULT '0',
  `display` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `default_text` mediumtext COLLATE utf8mb4_general_ci,
  `properties` text COLLATE utf8mb4_general_ci,
  `input_properties` text COLLATE utf8mb4_general_ci,
  `output_properties` text COLLATE utf8mb4_general_ci,
  `static` tinyint unsigned NOT NULL DEFAULT '0',
  `static_file` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`),
  KEY `locked` (`locked`),
  KEY `rank` (`rank`),
  KEY `static` (`static`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssystem_eventnames; rows: 278; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssystem_eventnames` (
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `service` tinyint unsigned NOT NULL DEFAULT '0',
  `groupname` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerssystem_settings; rows: 562; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerssystem_settings` (
  `key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_general_ci NOT NULL,
  `xtype` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'textfield',
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `area` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editedon` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstelegram_bots; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstelegram_bots` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `token` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '',
  `webhook` varchar(500) COLLATE utf8mb4_general_ci DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `snippet` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `updatedon` int NOT NULL DEFAULT '0',
  `createdon` int NOT NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `webhook_install` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `snippet` (`snippet`),
  KEY `token` (`token`),
  KEY `active` (`active`),
  KEY `webhook_install` (`webhook_install`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstelegram_commands; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstelegram_commands` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int unsigned DEFAULT '0',
  `command` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `snippet` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `description` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '',
  `install` tinyint(1) DEFAULT '0',
  `updatedon` int NOT NULL DEFAULT '0',
  `createdon` int NOT NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `install` (`install`),
  KEY `snippet` (`snippet`),
  KEY `command` (`command`),
  KEY `bot_id` (`bot_id`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstelegram_users; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnerstelegram_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int unsigned DEFAULT '0',
  `user_id` int unsigned DEFAULT '0',
  `telegram_id` int unsigned DEFAULT '0',
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `language_code` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `updatedon` int NOT NULL DEFAULT '0',
  `createdon` int NOT NULL DEFAULT '0',
  `is_bot` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `bot_id` (`bot_id`),
  KEY `user_id` (`user_id`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_certificate_templates; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_certificate_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `template_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template_preview` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `output_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `page_no` int unsigned NOT NULL DEFAULT '1',
  `fullname_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_font_size` decimal(10,2) NOT NULL DEFAULT '28.00',
  `fullname_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `fullname_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `course_title_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_font_size` decimal(10,2) NOT NULL DEFAULT '24.00',
  `course_title_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `course_title_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `completed_date_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_font_size` decimal(10,2) NOT NULL DEFAULT '20.00',
  `completed_date_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `completed_date_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `date_format` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'd.m.Y',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_course_access; rows: 5; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_course_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `principal_type` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `principal_id` int unsigned NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `usergroup_id` int unsigned NOT NULL DEFAULT '0',
  `assigned_by` int unsigned NOT NULL DEFAULT '0',
  `active_from` datetime DEFAULT NULL,
  `active_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_principal` (`course_id`,`principal_type`,`principal_id`),
  KEY `course_id` (`course_id`),
  KEY `user_id` (`user_id`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `principal` (`principal_type`,`principal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_courses; rows: 2; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_sequential` tinyint(1) NOT NULL DEFAULT '1',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_lesson_videos; rows: 452; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_lesson_videos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `preview_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_sort` (`lesson_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=529 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_module_lessons; rows: 27; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_module_lessons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `source_video` varchar(255) NOT NULL DEFAULT '',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `video_status` varchar(32) NOT NULL DEFAULT 'none',
  `source_presentation` varchar(255) NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) NOT NULL DEFAULT '',
  `slides_dir` varchar(255) NOT NULL DEFAULT '',
  `presentation_status` varchar(32) NOT NULL DEFAULT 'none',
  `preview_image` varchar(255) NOT NULL DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `module_sort` (`module_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_module_slides; rows: 471; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_module_slides` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `slide_no` int unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `timecode_ms` bigint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_slide` (`lesson_video_id`,`slide_no`),
  KEY `module_id` (`module_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_timecode` (`lesson_id`,`timecode_ms`),
  KEY `lesson_video_id` (`lesson_video_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_module_videos; rows: 1356; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_module_videos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `quality` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `mime` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'video/mp4',
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `width` int unsigned NOT NULL DEFAULT '0',
  `height` int unsigned NOT NULL DEFAULT '0',
  `bitrate` int unsigned NOT NULL DEFAULT '0',
  `filesize` bigint unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_quality` (`lesson_video_id`,`quality`),
  KEY `module_id` (`module_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_video_id` (`lesson_video_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_modules; rows: 6; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_modules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `resource_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource_id` (`resource_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_practice_attempts; rows: 12; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_practice_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `test_link_id` int unsigned NOT NULL DEFAULT '0',
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `attempt_num` int unsigned NOT NULL DEFAULT '1',
  `attempt_no` int unsigned NOT NULL DEFAULT '1',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `deadline_at` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `max_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `review_comment` text COLLATE utf8mb4_general_ci,
  `reviewer_user_id` int unsigned NOT NULL DEFAULT '0',
  `submittedon` datetime DEFAULT NULL,
  `reviewedon` datetime DEFAULT NULL,
  `reviewedby` int unsigned NOT NULL DEFAULT '0',
  `is_latest` tinyint unsigned NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test_link_id` (`test_link_id`),
  KEY `course_module_user` (`course_id`,`module_id`,`user_id`),
  KEY `status` (`status`),
  KEY `practice_user` (`practice_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_practice_files; rows: 11; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_practice_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL DEFAULT '0',
  `message_id` int unsigned NOT NULL DEFAULT '0',
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `size` int unsigned NOT NULL DEFAULT '0',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `message_id` (`message_id`),
  KEY `practice_id` (`practice_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_practice_messages; rows: 38; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_practice_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL DEFAULT '0',
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `author_id` int unsigned NOT NULL DEFAULT '0',
  `author_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `sender_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `message` mediumtext COLLATE utf8mb4_general_ci,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `createdon` (`createdon`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_practices; rows: 8; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_practices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `template_file` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `template_file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `deadline_at` datetime DEFAULT NULL,
  `deadline_days` int unsigned NOT NULL DEFAULT '0',
  `allowed_extensions` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
  `max_file_size` int unsigned NOT NULL DEFAULT '52428800',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `rank` int unsigned NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  `editedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `module_id` (`module_id`),
  KEY `active` (`active`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_test_links; rows: 13; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstraining_test_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `usertest_test_id` int unsigned NOT NULL DEFAULT '0',
  `link_type` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'module',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `max_attempts` int unsigned NOT NULL DEFAULT '0',
  `min_pass_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `block_next_module_until_passed` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_link` (`course_id`,`module_id`,`usertest_test_id`,`link_type`),
  KEY `course_id` (`course_id`),
  KEY `module_id` (`module_id`),
  KEY `usertest_test_id` (`usertest_test_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_certificates; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_certificates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `template_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `user_course_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'issued',
  `fullname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `course_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `completedon` datetime DEFAULT NULL,
  `issuedon` datetime DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `preview_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user` (`course_id`,`user_id`),
  KEY `template_id` (`template_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `issuedon` (`issuedon`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_courses; rows: 8; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'assigned',
  `current_module_id` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed_modules` int unsigned NOT NULL DEFAULT '0',
  `total_modules` int unsigned NOT NULL DEFAULT '0',
  `startedon` datetime DEFAULT NULL,
  `completedon` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user` (`course_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_lesson_progress; rows: 48; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_lesson_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_user` (`lesson_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_lesson_video_progress; rows: 682; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_lesson_video_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_user` (`lesson_video_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`),
  KEY `module_user` (`module_id`,`user_id`),
  KEY `lesson_user` (`lesson_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=683 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_manager_link; rows: 5; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_manager_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `manager_user_id` int unsigned NOT NULL DEFAULT '0',
  `employee_user_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `createdby` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `manager_employee` (`manager_user_id`,`employee_user_id`),
  KEY `manager_user_id` (`manager_user_id`),
  KEY `employee_user_id` (`employee_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_module_progress; rows: 24; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_module_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_user` (`module_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_user_test_status; rows: 8512; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_user_test_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `usertest_test_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `last_result_id` int unsigned NOT NULL DEFAULT '0',
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `last_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `last_passedon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `test_user` (`usertest_test_id`,`user_id`,`module_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8585 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstraining_usertest_bad_results_backup; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnerstraining_usertest_bad_results_backup` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `test_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  `test_time` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `session` text COLLATE utf8mb4_general_ci,
  `invite_id` int NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `status_id` (`status_id`),
  KEY `invite_id` (`invite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstransport_packages; rows: 34; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstransport_packages` (
  `signature` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `installed` datetime DEFAULT NULL,
  `state` tinyint unsigned NOT NULL DEFAULT '1',
  `workspace` int unsigned NOT NULL DEFAULT '0',
  `provider` int unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint unsigned NOT NULL DEFAULT '0',
  `source` tinytext COLLATE utf8mb4_general_ci,
  `manifest` text COLLATE utf8mb4_general_ci,
  `attributes` mediumtext COLLATE utf8mb4_general_ci,
  `package_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `metadata` text COLLATE utf8mb4_general_ci,
  `version_major` smallint unsigned NOT NULL DEFAULT '0',
  `version_minor` smallint unsigned NOT NULL DEFAULT '0',
  `version_patch` smallint unsigned NOT NULL DEFAULT '0',
  `release` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `release_index` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`signature`),
  KEY `workspace` (`workspace`),
  KEY `provider` (`provider`),
  KEY `disabled` (`disabled`),
  KEY `package_name` (`package_name`),
  KEY `version_major` (`version_major`),
  KEY `version_minor` (`version_minor`),
  KEY `version_patch` (`version_patch`),
  KEY `release` (`release`),
  KEY `release_index` (`release_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnerstransport_providers; rows: 4; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnerstransport_providers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_general_ci,
  `service_url` tinytext COLLATE utf8mb4_general_ci,
  `username` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `api_key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `priority` tinyint NOT NULL DEFAULT '10',
  `properties` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `api_key` (`api_key`),
  KEY `username` (`username`),
  KEY `active` (`active`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersuser_attributes; rows: 1426; sensitive_data_exported: no
CREATE TABLE `modx_partnersuser_attributes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `internalKey` int NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `surname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `patronymic` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_post` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `phone` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `mobilephone` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `blocked` tinyint unsigned NOT NULL DEFAULT '0',
  `blockeduntil` int NOT NULL DEFAULT '0',
  `blockedafter` int NOT NULL DEFAULT '0',
  `logincount` int NOT NULL DEFAULT '0',
  `lastlogin` int NOT NULL DEFAULT '0',
  `thislogin` int NOT NULL DEFAULT '0',
  `failedlogincount` int NOT NULL DEFAULT '0',
  `sessionid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `dob` int NOT NULL DEFAULT '0',
  `gender` int NOT NULL DEFAULT '0',
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `city` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `state` varchar(25) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `zip` varchar(25) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `fax` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `photo` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `website` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `extended` text COLLATE utf8mb4_general_ci,
  `field_inn` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_list_inn` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_company` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_list_company` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_iudiscount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rebeit` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_ceil` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_marketing` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_sold` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_summ` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `field_nfr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notresident` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `internalKey` (`internalKey`)
) ENGINE=InnoDB AUTO_INCREMENT=1621 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersuser_group_roles; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersuser_group_roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `description` mediumtext COLLATE utf8mb4_general_ci,
  `authority` int unsigned NOT NULL DEFAULT '9999',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `authority` (`authority`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersuser_group_settings; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersuser_group_settings` (
  `group` int unsigned NOT NULL DEFAULT '0',
  `key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  `xtype` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'textfield',
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `area` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editedon` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersuser_messages; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersuser_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `subject` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `sender` int NOT NULL DEFAULT '0',
  `recipient` int NOT NULL DEFAULT '0',
  `private` tinyint NOT NULL DEFAULT '0',
  `date_sent` datetime DEFAULT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersuser_settings; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersuser_settings` (
  `user` int NOT NULL DEFAULT '0',
  `key` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_general_ci,
  `xtype` varchar(75) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'textfield',
  `namespace` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'core',
  `area` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `editedon` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusers; rows: 1426; sensitive_data_exported: no
CREATE TABLE `modx_partnersusers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `cachepwd` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `class_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'modUser',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `remote_key` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remote_data` text COLLATE utf8mb4_general_ci,
  `hash_class` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'hashing.modNative',
  `salt` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `primary_group` int unsigned NOT NULL DEFAULT '0',
  `session_stale` text COLLATE utf8mb4_general_ci,
  `sudo` tinyint unsigned NOT NULL DEFAULT '0',
  `createdon` int NOT NULL DEFAULT '0',
  `user_1c` tinyint unsigned NOT NULL DEFAULT '1',
  `api` tinyint unsigned NOT NULL DEFAULT '0',
  `personal_data` tinyint unsigned NOT NULL DEFAULT '1',
  `partner_lic` tinyint unsigned NOT NULL DEFAULT '0',
  `webinar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `partner_events` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `news` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `software_updates` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `class_key` (`class_key`),
  KEY `remote_key` (`remote_key`),
  KEY `primary_group` (`primary_group`)
) ENGINE=InnoDB AUTO_INCREMENT=1621 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_answers; rows: 487; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `question_id` int DEFAULT NULL,
  `answer` text COLLATE utf8mb4_general_ci,
  `type_file` int DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `point` double DEFAULT NULL,
  `right` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `menuindex` (`menuindex`),
  KEY `right` (`right`)
) ENGINE=MyISAM AUTO_INCREMENT=2507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_categorys; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_categorys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_groups; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `parent` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_groups_link; rows: 1; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_groups_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `group_id` int DEFAULT NULL,
  `test_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_questions; rows: 119; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `test_id` int DEFAULT NULL,
  `parent` int NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `question` text COLLATE utf8mb4_general_ci,
  `type` int DEFAULT NULL,
  `type_file` int DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `extended` text COLLATE utf8mb4_general_ci,
  `max_point` double DEFAULT '0',
  `random_answer` tinyint(1) DEFAULT '0',
  `validate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM AUTO_INCREMENT=1133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_result_categorys; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_result_categorys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `result_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `cat_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`),
  KEY `category_id` (`category_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_result_invites; rows: 0; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_result_invites` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_pass` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  `test_page_id` int DEFAULT NULL,
  `auth_page_id` int DEFAULT NULL,
  `user_auth_code` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `url_scheme` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `url` varchar(400) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `result_id` int NOT NULL DEFAULT '0',
  `date_expired` datetime DEFAULT NULL,
  `send_email_if_empty_test` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_email` (`user_email`),
  KEY `user_auth_code` (`user_auth_code`),
  KEY `active` (`active`),
  KEY `send_email_if_empty_test` (`send_email_if_empty_test`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_result_status; rows: 4; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_result_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_resultanswers; rows: 212; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_resultanswers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `result_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `answer_id` int NOT NULL DEFAULT '0',
  `answer_ids` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `answer` text COLLATE utf8mb4_general_ci,
  `point` double DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=462 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_results; rows: 32; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `test_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  `test_time` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `session` text COLLATE utf8mb4_general_ci,
  `invite_id` int NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `status_id` (`status_id`),
  KEY `invite_id` (`invite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=243 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_test_question_link; rows: 112; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_test_question_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `test_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`)
) ENGINE=MyISAM AUTO_INCREMENT=3122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_test_variant_link; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_test_variant_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `use_custom_point` tinyint(1) DEFAULT '0',
  `start_point` double DEFAULT NULL,
  `end_point` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variant_id` (`variant_id`),
  KEY `test_id` (`test_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_tests; rows: 8; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_tests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `count_questions` int DEFAULT NULL,
  `count_questions_on_page` int DEFAULT NULL,
  `count_test_answer` int DEFAULT NULL,
  `time_test` int DEFAULT NULL,
  `type` int DEFAULT NULL,
  `use_category` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `customer` text COLLATE utf8mb4_general_ci,
  `appeal` text COLLATE utf8mb4_general_ci,
  `instruction` text COLLATE utf8mb4_general_ci,
  `use_block_q_number` tinyint(1) DEFAULT '1',
  `pub_date` int NOT NULL DEFAULT '0',
  `unpub_date` int NOT NULL DEFAULT '0',
  `variant_set_id` int DEFAULT NULL,
  `test_type` int NOT NULL DEFAULT '1',
  `ask_user_data` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(250)),
  KEY `active` (`active`),
  KEY `type` (`type`),
  KEY `variant_set_id` (`variant_set_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_variant_sets; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_variant_sets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersusertest_variants; rows: 2; sensitive_data_exported: no
CREATE TABLE `modx_partnersusertest_variants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `variant_set_id` int DEFAULT NULL,
  `start_point` double DEFAULT NULL,
  `end_point` double DEFAULT NULL,
  `passed` tinyint(1) DEFAULT '0',
  `result` text COLLATE utf8mb4_general_ci,
  `category_id` int NOT NULL DEFAULT '0',
  `haker` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `variant_set_id` (`variant_set_id`),
  KEY `test_id` (`test_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Table: modx_partnersworkspaces; rows: 1; sensitive_data_exported: limited_samples
CREATE TABLE `modx_partnersworkspaces` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `path` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `attributes` mediumtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
