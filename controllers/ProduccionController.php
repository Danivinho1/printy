<?php

namespace app\controllers;

use app\models\Produccion;
use app\models\ProduccionSearch;
use app\models\Catalogos;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use yii;


/**
 * ProduccionController implements the CRUD actions for Produccion model.
 */
class ProduccionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Produccion models.
     *
     * @return string
     */public function actionIndex()
{
    $searchModel = new ProduccionSearch();

    // 🔹 Cargar parámetros de la request
    $params = Yii::$app->request->queryParams;

    // 🔹 Pasar al search
    $dataProvider = $searchModel->search($params);

    // 🔹 Obtener el ID de "Listo" en catálogos (tipo estatus)
    $listoId = Catalogos::find()
        ->select('id')
        ->where(['nombre' => 'Listo', 'tipo' => 'estatus'])
        ->scalar();

    // 🔹 Filtrar SIEMPRE por diseños en estatus "Listo"
    $dataProvider->query
        ->joinWith(['venta.diseno d'])
        ->andWhere(['d.estatus_id' => $listoId]);

    return $this->render('index', [
        'searchModel'  => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}


    
    public function actionImpresion()
{
    // Obtener el ID del estatus "Listo" desde catalogos
    $listoId = Catalogos::find()->select('id')->where(['nombre' => 'Listo', 'tipo' => 'estatus'])->scalar();

    $query = Produccion::find()
        ->alias('p')
        ->joinWith(['venta v', 'venta.diseno d', 'venta.diseno.entrega e'])
        ->andWhere(['d.estatus_id' => $listoId]);

    // Orden similar a DisenoSearch: fecha_entrega, urgente, luego otros
    $query->addOrderBy(new Expression("
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN 1
            WHEN e.nombre = 'Urgente' THEN 2
            ELSE 3
        END ASC,
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN v.fecha_entrega
            ELSE NOW()
        END ASC
    "));
    $query->addOrderBy(['d.id' => SORT_ASC, 'd.created_at' => SORT_ASC]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
        'sort' => false, // ya definimos el orden manualmente
    ]);

    return $this->render('impresion', [
        'dataProvider' => $dataProvider,
    ]);
}  
    
    public function actionCorte()
{
    $query = Produccion::find()
    ->alias('p')
    ->joinWith(['venta v', 'venta.diseno d', 'venta.diseno.entrega e'])
    ->andWhere(['or',
    ['p.diseno_impresion' => 1],
    ['p.no_impresion' => 1]
]);



    // Orden similar a DisenoSearch: fecha_entrega, urgente, luego otros
    $query->addOrderBy(new Expression("
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN 1
            WHEN e.nombre = 'Urgente' THEN 2
            ELSE 3
        END ASC,
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN v.fecha_entrega
            ELSE NOW()
        END ASC
    "));
    $query->addOrderBy(['d.id' => SORT_ASC, 'd.created_at' => SORT_ASC]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
        'sort' => false, // ya definimos el orden manualmente
    ]);

    return $this->render('corte', [
        'dataProvider' => $dataProvider,
    ]);
}    

    public function actionFabricacion()
{
    $query = Produccion::find()
    ->alias('p')
    ->joinWith(['venta v', 'venta.diseno d', 'venta.diseno.entrega e'])
    ->andWhere(['p.corte_listo' => 1]);


    // Orden similar a DisenoSearch: fecha_entrega, urgente, luego otros
    $query->addOrderBy(new Expression("
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN 1
            WHEN e.nombre = 'Urgente' THEN 2
            ELSE 3
        END ASC,
        CASE
            WHEN v.fecha_entrega IS NOT NULL THEN v.fecha_entrega
            ELSE NOW()
        END ASC
    "));
    $query->addOrderBy(['d.id' => SORT_ASC, 'd.created_at' => SORT_ASC]);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
        'sort' => false, // ya definimos el orden manualmente
    ]);

    return $this->render('fabricacion', [
        'dataProvider' => $dataProvider,
    ]);
}

    /**
     * Displays a single Produccion model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Produccion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Produccion();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Produccion model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Produccion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Produccion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Produccion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Produccion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    
    public function actionToggle()
{
    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');

    $model = Produccion::findOne($id);
    if (!$model) {
        return $this->asJson(['success' => false, 'error' => 'Producción no encontrada']);
    }

    // Campos permitidos para toggles
    $allowedFields = ['diseno_impresion', 'corte_listo', 'fabricacion_listo', 'vector_listo', 'no_impresion'];
    if (!in_array($field, $allowedFields)) {
        return $this->asJson(['success' => false, 'error' => 'Campo inválido']);
    }

    // Inicializar si NULL
    if ($model->$field === null) {
        $model->$field = 0;
    }

    // Alternar valor
    $model->$field = $model->$field ? 0 : 1;

    if ($model->save(false)) {
        return $this->asJson(['success' => true, 'value' => (int)$model->$field]);
    } else {
        return $this->asJson(['success' => false, 'error' => 'No se pudo guardar']);
    }
}
public function actionGuardarEnlace()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    try {
        $id = Yii::$app->request->post('id'); // id de Produccion
        $enlace = Yii::$app->request->post('enlace_vector');

        if (!$id) {
            return ['success' => false, 'error' => 'ID no proporcionado'];
        }

        $model = Produccion::findOne($id);

        if (!$model) {
            return ['success' => false, 'error' => 'Registro no encontrado'];
        }

        $model->enlace_vector = $enlace;

        // 🔹 Guardar solo este atributo, sin validar otros campos obligatorios
        if ($model->save(false, ['enlace_vector'])) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'No se pudo guardar el enlace'];
        }

    } catch (\Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


public function actionToggleField()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = Produccion::findOne($id);
    if($model && $field == 'empaquetado_id'){
        $model->$field = $value;
        if($model->save(false)){
            return [
                'success' => true,
                'nombre' => $model->empaquetado ? $model->empaquetado->nombre : 'Desconocido'
            ];
        }
    }
    return ['success'=>false];
}
 public function actionUpdateCheck()
{
    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = Produccion::findOne($id);
    if($model && in_array($field, ['diseno_impresion','corte_listo','fabricacion_listo'])) {
        $model->$field = $value;
        if($model->save(false)) return 'ok';
    }

    Yii::$app->response->statusCode = 400;
    return 'error';
}

public function actionExportExcel()
{
    $producciones = Produccion::find()->all(); // Cambia por tu modelo de Producción
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Título
    $mes = date('F Y');
    $sheet->setCellValue('A1', "Producción del mes: $mes");
    $sheet->mergeCells('A1:Q1'); // Ajustado para más columnas
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados basados en el GridView de letreros adaptado para producción
    $headers = [
        'ID', 'Tipo Letrero', 'Nombre Letrero', 'Entrega', 'Diseñador', 'Unidades',
        'Diseño Impresión', 'Corte', 'Fabricación', 'Fecha Entrega', 'Días Restantes', 'Estatus'
    ];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col.'2', $header);
        $sheet->getStyle($col.'2')->getFont()->setBold(true);
        $col++;
    }

    // Función de color igual que en badges
    $getBadgeColor = function($nombre){
        if (!$nombre) return ['bg'=>'CCCCCC','text'=>'000000'];
        $hash = substr(md5($nombre),0,6);
        return ['bg'=>$hash,'text'=>'FFFFFF'];
    };

    $row = 3;
    foreach($producciones as $produccion){
        $sheet->setCellValue('A'.$row, $produccion->id);

        // Tipo Letrero
        $tipo = $produccion->tipoLetrero->nombre ?? 'No definido';
        $color = $getBadgeColor($tipo);
        $sheet->setCellValue('B'.$row, $tipo);
        $sheet->getStyle('B'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('B'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Nombre Letrero
        $sheet->setCellValue('C'.$row, $produccion->nombre_letrero);

        // Entrega
        $entrega = $produccion->venta->entrega->nombre ?? 'No definido';
        $color = $getBadgeColor($entrega);
        $sheet->setCellValue('D'.$row, $entrega);
        $sheet->getStyle('D'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('D'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Diseñador
        $disenador = $produccion->disenador->nombre ?? 'Sin asignar';
        $color = $getBadgeColor($disenador);
        $sheet->setCellValue('E'.$row, $disenador);
        $sheet->getStyle('E'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('E'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Unidades
        $sheet->setCellValue('F'.$row, $produccion->unidades);

        // Diseño Impresión
        $disenoImpresion = $produccion->diseno_impresion == 1 ? '✓' : '✗';
        $sheet->setCellValue('G'.$row, $disenoImpresion);
        $sheet->getStyle('G'.$row)->getFont()->setBold(true);
        $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($produccion->diseno_impresion == 1) {
            $sheet->getStyle('G'.$row)->getFont()->getColor()->setRGB('28a745');
        } else {
            $sheet->getStyle('G'.$row)->getFont()->getColor()->setRGB('dc3545');
        }

        // Corte Listo
        $corteListo = $produccion->corte_listo == 1 ? '✓' : '✗';
        $sheet->setCellValue('H'.$row, $corteListo);
        $sheet->getStyle('H'.$row)->getFont()->setBold(true);
        $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($produccion->corte_listo == 1) {
            $sheet->getStyle('H'.$row)->getFont()->getColor()->setRGB('28a745');
        } else {
            $sheet->getStyle('H'.$row)->getFont()->getColor()->setRGB('dc3545');
        }

        // Fabricación Listo
        $fabricacionListo = $produccion->fabricacion_listo == 1 ? '✓' : '✗';
        $sheet->setCellValue('I'.$row, $fabricacionListo);
        $sheet->getStyle('I'.$row)->getFont()->setBold(true);
        $sheet->getStyle('I'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        if ($produccion->fabricacion_listo == 1) {
            $sheet->getStyle('I'.$row)->getFont()->getColor()->setRGB('28a745');
        } else {
            $sheet->getStyle('I'.$row)->getFont()->getColor()->setRGB('dc3545');
        }

        // Fecha Entrega
        $fechaEntrega = ($produccion->venta && $produccion->venta->fecha_entrega) 
            ? Yii::$app->formatter->asDate($produccion->venta->fecha_entrega, 'php:d/m/Y') 
            : 'No definida';
        $sheet->setCellValue('J'.$row, $fechaEntrega);

        // Días Restantes
        if ($produccion->venta && $produccion->venta->fecha_entrega) {
            $hoy = new \DateTime();
            $fechaEntrega = new \DateTime($produccion->venta->fecha_entrega);

            $diasHabiles = 0;
            $fechaIter = clone $hoy;

            // Contar solo lunes a viernes
            while ($fechaIter <= $fechaEntrega) {
                $diaSemana = (int)$fechaIter->format('N'); // 1=lunes, 7=domingo
                if ($diaSemana < 6) { 
                    $diasHabiles++;
                }
                $fechaIter->modify('+1 day');
            }

            $diasText = $diasHabiles . ' día' . ($diasHabiles == 1 ? '' : 's');
            $sheet->setCellValue('K'.$row, $diasText);

            // Colorear según días restantes
            if ($diasHabiles > 5) {
                $sheet->getStyle('K'.$row)->getFont()->getColor()->setRGB('28a745'); // Verde
            } elseif ($diasHabiles >= 1) {
                $sheet->getStyle('K'.$row)->getFont()->getColor()->setRGB('ffc107'); // Amarillo
            } else {
                $sheet->getStyle('K'.$row)->getFont()->getColor()->setRGB('dc3545'); // Rojo
            }
        } else {
            $sheet->setCellValue('K'.$row, 'No definida');
            $sheet->getStyle('K'.$row)->getFont()->getColor()->setRGB('6c757d'); // Gris
        }

        // Estatus (Empaquetado)
        $estatus = $produccion->empaquetado ? $produccion->empaquetado->nombre : 'Pendiente';
        switch (strtolower($estatus)) {
            case 'listo':
                $colores = ['bg'=>'28a745','text'=>'ffffff'];
                break;
            case 'pendiente':
                $colores = ['bg'=>'dc3545','text'=>'ffffff'];
                break;
            default:
                $colores = $getBadgeColor($estatus);
                break;
        }
        $sheet->setCellValue('L'.$row, $estatus);
        $sheet->getStyle('L'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colores['bg']);
        $sheet->getStyle('L'.$row)->getFont()->getColor()->setRGB($colores['text']);

        $row++;
    }

    // Auto-ajustar columnas
    foreach(range('A','L') as $colID){
        $sheet->getColumnDimension($colID)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = "produccion_$mes.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}




}
