<?php
function renderBreadcrumbs($items = []) {
    if (empty($items)) return '';

    $schema_items[] = [
        "@type" => "ListItem",
        "position" => 1,
        "name" => "Inicio",
        "item" => APP_URL . "/"
    ];

    $html = '<nav class="breadcrumb-nav" aria-label="Breadcrumb">';
    $html .= '<div class="container">';
    $html .= '<ul>';
    $html .= '<li><a href="' . APP_URL . '/"><i class="bi bi-house-door"></i> Inicio</a></li>';

    foreach ($items as $index => $item) {
        $pos = $index + 2;
        $is_last = ($index === count($items) - 1);
        
        $schema_items[] = [
            "@type" => "ListItem",
            "position" => $pos,
            "name" => $item['name'],
            "item" => $item['url'] ?? null
        ];

        $html .= '<li class="breadcrumb-sep"><i class="bi bi-chevron-right"></i></li>';
        if ($is_last || empty($item['url'])) {
            $html .= '<li class="active">' . htmlspecialchars($item['name']) . '</li>';
        } else {
            $html .= '<li><a href="' . $item['url'] . '">' . htmlspecialchars($item['name']) . '</a></li>';
        }
    }

    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</nav>';

    $breadcrumb_schema = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $schema_items
    ];

    global $json_ld_schema;
    if (!isset($json_ld_schema)) $json_ld_schema = [];
    if (is_array($json_ld_schema)) {
        $json_ld_schema[] = $breadcrumb_schema;
    }

    return $html;
}
?>
