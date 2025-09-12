<?php
$this->title = 'Panel de Administración';
use yii\helpers\Url;
?>
<h1>Panel de Administración</h1>

<p>
    <a class="btn btn-primary" href="<?= Url::to(['admin/users']) ?>">Usuarios</a>
    <a class="btn btn-secondary" href="<?= Url::to(['roles/index']) ?>">Roles</a>
    <a class="btn btn-info" href="<?= Url::to(['permisos/index']) ?>">Permisos</a>
</p>