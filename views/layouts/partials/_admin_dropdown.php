<?php
use yii\helpers\Url;

if (Yii::\$app->user->isGuest) {
    return;
}

$identity = Yii::\$app->user->identity;
$isAdminUser = ($identity->hasPermission('admin', 'all') ?? false) || (($identity->role->es_admin ?? 0) == 1);
$isAdminSectionActive = in_array(Yii::\$app->controller->id, ['admin', 'usuario', 'role', 'roles'], true);

if (!$isAdminUser) {
    return;
}
?>
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
               class="block px-4 py-2 text-sm <?= Yii::\$app->controller->id === 'admin' ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100' ?>">
                Usuarios
            </a>
            <a href="<?= Url::to(['/role/index']) ?>"
               class="block px-4 py-2 text-sm <?= in_array(Yii::\$app->controller->id, ['roles', 'role'], true) ? 'bg-gray-100 text-gray-900' : 'text-gray-700 hover:bg-gray-100' ?>">
                Roles
            </a>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
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
    document.addEventListener('click', function () { closeMenu(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
})();
JS
);
?>