<?php


use yii\bootstrap5\Tabs;
use yii\grid\GridView;

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Campañas',
            'content' => GridView::widget([
                'dataProvider' => $campanasProvider,
                'columns' => [
                    'id',
                    'nombre',
                    'asesor.nombre',
                    'inversion',
                    'mensajes',
                    'retorno',
                    'analisis',
                    'mensaje_predeterminado:ntext',
                    'presupuesto',
                ],
            ]),
            'active' => true
        ],
        [
            'label' => 'Retorno',
            'content' => GridView::widget([
                'dataProvider' => $retornoProvider,
                'columns' => [
                    'id',
                    'campana.nombre',
                    'asesor.nombre',
                    'cantidad',
                ],
            ]),
        ],
        [
            'label' => 'Comparativa Mensual',
            'content' => GridView::widget([
                'dataProvider' => $ventasProvider,
                'columns' => [
                    'fecha',
                    'total',
                    'nivel',
                ],
            ]),
        ],
        [
            'label' => 'Productos Vendidos',
            'content' => GridView::widget([
                'dataProvider' => $productosProvider,
                'columns' => [
                    'id',
                    'tipoLetrero.nombre',
                    'unidades',
                    'subtotal',
                    'total',
                ],
            ]),
        ],
    ],
]);
