<?php
/** @var \yii\web\View $this */
/** @var array|\app\models\Notificacion[] $notificaciones */
/** @var int $conteoNuevas */

use yii\helpers\Html;
?>
<div class="notificaciones-widget">
    <button id="notifs-button" type="button" class="p-2">
        <?= Html::encode("Notificaciones ({$conteoNuevas})") ?>
    </button>

    <div id="notifs-list" style="display:none; position:absolute; right:0; z-index:1000; background:#fff; border:1px solid #ddd; padding:10px; width:320px;">
        <?php if (empty($notificaciones)): ?>
            <div class="notif-empty"><?= Html::encode('No hay notificaciones') ?></div>
        <?php else: ?>
            <ul style="list-style:none; margin:0; padding:0;">
                <?php foreach ($notificaciones as $n): ?>
                    <li style="padding:6px 8px; border-bottom:1px solid #eee;">
                        <div style="font-size:13px; color:#333;"><?= Html::encode($n->titulo ?? '') ?></div>
                        <div style="font-size:12px; color:#666;"><?= Html::encode($n->mensaje ?? '') ?></div>
                        <div style="font-size:11px; color:#999; margin-top:4px;"><?= Html::encode((string)($n->created_at ?? '')) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php
// Pequeño JS para mostrar/ocultar la lista (solo en dev; puedes mover a archivo .js)
$js = <<<'JS'
document.addEventListener('click', function(e){
    var btn = document.getElementById('notifs-button');
    var list = document.getElementById('notifs-list');
    if (!btn || !list) return;
    if (btn.contains(e.target)) {
        list.style.display = (list.style.display === 'none' || list.style.display === '') ? 'block' : 'none';
        return;
    }
    if (!list.contains(e.target)) {
        list.style.display = 'none';
    }
});
JS;
$this->registerJs($js);
?>