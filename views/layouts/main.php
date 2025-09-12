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
    <!-- Heroicons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/heroicons@1.0.6/outline/heroicons-outline.css">
    
    <?php $this->head() ?>
</head>
<body class="h-full bg-gray-50">
<?php $this->beginBody() ?>

<div class="min-h-full flex flex-col">
<div class="min-h-full flex flex-col">
    <!-- Header Principal -->
    <header class="bg-gradient-to-r from-yellow-600 via-yellow-500 to-amber-500 shadow-lg border-b border-yellow-600/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo y Brand -->
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <?= Html::img('@web/images/logo.png', [
                                'alt' => Yii::$app->name,
                                'class' => 'h-10 w-auto',
                            ]) ?>
                        </div>
                    </div>
                    
                    <!-- Selector de fecha/período -->
                    <div class="hidden sm:flex items-center space-x-2 ml-8">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <select class="text-sm text-gray-700 bg-white/60 border border-gray-300/50 rounded-md px-3 py-1 focus:ring-2 focus:ring-yellow-400 focus:border-transparent backdrop-blur-sm shadow-sm">
                            <option class="text-gray-800">Agosto 2025</option>
                            <option class="text-gray-800">Julio 2025</option>
                            <option class="text-gray-800">Junio 2025</option>
                        </select>
                    </div>
                </div>

                <!-- Barra de búsqueda y controles del usuario -->
                <div class="flex items-center space-x-4">
                    <!-- Barra de búsqueda -->
                    <div class="hidden md:block relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               class="block w-64 xl:w-80 pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white/80 backdrop-blur-sm placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-yellow-400 focus:border-transparent text-sm shadow-sm" 
                               placeholder="Buscar...">
                    </div>

                    <!-- Botón de búsqueda móvil -->
                    <button class="md:hidden p-2 text-gray-600 hover:text-gray-800 hover:bg-white/40 rounded-md transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>

                    <!-- Notificaciones -->
                    <div class="relative">
                        <button class="p-2 text-gray-600 hover:text-gray-800 hover:bg-white/40 rounded-md transition-colors relative">
                            <span class="sr-only">Ver notificaciones</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                            </svg>
                            <!-- Badge de notificación -->
                            <span class="absolute -top-0.5 -right-0.5 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                    </div>

                    <!-- Avatar del usuario con dropdown de perfil -->
                    <div class="relative" id="profile-dropdown">
                        <button class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 text-white hover:from-blue-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-yellow-200 transition-all shadow-lg inline-flex items-center"
                                id="profile-button" aria-expanded="false" aria-haspopup="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <svg id="dropdown-arrow-profile" class="w-3 h-3 ml-1.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Menú perfil -->
                        <div id="dropdown-menu-profile" class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg border border-gray-200 rounded-md z-50">
                            <div class="py-2">
                                <a href="<?= Url::to(['/usuario/update', 'id' => Yii::$app->user->id]) ?>"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:text-gray-900 hover:bg-gray-50 no-underline">
                                    Editar perfil
                                </a>
                                <?php
                                echo Html::beginForm(['/site/logout'], 'post', ['class' => 'm-0']);
                                echo Html::submitButton(
                                    'Cerrar sesión',
                                    ['class' => 'w-full text-left block px-4 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 bg-transparent border-0']
                                );
                                echo Html::endForm();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegación principal -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-0 overflow-x-auto scrollbar-hide">
        <!-- Dashboard --> <a href="<?= Url::to(['/site/index']) ?>" 
         class="<?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duración-200">
          <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"> 
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/> <polyline points="9,22 9,12 15,12 15,22"/> </svg> Dashboard </a> 
          <!-- Ventas con dropdown --->
<div class="relative" id="ventas-dropdown">
    <button 
        type="button"
        id="ventas-button"
        aria-expanded="false"
        aria-haspopup="true"
        class="<?= Yii::$app->controller->id === 'ventas' || Yii::$app->controller->id === 'dashboard' 
            ? 'text-blue-600 bg-blue-50/50' 
            : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> 
            whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duration-200 relative z-10"
    >
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
             <circle cx="8" cy="21" r="1"/> 
             <circle cx="19" cy="21" r="1"/>
            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/> 
        </svg> Ventas
        <svg id="dropdown-arrow-ventas" class="w-3 h-3 ml-1.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Menu Ventas --->
    <div id="dropdown-menu-ventas" class="hidden absolute left-0 mt-1 w-48 bg-white shadow-lg border border-gray-200 rounded-md z-50">
        <div class="py-2">
            <a href="<?= Url::to(['/ventas/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'ventas' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Ventas
            </a>
            <a href="<?= Url::to(['/ventas/dashboard']) ?>" 
               class="<?= Yii::$app->controller->id === 'pedidos' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Dashboard
            </a>
        </div>
    </div>
