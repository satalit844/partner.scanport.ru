<?php
/** @var modX $modx */
/* @var msExportUsersExcel $msExportUsersExcel */
switch ($modx->event->name) {
    case 'OnManagerPageBeforeRender':

        $namespace = $controller->config['namespace'];
        $cnr = $controller->config['controller'];

        // экспорт пользователей
        if ($namespace == "core" & $cnr == "security/user") {
            $msExportUsersExcel = $modx->getService('msExportUsersExcel', 'msExportUsersExcel', MODX_CORE_PATH . 'components/msexportusersexcel/model/');
            $msExportUsersExcel->ButtonRegistration('modx-form-users', $namespace, $cnr);
            $controller->addHTML('
                <script>
                    Ext.ComponentMgr.onAvailable("modx-panel-users", function () {
                        this.items[1].items[1].id = "modx-form-users"
                        this.items[1].items[1].baseParams = {
                            action: "security/user/getList",
                            usergroup: MODx.request["usergroup"] ? MODx.request["usergroup"] : "",
                	        sort: "id",
                            dir: "DESC"
                        };
                    });
                </script>
            ');

        }

        break;
    case 'OnMODXInit':
        $modx->lexicon->load('msexportusersexcel:export');
        break;
}