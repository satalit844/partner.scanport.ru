<?php
$user = $modx->getObject('modUser', ['id' => $modx->user->id]);
if (!empty($user)) {
    $fields = explode(',', $fields);
    if (count($fields) > 1) { 
        $output = [];
        foreach ($fields as $field) {
            $output[$field] = $user->get($field);
        }
        return $output;
    } else {
        $partner_lic = $user->get($fields[0]);
        return $partner_lic;
    }
}