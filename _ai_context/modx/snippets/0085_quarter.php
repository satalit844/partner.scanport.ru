<?php
function getQuarterEndDate() {
    $currentMonth = date('n');
    $currentYear = date('Y');
    if ($currentMonth >= 1 && $currentMonth <= 3) {
        $endMonth = 3;
    } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
        $endMonth = 6;
    } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
        $endMonth = 9;
    } else {
        $endMonth = 12;
    }
    $endDate = date("t", strtotime("$currentYear-$endMonth-01"));
    $quarterEndDate = date("d.m.Y", strtotime("$currentYear-$endMonth-$endDate"));
    return $quarterEndDate;
}

return getQuarterEndDate();