</div>
</a> 
          <!-- Diseño --> 
           <a href="<?= Url::to(['/diseno/index']) ?>" 
           class="<?= Yii::$app->controller->id === 'diseno' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duration-200"> 
           <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"> 
            <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/> 
            <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/>
             <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/> 
             <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/>
              <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/> 
            </svg> Diseño </a>


           <!-- Producción con dropdown --->
<div class="relative" id="produccion-dropdown">
    <button 
        type="button"
        id="produccion-button"
        aria-expanded="false"
        aria-haspopup="true"
        class="<?= Yii::$app->controller->id === 'produccion' || Yii::$app->controller->id === 'impresion' || Yii::$app->controller->id === 'corte' || Yii::$app->controller->id === 'fabricacion'
            ? 'text-blue-600 bg-blue-50/50' 
            : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> 
            whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duration-200 relative z-10"
    >
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path d="M12 15a3 3 0 0 0 3-3 3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3Z"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z"/>
        </svg>
        Producción
        <svg id="dropdown-arrow" class="w-3 h-3 ml-1.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>


    <!-- Dropdown Menu --->
    <div id="dropdown-menu" class="hidden absolute left-0 mt-1 w-48 bg-white shadow-lg border border-gray-200 rounded-md z-50">
        <div class="py-2">
            <a href="<?= Url::to(['/produccion/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'produccion' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                General
            </a>
            <a href="<?= Url::to(['/produccion/impresion']) ?>" 
               class="<?= Yii::$app->controller->id === 'impresion' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transición-colors duración-150 no-underline">
                Impresión
            </a>
            <a href="<?= Url::to(['/produccion/corte']) ?>" 
               class="<?= Yii::$app->controller->id === 'corte' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Corte
            </a>
            <a href="<?= Url::to(['/produccion/fabricacion']) ?>" 
               class="<?= Yii::$app->controller->id === 'fabricacion' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Fabricación
            </a>
        </div>
    </div>
</div> 
<!-- Logística con dropdown --->
<div class="relative" id="logistica-dropdown">
    <button 
        type="button"
        id="logistica-button"
        aria-expanded="false"
        aria-haspopup="true"
        class="<?= Yii::$app->controller->id === 'logistica' || Yii::$app->controller->id === 'atencion-clientes'
            ? 'text-blue-600 bg-blue-50/50' 
            : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> 
            whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duration-200 relative z-10"
    >
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                        <path d="M15 18H9"/>
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                        <circle cx="17" cy="18" r="2"/>
                        <circle cx="7" cy="18" r="2"/>
                    </svg>
                    Logística
        <svg id="dropdown-arrow-logistica" class="w-3 h-3 ml-1.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Menu Logística --->
    <div id="dropdown-menu-logistica" class="hidden absolute left-0 mt-1 w-48 bg-white shadow-lg border border-gray-200 rounded-md z-50">
        <div class="py-2">
            <a href="<?= Url::to(['/logistica/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'logistica' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duración-150 no-underline">
                General
            </a>
            <a href="<?= Url::to(['/logistica/atencion-clientes']) ?>" 
               class="<?= Yii::$app->controller->id === 'envios' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Atencion a clientes
            </a>
        </div>
    </div>
</div>

<?php
// Mostrar Administración solo para usuarios con admin o permisos equivalentes
$adminActive = in_array(Yii::$app->controller->id, ['admin','usuario','role','permiso'], true);
$canAdmin = !Yii::$app->user->isGuest && (
    (isset(Yii::$app->user->identity->role) && (int)Yii::$app->user->identity->role->es_admin === 1)
    || (method_exists(Yii::$app->user->identity, 'can') && (
        Yii::$app->user->identity->can('admin', 'all') ||
        Yii::$app->user->identity->can('admin', 'usuarios') ||
        Yii::$app->user->identity->can('admin', 'roles') ||
        Yii::$app->user->identity->can('admin', 'permisos')
    ))
);
?>
<?php if ($canAdmin): ?>
<!-- Administración con dropdown -->
<div class="relative" id="admin-dropdown">
    <button 
        type="button"
        id="admin-button"
        aria-expanded="false"
        aria-haspopup="true"
        class="<?= $adminActive
            ? 'text-blue-600 bg-blue-50/50' 
            : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' ?> 
            whitespace-nowrap inline-flex items-center px-4 py-4 text-sm font-medium transition-all duration-200 relative z-10"
    >
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path d="M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z"/>
            <path d="M19 21a7 7 0 0 0-14 0"/>
        </svg>
        Administración
        <svg id="dropdown-arrow-admin" class="w-3 h-3 ml-1.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div id="dropdown-menu-admin" class="hidden absolute left-0 mt-1 w-56 bg-white shadow-lg border border-gray-200 rounded-md z-50">
        <div class="py-2">
            <a href="<?= Url::to(['/admin/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'admin' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Panel
            </a>
            <a href="<?= Url::to(['/usuario/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'usuario' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Usuarios
            </a>
            <a href="<?= Url::to(['/role/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'role' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Roles
            </a>
            <a href="<?= Url::to(['/permiso/index']) ?>" 
               class="<?= Yii::$app->controller->id === 'permiso' ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' ?> 
                      block px-4 py-2 text-sm transition-colors duration-150 no-underline">
                Permisos
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

            </div>
        </div>

        
    </nav>

    <!-- Contenido principal -->
    <main class="flex-1 max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Container con padding responsivo mejorado -->
        <div class="space-y-6">
            <?= $content ?>
        </div>
    </main>
