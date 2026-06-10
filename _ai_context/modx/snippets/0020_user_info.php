<?php
$user = $modx->getObject('modUser', ['id' => $modx->user->id]);
if (!empty($user)) {
    $item = explode(';', $user->get('webinar'));
    $check_all = ['webinar','partner_events','news','software_updates'];
    $webinar = explode(';', $user->get('webinar'));
    $partner_events = explode(';',$user->get('partner_events'));
    $news = explode(';',$user->get('news'));
    $software_updates = explode(';',$user->get('software_updates'));
    
    $output = array(
        'personal_data' => $user->get('personal_data'),
        'data' => [],
    );
    $data = [];
    foreach ($item as $key => $item) {
        $webinar[$key] = explode(':', $webinar[$key]);
        $partner_events[$key] = explode(':', $partner_events[$key]);
        $news[$key] = explode(':', $news[$key]);
        $software_updates[$key] = explode(':', $software_updates[$key]);
        
        $data[$key]['email'] = $webinar[$key][0];
        $data[$key]['webinar'] = array(
            'check' => $webinar[$key][1]
        );
        $data[$key]['partner_events'] = array(
            'check' => $partner_events[$key][1]
        );
        $data[$key]['news'] = array(
            'check' => $news[$key][1]
        );
        $data[$key]['software_updates'] = array(
            'check' => $software_updates[$key][1]
        );
    }
    $output = array(
        'personal_data' => $user->get('personal_data'),
        'data' => $data
    );
    return $output;
}