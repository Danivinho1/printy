<?php

namespace app\controllers;

use app\models\Catalogos;
use Yii;
use app\models\Campanas;
use app\models\CampanasSearch;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Controlador para el módulo de Campañas (Marketing)
 */
class CampanasController extends Controller
{
    /** ===============================
     *  CONFIGURACIÓN DE BEHAVIORS
     * =============================== */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        // Endpoints AJAX
                        'update-field' => ['POST'],
                        'update-inline' => ['POST'],
                        'get-select-options' => ['GET'],
                    ],
                ],
            ]
        );
    }

    /** ===============================
     *  LISTAR CAMPAÑAS
     * =============================== */
    public function actionIndex()
    {
        $searchModel = new CampanasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** ===============================
     *  VISTA DETALLADA DE UNA CAMPAÑA
     * =============================== */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /** ===============================
     *  CREAR NUEVA CAMPAÑA
     * =============================== */
    public function actionCreate()
    {
        $model = new Campanas();

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                // Evitar valores incómodos desde el form (created_at/updated_at se manejan por Behavior si lo configuras)
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /** ===============================
     *  ACTUALIZAR UNA CAMPAÑA
     * =============================== */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /** ===============================
     *  ELIMINAR UNA CAMPAÑA
     * =============================== */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /** ===============================
     *  OPCIONES PARA SELECTS (AJAX)
     * =============================== */
    public function actionGetSelectOptions($field)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Asesores desde catálogos
        if ($field === 'asesor_id') {
            $rows = Catalogos::find()
                ->select(['id', 'nombre'])
                ->where(['tipo' => 'asesor'])
                ->orderBy('nombre')
                ->asArray()
                ->all();

            $options = array_map(static fn($r) => ['value' => (string)$r['id'], 'text' => $r['nombre']], $rows);
            return ['options' => $options];
        }

        // Tipos de campaña desde catálogos
        if ($field === 'campaña_id') {
            $rows = Catalogos::find()
                ->select(['id', 'nombre'])
                ->where(['tipo' => 'campaña'])
                ->orderBy('nombre')
                ->asArray()
                ->all();

            $options = array_map(static fn($r) => ['value' => (string)$r['id'], 'text' => $r['nombre']], $rows);
            return ['options' => $options];
        }

        // Opciones del ENUM "analisis"
        if ($field === 'analisis') {
            $opts = Campanas::optsAnalisis();
            $options = [];
            foreach ($opts as $value => $text) {
                $options[] = ['value' => $value, 'text' => $text];
            }
            return ['options' => $options];
        }

        // Si piden otro campo, devolver vacío
        return ['options' => []];
    }

    /** ===============================
     *  GUARDADO EN TIEMPO REAL (AJAX) NUEVO
     *  Espera: id, field, value
     * =============================== */
public function actionUpdateField()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $id    = Yii::$app->request->post('id');
    // Compat: aceptar 'field' o 'attr'
    $field = Yii::$app->request->post('field', Yii::$app->request->post('attr'));
    $value = Yii::$app->request->post('value');

    if (!$id || !$field) {
        return ['success' => false, 'message' => 'Faltan parámetros'];
    }

    $model = Campanas::findOne($id);
    if (!$model) {
        throw new NotFoundHttpException('Registro no encontrado');
    }

    // Lista blanca de campos editables
    $allowed = [
        'nombre', // <-- añade esto
        'asesor_id', 'campaña_id',
        'inversion', 'mensajes', 'retorno',
        'analisis',
        'mensaje_predeterminado',
    ];
    if (!in_array($field, $allowed, true)) {
        return ['success' => false, 'message' => 'Campo no permitido'];
    }

    try {
        switch ($field) {
            case 'nombre':
                $model->nombre = (string)$value;
                break;

            case 'asesor_id':
            case 'campaña_id':
                $model->$field = ($value === '' || $value === null) ? null : (int)$value;
                break;

            case 'inversion':
            case 'retorno':
                $model->$field = ($value === '' || $value === null) ? 0 : (float)$value;
                break;

            case 'mensajes':
                $model->$field = ($value === '' || $value === null) ? 0 : (int)$value;
                break;

            case 'analisis':
                $opts = Campanas::optsAnalisis();
                if (!isset($opts[$value])) {
                    return ['success' => false, 'message' => 'Valor de análisis inválido'];
                }
                $model->analisis = $value;
                break;

            case 'mensaje_predeterminado':
                $model->mensaje_predeterminado = (string)$value;
                break;
        }

        if (!$model->save()) {
            return ['success' => false, 'message' => 'No se pudo guardar', 'errors' => $model->getErrors()];
        }

        // Construir HTML para refrescar celda
        $newContent = $this->renderCellContent($model, $field);

        return [
            'success'    => true,
            'message'    => 'Guardado correctamente',
            'newContent' => $newContent,
        ];
    } catch (\Throwable $e) {
        Yii::error($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
        return ['success' => false, 'message' => 'Error del servidor'];
    }
}

    /** ===============================
     *  GUARDADO EN TIEMPO REAL (AJAX) LEGADO
     *  Espera: id, attr, value
     *  Redirige internamente a update-field para unificar comportamiento
     * =============================== */
    public function actionUpdateInline(): array
    {
        // Reutiliza la lógica de update-field (acepta 'attr' como alias de 'field')
        return $this->actionUpdateField();
    }

    /** ===============================
     *  FUNCIÓN AUXILIAR
     * =============================== */
    protected function findModel($id)
    {
        if (($model = Campanas::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La campaña solicitada no existe.');
    }

    /**
     * Genera el HTML de la celda después de guardar para evitar refrescos adicionales
     */
    protected function renderCellContent(Campanas $model, string $field): string
    {
        switch ($field) {
            case 'asesor_id':
            case 'campaña_id':
                $nombre = Catalogos::find()->select('nombre')->where(['id' => $model->$field])->scalar();
                $nombre = $nombre ?: 'Sin asignar';
                return '<span class="badge bg-light text-dark">' . Html::encode($nombre) . '</span>';

            case 'inversion':
            case 'retorno':
                $num = (float)$model->$field;
                return '$' . number_format($num, 2);

            case 'mensajes':
                return (string)((int)$model->mensajes);

            case 'analisis':
                $texto = Campanas::optsAnalisis()[$model->analisis] ?? $model->analisis;
                return '<span class="badge bg-secondary">' . Html::encode($texto) . '</span>';

            case 'mensaje_predeterminado':
                return Html::encode(mb_strimwidth((string)$model->mensaje_predeterminado, 0, 60, '…', 'UTF-8'));
        }
        return '';
    }
}