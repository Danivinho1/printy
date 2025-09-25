<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Metas;
use app\models\Ventas;
use app\models\ContactForm;
use app\models\Notificacion;
use app\models\Pedidos; // Ajusta si tu modelo de pedidos tiene otro nombre

class SiteController extends Controller
{
    // ... (mismos behaviors y actions)

    public function actionIndex()
    {
        $usuarioId = Yii::$app->user->id;

        // --- NOTIFICACIONES AUTOMÁTICAS ---

        // Pedidos atrasados (fecha_entrega < hoy && estatus = pendiente)
        $atrasados = Pedidos::find()
            ->where(['usuario_id' => $usuarioId])
            ->andWhere(['<', 'fecha_entrega', date('Y-m-d')])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();
        if ($atrasados) {
            $msg = 'Tienes ' . count($atrasados) . ' pedidos atrasados';
            Notificacion::crearSiNoExiste($usuarioId, $msg, 'danger');
        }

        // Pedidos pendientes de pago
        $pendientesPago = Pedidos::find()
            ->where(['usuario_id' => $usuarioId, 'estatus' => 'pendiente_pago'])
            ->all();
        if ($pendientesPago) {
            $msg = 'Tienes ' . count($pendientesPago) . ' pedidos pendientes de pago';
            Notificacion::crearSiNoExiste($usuarioId, $msg, 'warning');
        }

        // Pedidos urgentes (entrega en los próximos 2 días)
        $urgentes = Pedidos::find()
            ->where(['usuario_id' => $usuarioId])
            ->andWhere(['between', 'fecha_entrega', date('Y-m-d'), date('Y-m-d', strtotime('+2 days'))])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();
        if ($urgentes) {
            $msg = 'Tienes ' . count($urgentes) . ' pedidos urgentes por entregar';
            Notificacion::crearSiNoExiste($usuarioId, $msg, 'info');
        }

        // Pedidos caducados (fecha_caducidad < hoy)
        $caducados = Pedidos::find()
            ->where(['usuario_id' => $usuarioId])
            ->andWhere(['<', 'fecha_caducidad', date('Y-m-d')])
            ->all();
        if ($caducados) {
            $msg = 'Tienes ' . count($caducados) . ' pedidos caducados';
            Notificacion::crearSiNoExiste($usuarioId, $msg, 'danger');
        }

        // --- TU LÓGICA ORIGINAL DEL DASHBOARD ---

        $mesActual = date('Y-m');
        $ventasDelMes = Ventas::find()
            ->where(['like', 'fecha_compra', $mesActual])
            ->with(['tipoLetrero'])
            ->all();

        $totalUnidades = 0;
        $totalDinero   = 0;
        $totalAnticipo = 0;
        $totalRestante = 0;

        // Agrupación productos vendidos
        $conteoProductos = [];
        foreach ($ventasDelMes as $venta) {
            $totalUnidades += (int)$venta->unidades;
            $totalDinero   += (float)$venta->precio_total;
            $totalAnticipo += (float)$venta->anticipo;
            $totalRestante += (float)$venta->restante;

            if ($venta->tipoLetrero) {
                $tipoId = $venta->tipo_letrero_id;
                $nombreTipo = $venta->tipoLetrero->nombre;

                if (!isset($conteoProductos[$tipoId])) {
                    $conteoProductos[$tipoId] = [
                        'id' => $tipoId,
                        'nombre' => $nombreTipo,
                        'cantidad' => 0,
                        'total_unidades' => 0,
                        'total_ventas' => 0
                    ];
                }

                $conteoProductos[$tipoId]['cantidad']++;
                $conteoProductos[$tipoId]['total_unidades'] += (int)$venta->unidades;
                $conteoProductos[$tipoId]['total_ventas']   += (float)$venta->precio_total;
            }
        }

        uasort($conteoProductos, fn($a, $b) => $b['cantidad'] - $a['cantidad']);
        $productosVendidos = array_slice($conteoProductos, 0, 5, true);

        // Metas
        Metas::crearMetasPorDefecto($mesActual);
        $metaUnidades = Metas::getMetaUnidades($mesActual);
        $metaDinero   = Metas::getMetaDinero($mesActual);

        $porcentajeUnidades = $metaUnidades > 0 ? round(($totalUnidades / $metaUnidades) * 100) : 0;
        $porcentajeDinero   = $metaDinero > 0 ? round(($totalDinero / $metaDinero) * 100) : 0;

        return $this->render('index', [
            'totalUnidades' => $totalUnidades,
            'porcentajeUnidades' => $porcentajeUnidades,
            'totalDinero' => $totalDinero,
            'porcentajeDinero' => $porcentajeDinero,
            'totalAnticipo' => $totalAnticipo,
            'totalRestante' => $totalRestante,
            'metaUnidades' => $metaUnidades,
            'metaDinero' => $metaDinero,
            'productosVendidos' => $productosVendidos,
        ]);
    }

    // ...resto del controlador igual...
}