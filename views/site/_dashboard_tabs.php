<?php
/* 
 * Archivo: views/site/_dashboard_tabs.php
 * Pestañas de navegación del dashboard
 */

use yii\helpers\Html;
use yii\helpers\Url;

$currentRoute = Yii::$app->requestedRoute;

$tabs = [
    [
        'id' => 'dashboard',
        'name' => 'Dashboard',
        'url' => ['/site/dashboard'],
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>'
    ],
    [
        'id' => 'ventas',
        'name' => 'Ventas',
        'url' => ['/ventas/index'],
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>'
    ],
    [
        'id' => 'proyectos',
        'name' => 'Proyectos',
        'url' => ['/proyectos/index'],
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4z"></path>'
    ],
    [
        'id' => 'produccion',
        'name' => 'Producción',
        'url' => ['/produccion/index'],
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>'
    ],
    [
        'id' => 'reportes',
        'name' => 'Reportes',
        'url' => ['/reportes/index'],
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>'
    ]
];

foreach ($tabs as $tab):
    $isActive = strpos($currentRoute, trim($tab['url'][0], '/')) !== false || 
                ($tab['id'] === 'dashboard' && $currentRoute === 'site/dashboard');
    ?>
    <?= Html::a(
        '<svg class="department-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . 
        $tab['icon'] . 
        '</svg>' .
        '<span>' . Html::encode($tab['name']) . '</span>',
        $tab['url'],
        [
            'class' => 'department-tab ' . ($isActive ? 'active' : ''),
            'encode' => false
        ]
    ) ?>
<?php endforeach; ?>


