<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;
use yii\bootstrap5\BootstrapPluginAsset;

AppAsset::register($this);
BootstrapPluginAsset::register($this);

// Registrar los estilos del dashboard
$this->registerCssFile('@web/css/dashboard-styles.css', ['depends' => [AppAsset::class]]);
$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
$this->registerMetaTag(['name' => 'csrf-token', 'content' => Yii::$app->request->csrfToken], 'csrf-token');

// Detectar si estamos en una página de dashboard
$isDashboard = isset($this->params['is_dashboard']) && $this->params['is_dashboard'];

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-full bg-gray-50">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Heroicons para los iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/heroicons@1.0.6/outline/heroicons-outline.css">
    
    <?php $this->head() ?>
</head>
<body class="h-full">
<?php $this->beginBody() ?>
<?php $this->head() ?>

<div class="min-h-full">
    <!-- Header -->
    <header class="shadow-sm border-b border-gray-200" style="background-color: #030f1dff;">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Logo y título -->
                <div class="flex items-center">
                    <div class="flex items-center space-x-4">
                        <?php NavBar::begin([
                            'brandLabel' => Html::img('@web/images/logo.png', [
                                'alt' => Yii::$app->name,
                                'style' => 'height:60px;',
                            ]),
                            'brandUrl' => null,
                            'options' => [
                                'class' => 'navbar-expand-md navbar-dark bg-dark fixed-top'
                            ],
                        ]);  ?>
                        
                        <!-- Selector de fecha/período -->
                        <div class="flex items-center space-x-2 ml-8">
                            <svg class="w-5 h-5 text-white-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <select class="text-sm text-white-600 border-none bg-transparent focus:ring-0">
                                <option>August 2025</option>
                                <option>July 2025</option>
                                <option>June 2025</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Barra de búsqueda y usuario -->
                <div class="flex items-center space-x-4">
                    <!-- Barra de búsqueda -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               class="block w-80 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                               placeholder="Buscar...">
                    </div>

                    <!-- Notificaciones -->
                    <div class="relative">
                        <button class="p-2 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Ver notificaciones</span>
                            <div class="relative">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                </svg>
                                <!-- Punto rojo de notificación -->
                                <div class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></div>
                            </div>
                        </button>
                    </div>

                    <!-- Avatar del usuario -->
                    <div class="relative">
                        <button class="bg-yellow-600 rounded-full p-2 text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegación principal -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <!-- Dashboard -->
                <a href="<?= Url::to(['/site/index']) ?>" 
                   class="<?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    Dashboard
                </a>

                <!-- Ventas -->
                <a href="<?= Url::to(['/ventas/index']) ?>" 
                   class="<?= Yii::$app->controller->id === 'ventas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    Ventas
                </a>

                <!-- Diseño -->
                <a href="<?= Url::to(['/diseno/index']) ?>" 
                   class="<?= Yii::$app->controller->id === 'diseno' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="13.5" cy="6.5" r=".5"/>
                        <circle cx="17.5" cy="10.5" r=".5"/>
                        <circle cx="8.5" cy="7.5" r=".5"/>
                        <circle cx="6.5" cy="12.5" r=".5"/>
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
                    </svg>
                    Diseño
                </a>

                <!-- Producción -->
                <a href="<?= Url::to(['/produccion/index']) ?>" 
                   class="<?= Yii::$app->controller->id === 'produccion' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6"/>
                        <path d="m15.5 3.5-1 1m-5 5-1 1m-3 5 1-1m5-5 1-1"/>
                        <path d="m20.5 8.5-1 1m-5 5-1 1m-3-11 1 1m5 5 1 1"/>
                        <path d="M23 12h-6M17 12H7m-6 0h6"/>
                    </svg>
                    Producción
                </a>

                <!-- Logística -->
                <a href="<?= Url::to(['/logistica/index']) ?>" 
                   class="<?= Yii::$app->controller->id === 'logistica' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                        <path d="M15 18H9"/>
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                        <circle cx="17" cy="18" r="2"/>
                        <circle cx="7" cy="18" r="2"/>
                    </svg>
                    Logística
                </a>

                <!-- Administración con submenú (solo para admin) -->
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?php
                    $isAdminUser = (Yii::$app->user->identity->hasPermission('admin', 'all') ?? false)
                        || (Yii::$app->user->identity->role->es_admin ?? 0);

                    // Activo si estás en admin/usuario/role/roles
                    $isAdminSectionActive = in_array(Yii::$app->controller->id, ['admin', 'usuario', 'role', 'roles'], true);
                    ?>
                    <?php if ($isAdminUser): ?>
                        <div class="relative inline-block text-left">
                            <button id="adminMenuButton"
                                    type="button"
                                    class="<?= $isAdminSectionActive
                                        ? 'border-red-500 text-red-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ?> inline-flex items-center px-1 pt-4 pb-4 border-b-2 text-sm font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Administración
                                <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div id="adminMenu"
                                 class="hidden absolute z-50 mt-2 w-44 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="py-1">
                                    <a href="<?= Url::to(['/admin/users']) ?>"
                                       class="block px-4 py-2 text-sm <?= Yii::$app->controller->id === 'admin' ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100' ?>">
                                        Usuarios
                                    </a>
                                    <!-- Ajusta la ruta según tu controlador real de roles: /roles/index o /role/index -->
                                    <a href="<?= Url::to(['/role/index']) ?>"
                                       class="block px-4 py-2 text-sm <?= in_array(Yii::$app->controller->id, ['roles', 'role'], true) ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100' ?>">
                                        Roles
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Script para abrir/cerrar el dropdown -->
                        <script>
                            (function () {
                                var btn = document.getElementById('adminMenuButton');
                                var menu = document.getElementById('adminMenu');
                                if (!btn || !menu) return;

                                function closeMenu() { menu.classList.add('hidden'); }
                                function toggleMenu() { menu.classList.toggle('hidden'); }

                                btn.addEventListener('click', function (e) {
                                    e.stopPropagation();
                                    toggleMenu();
                                });
                                document.addEventListener('click', function () {
                                    closeMenu();
                                });
                                document.addEventListener('keydown', function (e) {
                                    if (e.key === 'Escape') closeMenu();
                                });
                            })();
                        </script>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <?= $content ?>
    </main>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>