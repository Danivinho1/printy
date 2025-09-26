<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<li class="nav-item dropdown">
    <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
        <i class="fa fa-bell"></i>
        <?php if ($noLeidas > 0): ?>
            <span class="badge bg-danger"><?= $noLeidas ?></span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px;max-width:400px;">
        <li><strong>Notificaciones</strong></li>
        <?php if (empty($notificaciones)): ?>
            <li class="dropdown-item small text-muted">Sin notificaciones</li>
        <?php else: ?>
            <?php foreach ($notificaciones as $n): ?>
                <li class="dropdown-item<?= $n->leida ? '' : ' bg-light' ?>">
                    <div>
                        <span class="badge bg-<?= Html::encode($n->tipo ?? 'info') ?> me-1">&nbsp;</span>
                        <?= Html::encode($n->mensaje) ?>
                    </div>
                    <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($n->created_at)) ?></div>
                </li>
            <?php endforeach; ?>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a href="<?= Url::to(['/notificacion/index']) ?>" class="dropdown-item text-center">Ver todas</a>
            </li>
        <?php endif; ?>
    </ul>
</li>