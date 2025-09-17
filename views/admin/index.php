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
<div class="space-y-6">

  <!-- Header -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        <p class="mt-1 text-sm text-gray-500">Gestiona usuarios, roles y permisos del sistema.</p>
      </div>
      <div class="flex gap-3">
        <a href="<?= Url::to(['/usuario/create']) ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow">
          <!-- plus icon -->
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          Nuevo usuario
        </a>
        <a href="<?= Url::to(['/usuario/index']) ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200">
          <!-- users icon -->
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Ver usuarios
        </a>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Usuarios -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Usuarios</p>
          <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int)$totalUsuarios ?></p>
        </div>
        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
      </div>
      <div class="mt-4 flex gap-2">
        <a href="<?= Url::to(['/usuario/index']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Entrar</a>
        <a href="<?= Url::to(['/usuario/create']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">Crear</a>
      </div>
    </div>

    <!-- Roles -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Roles</p>
          <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int)$totalRoles ?></p>
        </div>
        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z"/><path d="M19 21a7 7 0 0 0-14 0"/></svg>
        </div>
      </div>
      <div class="mt-4 flex gap-2">
        <a href="<?= Url::to(['/role/index']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Entrar</a>
        <a href="<?= Url::to(['/role/create']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-amber-600 hover:bg-amber-700 text-white">Crear</a>
      </div>
    </div>

    <!-- Permisos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">Permisos</p>
          <p class="mt-1 text-3xl font-bold text-gray-900"><?= (int)$totalPerms ?></p>
        </div>
        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1l3 5 5 .7-3.6 3.6.9 5-5.3-2.8L7.6 15l.9-5L5 6.7 10 6z"/></svg>
        </div>
      </div>
      <div class="mt-4 flex gap-2">
        <a href="<?= Url::to(['/permiso/index']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Entrar</a>
        <a href="<?= Url::to(['/permiso/create']) ?>" class="px-3 py-1.5 text-sm rounded-md bg-emerald-600 hover:bg-emerald-700 text-white">Crear</a>
      </div>
    </div>
  </div>

  <!-- Últimos usuarios -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-900">Últimos usuarios modificados</h2>
      <a href="<?= Url::to(['/usuario/index']) ?>" class="text-sm text-blue-600 hover:text-blue-700">Ver todos</a>
    </div>
    <div class="p-6 overflow-x-auto">
      <table class="min-w-full text-left">
        <thead class="text-xs uppercase text-gray-500">
          <tr>
            <th class="py-2 pr-4">ID</th>
            <th class="py-2 pr-4">Usuario</th>
            <th class="py-2 pr-4">Nombre</th>
            <th class="py-2 pr-4">Rol</th>
            <th class="py-2 pr-4">Activo</th>
            <th class="py-2 pr-4">Actualizado</th>
            <th class="py-2 pr-4">Acciones</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
          <?php if (empty($ultimosUsuarios)): ?>
            <tr><td colspan="7" class="py-4 text-gray-400">Sin usuarios recientes.</td></tr>
          <?php else: ?>
            <?php foreach ($ultimosUsuarios as $u): ?>
              <tr class="border-t border-gray-100">
                <td class="py-2 pr-4"><?= (int)$u->id ?></td>
                <td class="py-2 pr-4"><?= Html::encode($u->username) ?></td>
                <td class="py-2 pr-4"><?= Html::encode($u->nombre_completo ?? '-') ?></td>
                <td class="py-2 pr-4"><?= $u->role ? Html::encode($u->role->nombre) : '-' ?></td>
                <td class="py-2 pr-4">
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs <?= $u->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                    <?= $u->activo ? 'Sí' : 'No' ?>
                  </span>
                </td>
                <td class="py-2 pr-4"><?= Html::encode($u->updated_at) ?></td>
                <td class="py-2 pr-4">
                  <div class="flex gap-2">
                    <a href="<?= Url::to(['/usuario/view', 'id' => $u->id]) ?>" class="text-blue-600 hover:text-blue-800">Ver</a>
                    <a href="<?= Url::to(['/usuario/update', 'id' => $u->id]) ?>" class="text-amber-600 hover:text-amber-800">Editar</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Accesos rápidos -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="<?= Url::to(['/usuario/create']) ?>" class="group bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-5 text-white shadow hover:shadow-md transition">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold">Agregar usuario</h3>
          <p class="mt-1 text-white/90 text-sm">Crea una nueva cuenta y asigna rol.</p>
        </div>
        <div class="h-10 w-10 rounded-lg bg-white/20 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        </div>
      </div>
    </a>

    <a href="<?= Url::to(['/role/index']) ?>" class="group bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow transition">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Gestionar roles</h3>
          <p class="mt-1 text-gray-500 text-sm">Define permisos por rol.</p>
        </div>
        <div class="h-10 w-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z"/><path d="M19 21a7 7 0 0 0-14 0"/></svg>
        </div>
      </div>
    </a>

    <a href="<?= Url::to(['/permiso/index']) ?>" class="group bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow transition">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Gestionar permisos</h3>
          <p class="mt-1 text-gray-500 text-sm">Alta/edición de permisos granulares.</p>
        </div>
        <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 1l3 5 5 .7-3.6 3.6.9 5-5.3-2.8L7.6 15l.9-5L5 6.7 10 6z"/></svg>
        </div>
      </div>
    </a>
  </div>

</div>