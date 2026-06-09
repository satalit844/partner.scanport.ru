<?php
/**
 * Renders the whole left training-aware navigation as <ul>...</ul>.
 * Safe for multiple calls in one template: no global function declarations.
 */
if (!isset($modx) || !$modx instanceof modX) {
    return '';
}

$contextKey = $modx->context ? $modx->context->get('key') : 'web';
$excludeRaw = trim((string)$modx->getOption('resources', $scriptProperties, ''));
$outerClass = trim((string)$modx->getOption('outerClass', $scriptProperties, 'nav flex-column flex-nowrap mb-auto pt-4 gap-lg-2 pe-1'));

$excluded = [];
foreach (array_filter(array_map('trim', explode(',', $excludeRaw))) as $part) {
    if ($part === '') {
        continue;
    }
    $id = (int)$part;
    if ($id < 0) {
        $excluded[abs($id)] = true;
    }
}

$esc = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$resourceTitle = static function (modResource $resource) {
    $menutitle = trim((string)$resource->get('menutitle'));
    return $menutitle !== '' ? $menutitle : (string)$resource->get('pagetitle');
};

$isUnder = static function (modX $modx, $resourceId, $parentId) {
    $resourceId = (int)$resourceId;
    $parentId = (int)$parentId;
    if ($resourceId <= 0 || $parentId <= 0) {
        return false;
    }
    if ($resourceId === $parentId) {
        return true;
    }

    $seen = [];
    while ($resourceId > 0 && empty($seen[$resourceId])) {
        $seen[$resourceId] = true;
        /** @var modResource $res */
        $res = $modx->getObject('modResource', $resourceId);
        if (!$res) {
            return false;
        }
        $resourceId = (int)$res->get('parent');
        if ($resourceId === $parentId) {
            return true;
        }
    }
    return false;
};

$resourceIcon = static function (modResource $resource) {
    try {
        return trim((string)$resource->getTVValue('img'));
    } catch (Throwable $e) {
        return '';
    }
};

$relativeUrl = static function (modX $modx, $id) {
    $uri = $modx->makeUrl((int)$id, '', '', 'full');
    $siteUrl = $modx->getOption('site_url');
    if ($siteUrl && strpos($uri, $siteUrl) === 0) {
        $uri = substr($uri, strlen($siteUrl));
    }
    return $uri;
};

$getMode = static function (modX $modx) {
    $path = MODX_CORE_PATH . 'components/training/elements/snippets/trainingMenuAccess.php';
    if (!is_file($path)) {
        return 'none';
    }

    $json = include $path;
    $data = json_decode((string)$json, true);
    if (!is_array($data) || empty($data['mode'])) {
        return 'none';
    }

    $mode = (string)$data['mode'];
    return in_array($mode, ['none', 'employee', 'manager', 'director'], true) ? $mode : 'none';
};

$renderSimpleItem = static function (modX $modx, modResource $resource, $currentId) use ($esc, $resourceTitle, $resourceIcon, $relativeUrl, $isUnder) {
    $id = (int)$resource->get('id');
    $uri = $relativeUrl($modx, $id);
    $title = $resourceTitle($resource);
    $img = $resourceIcon($resource);
    $active = $isUnder($modx, $currentId, $id) ? ' active' : '';
    $iconHtml = $img !== '' ? '<img src="' . $esc($img) . '" class="img-svg" alt="">' : '';

    return '<li class="nav-item">'
        . '<a href="' . $esc($uri) . '" class="nav-link' . $active . ' d-flex gap-2">'
        . $iconHtml
        . '<span>' . $esc($title) . '</span>'
        . '</a>'
        . '</li>';
};

$renderTrainingItem = static function (modX $modx, modResource $resource, $currentId) use ($esc, $resourceTitle, $resourceIcon, $relativeUrl, $isUnder, $getMode, $renderSimpleItem) {
    $mode = $getMode($modx);
    if ($mode === 'none') {
        return $renderSimpleItem($modx, $resource, $currentId);
    }

    $id = (int)$resource->get('id');
    $uri = $relativeUrl($modx, $id);
    $title = $resourceTitle($resource);
    $img = $resourceIcon($resource);
    $active = $isUnder($modx, $currentId, $id) ? ' active' : '';
    // Для пункта «Обучение» используем иконку из дизайн-системы, не TV ресурса.
    $iconHtml = '<img src="theme/images/study-university.svg" class="img-svg menu-ico" alt="">';

    $allItems = [
        ['id' => 150, 'title' => 'Все курсы'],
        ['id' => 151, 'title' => 'Мои курсы'],
        ['id' => 152, 'title' => 'Сертификаты'],
        ['id' => 153, 'title' => 'История'],
        ['id' => 155, 'title' => 'Управление курсами'],
        ['id' => 156, 'title' => 'Управление сертификатами'],
        ['id' => 157, 'title' => 'Управление историей'],
    ];

    if ($mode === 'employee') {
        $items = array_slice($allItems, 0, 4);
    } elseif ($mode === 'manager') {
        $items = [$allItems[0], $allItems[4], $allItems[5], $allItems[6]];
    } else {
        $items = $allItems;
    }

    $submenu = '';
    foreach ($items as $item) {
        /** @var modResource $res */
        $res = $modx->getObject('modResource', (int)$item['id']);
        if (!$res || (int)$res->get('deleted') === 1 || (int)$res->get('published') !== 1) {
            continue;
        }

        $itemId = (int)$item['id'];
        $linkTitle = trim((string)$res->get('menutitle')) ?: trim((string)$res->get('pagetitle')) ?: $item['title'];
        $link = $relativeUrl($modx, $itemId);
        $subActive = $isUnder($modx, $currentId, $itemId);
        $submenu .= '<li class="' . ($subActive ? 'is-active' : '') . '"><a href="' . $esc($link) . '"' . ($subActive ? ' aria-current="page"' : '') . '>' . $esc($linkTitle) . '</a></li>';
    }

    if ($submenu === '') {
        return $renderSimpleItem($modx, $resource, $currentId);
    }

    return '<li class="nav-item menu-toggle' . $active . '">'
        . '<div class="menu-button">'
        . '<a href="' . $esc($uri) . '" class="nav-link d-flex gap-2">'
        . $iconHtml
        . '<span>' . $esc($title) . '</span>'
        . '</a>'
        . '<button type="button" class="button-menu" aria-label="Открыть меню обучения">'
        . '<img src="theme/images/menu_arrow.svg" class="img-svg toggle-ico" alt="">'
        . '</button>'
        . '</div>'
        . '<ul class="sumbenu">' . $submenu . '</ul>'
        . '</li>';
};

$currentId = $modx->resource ? (int)$modx->resource->get('id') : 0;

$q = $modx->newQuery('modResource');
$q->where([
    'parent' => 0,
    'published' => 1,
    'deleted' => 0,
    'hidemenu' => 0,
    'context_key' => $contextKey,
]);
$q->sortby('menuindex', 'ASC');
$q->sortby('id', 'ASC');

$rows = $modx->getCollection('modResource', $q);
$html = '<ul class="' . $esc($outerClass) . '">';
foreach ($rows as $resource) {
    $id = (int)$resource->get('id');
    if (isset($excluded[$id])) {
        continue;
    }

    if ($id === 4) {
        $html .= $renderTrainingItem($modx, $resource, $currentId);
    } else {
        $html .= $renderSimpleItem($modx, $resource, $currentId);
    }
}
$html .= '</ul>';

return $html;