</div>

<!-- Agregar estilos personalizados -->
<style>
/* Scrollbar personalizada para navegación horizontal en móviles */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Animación suave para transiciones */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Backdrop blur soporte adicional */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Eliminar cualquier borde inferior de la navegación */
nav a {
    border-bottom: none !important;
    border: none !important;
}

/* Estilos para el dropdown */
.arrow-rotated {
    transform: rotate(180deg);
}

/* Asegurar que el dropdown se muestre */
#dropdown-menu {
    display: none;
}

#dropdown-menu:not(.hidden) {
    display: block;
}

.arrow-rotated {
    transform: rotate(180deg);
}

/* Quitar apariencia de hipervínculo en todos los enlaces del menú */
nav a,
#dropdown-menu a {
    text-decoration: none !important;
    color: inherit !important;
}

/* Estado activo */
#dropdown-menu a.bg-blue-50 {
    font-weight: 600;
    background-color: #ebf4ff; /* Azul claro */
}

/* Hover */
#dropdown-menu a:hover {
    background-color: #f3f4f6; /* Gris suave */
    color: #111827; /* Gris oscuro */
}

/* Mejorar la experiencia del dropdown en móviles */
@media (max-width: 768px) {
    #dropdown-menu {
        position: fixed !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        width: 90vw !important;
        max-width: 300px !important;
    }
}

/* Solución 1: Estilos CSS mejorados para el dropdown */
#produccion-dropdown { position: relative; z-index: 1000; }

/* Menú dropdown base */
#dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1001;
    background: white;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05);
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    min-width: 12rem;
    width: max-content;
    overflow: visible;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all .2s cubic-bezier(.4,0,.2,1);
}

/* Mostrar dropdown */
#dropdown-menu:not(.hidden) {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Enlaces del dropdown */
#dropdown-menu a {
    display: block;
    padding: .5rem 1rem;
    color: #374151;
    text-decoration: none !important;
    font-size: .875rem;
    transition: all .15s ease-in-out;
    border-radius: .25rem;
    margin: .125rem .25rem;
}

#dropdown-menu a:hover { background-color: #f3f4f6; color: #111827; }
#dropdown-menu a.bg-blue-50 { background-color: #eff6ff; color: #2563eb; font-weight: 600; }
nav .flex { overflow: visible !important; }

@media (max-width: 768px) {
    #dropdown-menu {
        position: fixed !important;
        top: auto !important;
        left: 50% !important;
        transform: translateX(-50%) translateY(-10px) !important;
        width: 90vw !important;
        max-width: 300px !important;
        margin-top: .5rem;
    }
    #dropdown-menu:not(.hidden) { transform: translateX(-50%) translateY(0) !important; }
}

/* Asegurar que sticky nav no interfiera */
.sticky { overflow: visible !important; }

/* Clase helper para debugging */
/* .dropdown-debug { border: 2px solid red !important; background: yellow !important; } */
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function setupDropdown(buttonId, menuId, arrowId) {
        const button = document.getElementById(buttonId);
        const menu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);

        if (!button || !menu || !arrow) return;

        // Abrir / cerrar dropdown
        button.addEventListener("click", function (e) {
            e.stopPropagation();

            const isHidden = menu.classList.contains("hidden");

            // Cerrar todos los menús antes de abrir este
            document.querySelectorAll("[id^='dropdown-menu']").forEach(el => el.classList.add("hidden"));
            document.querySelectorAll("[id^='dropdown-arrow']").forEach(el => el.classList.remove("rotate-180"));

            if (isHidden) {
                menu.classList.remove("hidden");
                arrow.classList.add("rotate-180");
            } else {
                menu.classList.add("hidden");
                arrow.classList.remove("rotate-180");
            }
        });

        // Cerrar si clicamos fuera
        document.addEventListener("click", function (e) {
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add("hidden");
                arrow.classList.remove("rotate-180");
            }
        });
    }

    // Activar para Producción, Logística y Ventas
    setupDropdown("produccion-button", "dropdown-menu", "dropdown-arrow");
    setupDropdown("logistica-button", "dropdown-menu-logistica", "dropdown-arrow-logistica");
    setupDropdown("ventas-button", "dropdown-menu-ventas", "dropdown-arrow-ventas");
    // Activar para Administración (reinsertado)
    setupDropdown("admin-button", "dropdown-menu-admin", "dropdown-arrow-admin");
    // Nuevo: Perfil
    setupDropdown("profile-button", "dropdown-menu-profile", "dropdown-arrow-profile");
});
</script>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>