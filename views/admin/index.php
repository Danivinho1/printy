<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $totalUsuarios int */
/* @var $totalRoles int */
/* @var $totalPerms int */
/* @var $ultimosUsuarios app\models\Usuario[] */

$this->title = 'Panel de Administración';
?>

<!-- Custom styles for enhanced animations and gradients -->
<style>
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes pulse-ring {
  0% { transform: scale(.33); }
  80%, 100% { opacity: 0; transform: scale(1.33); }
}
@keyframes pulse-dot {
  0% { transform: scale(.8); }
  50% { transform: scale(1); }
  100% { transform: scale(.8); }
}
.animate-fadeInUp { animation: fadeInUp 0.6s ease-out forwards; }
.animate-delay-100 { animation-delay: 0.1s; }
.animate-delay-200 { animation-delay: 0.2s; }
.animate-delay-300 { animation-delay: 0.3s; }
.pulse-ring { animation: pulse-ring 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite; }
.pulse-dot { animation: pulse-dot 2s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite; }

/* Eliminar estilos de hipervínculo de todos los botones */
.btn-action {
  text-decoration: none !important;
  color: inherit !important;
}
.btn-action:hover {
  text-decoration: none !important;
}
.btn-action:focus {
  text-decoration: none !important;
  outline: none;
}
.btn-action:visited {
  text-decoration: none !important;
  color: inherit !important;
}
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Section with enhanced design -->
    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 p-8 animate-fadeInUp">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="space-y-2">
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <div class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-green-400 pulse-dot"></div>
            </div>
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                <?= Html::encode($this->title) ?>
              </h1>
              <p class="text-gray-600 font-medium">Sistema de gestión integral</p>
            </div>
          </div>
          <p class="text-sm text-gray-500 max-w-2xl">
            Administra usuarios, roles y permisos de forma eficiente. Monitor del estado del sistema en tiempo real.
          </p>
        </div>
        
        <div class="flex flex-wrap gap-3">
          <a href="<?= Url::to(['/usuario/create']) ?>" 
             class="btn-action group inline-flex items-center px-6 py-3 text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="text-white">Nuevo usuario</span>
          </a>
          <a href="<?= Url::to(['/usuario/index']) ?>" 
             class="btn-action inline-flex items-center px-6 py-3 text-sm font-semibold rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 hover:border-blue-300 shadow-sm hover:shadow-md transition-all duration-200">
            <svg class="w-4 h-4 mr-2 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="text-blue-700">Ver todos los usuarios</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Enhanced KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Usuarios Card -->
      <div class="group bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 animate-fadeInUp animate-delay-100">
        <div class="flex items-center justify-between mb-4">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Usuarios Totales</p>
            <div class="flex items-baseline space-x-2">
              <p class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                <?= number_format((int)$totalUsuarios) ?>
              </p>
              <span class="text-sm text-green-600 font-medium flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                Activos
              </span>
            </div>
          </div>
          <div class="relative">
            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
              </svg>
            </div>
            <div class="absolute -inset-2 rounded-2xl bg-blue-500/20 pulse-ring"></div>
          </div>
        </div>
        <div class="flex gap-2">
          <a href="<?= Url::to(['/usuario/index']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-center transition-colors duration-200">
            <span class="text-gray-700">Gestionar</span>
          </a>
          <a href="<?= Url::to(['/usuario/create']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-center transition-colors duration-200">
            <span class="text-white">Crear Nuevo</span>
          </a>
        </div>
      </div>

      <!-- Roles Card -->
      <div class="group bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 animate-fadeInUp animate-delay-200">
        <div class="flex items-center justify-between mb-4">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Roles Definidos</p>
            <div class="flex items-baseline space-x-2">
              <p class="text-4xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                <?= number_format((int)$totalRoles) ?>
              </p>
              <span class="text-sm text-amber-600 font-medium">Configurados</span>
            </div>
          </div>
          <div class="relative">
            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z"/>
                <path d="M19 21a7 7 0 0 0-14 0"/>
              </svg>
            </div>
            <div class="absolute -inset-2 rounded-2xl bg-amber-500/20 pulse-ring"></div>
          </div>
        </div>
        <div class="flex gap-2">
          <a href="<?= Url::to(['/role/index']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-center transition-colors duration-200">
            <span class="text-gray-700">Ver Roles</span>
          </a>
          <a href="<?= Url::to(['/role/create']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-amber-600 hover:bg-amber-700 text-center transition-colors duration-200">
            <span class="text-white">Crear Rol</span>
          </a>
        </div>
      </div>

      <!-- Permisos Card -->
      <div class="group bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6 hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 animate-fadeInUp animate-delay-300">
        <div class="flex items-center justify-between mb-4">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Permisos Totales</p>
            <div class="flex items-baseline space-x-2">
              <p class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                <?= number_format((int)$totalPerms) ?>
              </p>
              <span class="text-sm text-emerald-600 font-medium">Granulares</span>
            </div>
          </div>
          <div class="relative">
            <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="absolute -inset-2 rounded-2xl bg-emerald-500/20 pulse-ring"></div>
          </div>
        </div>
        <div class="flex gap-2">
          <a href="<?= Url::to(['/permiso/index']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 text-center transition-colors duration-200">
            <span class="text-gray-700">Ver Lista</span>
          </a>
          <a href="<?= Url::to(['/permiso/create']) ?>" 
             class="btn-action flex-1 px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-center transition-colors duration-200">
            <span class="text-white">Crear Permiso</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Enhanced Recent Users Table -->
    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 overflow-hidden animate-fadeInUp animate-delay-300">
      <div class="px-8 py-6 border-b border-gray-100/50 bg-gradient-to-r from-gray-50/50 to-white/50">
        <div class="flex items-center justify-between">
          <div class="space-y-1">
            <h2 class="text-xl font-bold text-gray-900">Actividad Reciente</h2>
            <p class="text-sm text-gray-500">Últimos usuarios modificados en el sistema</p>
          </div>
          <a href="<?= Url::to(['/usuario/index']) ?>" 
             class="btn-action inline-flex items-center px-4 py-2 text-sm font-medium bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-all duration-200">
            <span class="text-blue-600 hover:text-blue-700">Ver historial completo</span>
            <svg class="w-4 h-4 ml-2 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50/50">
            <tr>
              <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Información</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actualizado</th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($ultimosUsuarios)): ?>
              <tr>
                <td colspan="6" class="px-8 py-12 text-center">
                  <div class="flex flex-col items-center space-y-3">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No hay usuarios recientes</p>
                    <p class="text-sm text-gray-400">Los últimos usuarios modificados aparecerán aquí</p>
                  </div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($ultimosUsuarios as $index => $u): ?>
                <tr class="hover:bg-gray-50/50 transition-colors duration-200 group">
                  <td class="px-8 py-4">
                    <div class="flex items-center space-x-3">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                        <?= strtoupper(substr($u->username, 0, 1)) ?>
                      </div>
                      <div>
                        <p class="text-sm font-semibold text-gray-900"><?= Html::encode($u->username) ?></p>
                        <p class="text-xs text-gray-500">ID: <?= (int)$u->id ?></p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <p class="text-sm text-gray-900"><?= Html::encode($u->nombre_completo ?? 'Sin nombre') ?></p>
                    <p class="text-xs text-gray-500">Perfil completo</p>
                  </td>
                  <td class="px-6 py-4">
                    <?php if ($u->role): ?>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <?= Html::encode($u->role->nombre) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-sm text-gray-400">Sin rol</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $u->activo ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                      <div class="w-2 h-2 rounded-full mr-2 <?= $u->activo ? 'bg-emerald-400' : 'bg-red-400' ?>"></div>
                      <?= $u->activo ? 'Activo' : 'Inactivo' ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <p class="text-sm text-gray-900"><?= date('d/m/Y', strtotime($u->updated_at)) ?></p>
                    <p class="text-xs text-gray-500"><?= date('H:i', strtotime($u->updated_at)) ?></p>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center space-x-2 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                      <a href="<?= Url::to(['/usuario/view', 'id' => $u->id]) ?>" 
                         class="btn-action inline-flex items-center px-3 py-1.5 text-xs font-medium bg-blue-50 hover:bg-blue-100 rounded-md border border-blue-200 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span class="text-blue-600 hover:text-blue-800">Ver</span>
                      </a>
                      <a href="<?= Url::to(['/usuario/update', 'id' => $u->id]) ?>" 
                         class="btn-action inline-flex items-center px-3 py-1.5 text-xs font-medium bg-amber-50 hover:bg-amber-100 rounded-md border border-amber-200 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="text-amber-600 hover:text-amber-800">Editar</span>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Enhanced Quick Actions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <a href="<?= Url::to(['/usuario/create']) ?>" 
         class="btn-action group relative bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl p-8 shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
        <div class="relative z-10">
          <div class="flex items-start justify-between mb-6">
            <div class="space-y-2">
              <h3 class="text-xl font-bold text-white">Agregar Usuario</h3>
              <p class="text-white/90 text-sm leading-relaxed">Crea una nueva cuenta de usuario y asigna roles específicos para el acceso al sistema.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14"/>
              </svg>
            </div>
          </div>
          <div class="flex items-center text-sm font-medium text-white">
            Crear ahora
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
        <div class="absolute -top-4 -right-4 h-24 w-24 rounded-full bg-white/10 group-hover:scale-150 transition-transform duration-500"></div>
      </a>

      <a href="<?= Url::to(['/role/index']) ?>" 
         class="btn-action group relative bg-white rounded-2xl p-8 border border-gray-200 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-orange-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="flex items-start justify-between mb-6">
            <div class="space-y-2">
              <h3 class="text-xl font-bold text-gray-900">Gestionar Roles</h3>
              <p class="text-gray-500 text-sm leading-relaxed">Define y configura roles con permisos específicos para diferentes niveles de acceso.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z"/>
                <path d="M19 21a7 7 0 0 0-14 0"/>
              </svg>
            </div>
          </div>
          <div class="flex items-center text-sm font-medium text-gray-600">
            <span>Administrar roles</span>
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </a>

      <a href="<?= Url::to(['/permiso/index']) ?>" 
         class="btn-action group relative bg-white rounded-2xl p-8 border border-gray-200 shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 overflow-hidden md:col-span-2 xl:col-span-1">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-teal-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="flex items-start justify-between mb-6">
            <div class="space-y-2">
              <h3 class="text-xl font-bold text-gray-900">Gestionar Permisos</h3>
              <p class="text-gray-500 text-sm leading-relaxed">Configura permisos granulares para un control detallado del acceso a funcionalidades.</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
          </div>
          <div class="flex items-center text-sm font-medium text-gray-600">
            <span>Configurar permisos</span>
            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </a>
    </div>

    <!-- Additional Statistics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- System Health Card -->
      <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Estado del Sistema</h3>
          <div class="flex items-center space-x-2">
            <div class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></div>
            <span class="text-sm text-green-600 font-medium">Operativo</span>
          </div>
        </div>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
            <div class="flex items-center space-x-3">
              <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Sesiones Activas</span>
            </div>
            <span class="text-sm font-bold text-gray-900">24</span>
          </div>
          
          <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
            <div class="flex items-center space-x-3">
              <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Tiempo de Actividad</span>
            </div>
            <span class="text-sm font-bold text-gray-900">99.9%</span>
          </div>
          
          <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
            <div class="flex items-center space-x-3">
              <div class="h-8 w-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Última Copia de Seguridad</span>
            </div>
            <span class="text-sm font-bold text-gray-900">Hace 2h</span>
          </div>
        </div>
      </div>

      <!-- Quick Statistics -->
      <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-white/20 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas Rápidas</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Usuarios activos hoy</span>
            <div class="flex items-center space-x-2">
              <div class="h-2 w-16 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: 75%"></div>
              </div>
              <span class="text-sm font-semibold text-gray-900">75%</span>
            </div>
          </div>
          
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Roles más utilizados</span>
            <div class="flex items-center space-x-2">
              <div class="h-2 w-16 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full" style="width: 60%"></div>
              </div>
              <span class="text-sm font-semibold text-gray-900">60%</span>
            </div>
          </div>
          
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Permisos asignados</span>
            <div class="flex items-center space-x-2">
              <div class="h-2 w-16 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full" style="width: 85%"></div>
              </div>
              <span class="text-sm font-semibold text-gray-900">85%</span>
            </div>
          </div>
          
          <div class="mt-6 p-4 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p class="text-sm font-medium text-indigo-900">Tip del día</p>
                <p class="text-xs text-indigo-700">Revisa regularmente los permisos de usuario para mantener la seguridad del sistema.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    

  </div>
</div>