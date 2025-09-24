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

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // KPIs del mes actual
        $mesActual = date('Y-m');
        $ventasDelMes = Ventas::find()
            ->where(['like', 'fecha_compra', $mesActual])
            ->with(['tipoLetrero']) // Para top productos, igual que en ventas
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
            'productosVendidos' => $productosVendidos, // <-- Ahora la vista puede usarla
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionVentas()
    {
        $this->layout = 'main'; 

        // Mes actual
        $mesActual = date('Y-m');
        $ventasDelMes = Ventas::find()
            ->where(['like', 'fecha_compra', $mesActual])
            ->with(['tipoLetrero', 'asesor', 'medio'])
            ->all();

        // Totales
        $totalUnidades = 0;
        $totalDinero   = 0;
        $totalAnticipo = 0;
        $totalRestante = 0;

        foreach ($ventasDelMes as $venta) {
            $totalUnidades += (int)$venta->unidades;
            $totalDinero   += (float)$venta->precio_total;
            $totalAnticipo += (float)$venta->anticipo;
            $totalRestante += (float)$venta->restante;
        }

        // Metas
        Metas::crearMetasPorDefecto($mesActual);
        $metaUnidades = Metas::getMetaUnidades($mesActual);
        $metaDinero   = Metas::getMetaDinero($mesActual);

        $porcentajeUnidades = $metaUnidades > 0 ? round(($totalUnidades / $metaUnidades) * 100) : 0;
        $porcentajeDinero   = $metaDinero > 0 ? round(($totalDinero / $metaDinero) * 100) : 0;

        // Agrupación productos
        $conteoProductos = [];
        foreach ($ventasDelMes as $venta) {
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

        return $this->render('ventas', [
            'ventasDelMes' => $ventasDelMes,
            'totalUnidades' => $totalUnidades,
            'totalDinero' => $totalDinero,
            'totalAnticipo' => $totalAnticipo,
            'totalRestante' => $totalRestante,
            'metaUnidades' => $metaUnidades,
            'metaDinero' => $metaDinero,
            'porcentajeUnidades' => $porcentajeUnidades,
            'porcentajeDinero' => $porcentajeDinero,
            'productosVendidos' => $productosVendidos,
            'mesActual' => $mesActual
        ]);
    }
}