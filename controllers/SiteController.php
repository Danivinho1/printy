<?php

namespace app\controllers;


use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Ventas;
use app\models\Metas;

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
                'only' => ['logout', 'index'], // Solo aplica a logout e index
                'rules' => [
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'], // '@' significa usuarios autenticados
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
        // Si el usuario no está logueado, lo redirige al login
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['site/login'])->send();
            return '';
        }

        $mesActual = date('Y-m');
        $ventasDelMes = Ventas::find()
            ->where(['like', 'fecha_compra', $mesActual])
            ->with(['tipoLetrero'])
            ->all();

        $totalUnidades = count($ventasDelMes);
        $totalDinero = 0;
        $totalAnticipo = 0;
        foreach ($ventasDelMes as $venta) {
            $totalDinero += $venta->precio_total;
            $totalAnticipo += $venta->anticipo;
        }
        $totalRestante = $totalDinero - $totalAnticipo;

        // Obtener metas
        $metaUnidades = Metas::getMetaUnidades($mesActual);
        $metaDinero = Metas::getMetaDinero($mesActual);

        $porcentajeUnidades = $metaUnidades > 0 ? round(($totalUnidades / $metaUnidades) * 100) : 0;
        $porcentajeDinero = $metaDinero > 0 ? round(($totalDinero / $metaDinero) * 100) : 0;

        // --- NUEVA LÓGICA AÑADIDA ---
        // Obtener Top 5 productos vendidos del mes
        $productosVendidos = (new Query())
            ->select([
                'c.nombre',
                'total_unidades' => 'COUNT(v.id)'
            ])
            ->from(['v' => 'ventas'])
            ->innerJoin(['c' => 'catalogos'], 'v.tipo_letrero_id = c.id')
            ->where(['like', 'v.fecha_compra', $mesActual])
            ->groupBy('c.nombre')
            ->orderBy(['total_unidades' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'totalUnidades' => $totalUnidades,
            'porcentajeUnidades' => $porcentajeUnidades,
            'totalDinero' => $totalDinero,
            'porcentajeDinero' => $porcentajeDinero,
            'totalAnticipo' => $totalAnticipo,
            'totalRestante' => $totalRestante,
            'metaUnidades' => $metaUnidades,
            'metaDinero' => $metaDinero,
            'productosVendidos' => $productosVendidos, // <-- VARIABLE AÑADIDA
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
}

