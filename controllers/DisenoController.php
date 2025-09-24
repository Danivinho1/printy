<?php

namespace app\controllers;

use Yii;
use app\models\Diseno;
use app\models\DisenoSearch;
use app\models\Catalogos;
use app\models\Ventas;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\helpers\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class DisenoController extends Controller
{
    public function actionIndex()
{


    $searchModel = new DisenoSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    // Acción para obtener opciones de select
    public function actionGetSelectOptions($field)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $tipoCatalogoMap = [
                'estatus' => 'estatus',
                'responsable_id' => 'responsable',
                'tipo_letrero_id' => 'tipo_letrero',
                'entrega_id' => 'entrega',
            ];

            if (!isset($tipoCatalogoMap[$field])) {
                throw new BadRequestHttpException("Campo no válido: $field");
            }

            $tipo = $tipoCatalogoMap[$field];
            $registros = Catalogos::find()->where(['tipo' => $tipo])->all();

            $options = [];
            foreach ($registros as $r) {
                $options[] = ['value' => $r->id, 'text' => $r->nombre];
            }

            return ['options' => $options];

        } catch (\Throwable $e) {
            return ['options' => [], 'error' => $e->getMessage()];
        }
    }

    // Acción para obtener opciones de multiselect
    public function actionGetMultiselectOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isAjax) {
            return ['options' => []];
        }
        
        $type = Yii::$app->request->get('type');
        
        try {
            // Mapear los tipos correctos
            $tipoMapping = [
                'adicional' => 'adicionales',
                'extra' => 'extras'
            ];
            
            $tipoBusqueda = isset($tipoMapping[$type]) ? $tipoMapping[$type] : $type;
            
            if (in_array($tipoBusqueda, ['adicionales', 'extras'])) {
                $options = Catalogos::find()
                    ->select(['id as value', 'nombre as text'])
                    ->where(['tipo' => $tipoBusqueda, 'activo' => 1])
                    ->orderBy('orden ASC, nombre ASC')
                    ->asArray()
                    ->all();
            } else {
                $options = [];
            }
            
            return ['options' => $options];
            
        } catch (\Exception $e) {
            Yii::error("Error cargando opciones multiselect para tipo {$type}: " . $e->getMessage(), __METHOD__);
            return ['options' => [], 'error' => $e->getMessage()];
        }
    }
    

    // Acción para actualizar campos simples
    public function actionUpdateField()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    $model = Diseno::findOne($id);
    if (!$model) return ['success'=>false,'message'=>'Registro no encontrado'];

    $model->$field = $value;

    if ($model->save()) {
        if ($field === 'estatus_id') {
            $nombre = $model->estatus->nombre ?? 'Pendiente';
            $colores = match($nombre) {
                'Pendiente' => ['bg'=>'#dc3545','text'=>'#fff'],
                'Listo' => ['bg'=>'#28a745','text'=>'#fff'],
                default => $this->generarColorUnico($nombre)
            };
            $badge = '<span class=\"badge\" style=\"background-color:'.$colores['bg'].'; color:'.$colores['text'].';\">'.$nombre.'</span>';
            return ['success'=>true,'newContent'=>$badge];
        }
        return ['success'=>true];
    }

    return ['success'=>false,'message'=>'Error al guardar: '.json_encode($model->errors)];
}


    // Acción para actualizar campos many-to-many (extras, adicionales)
    public function actionUpdateManyToMany()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $post = Yii::$app->request->post();
    $id = $post['id'] ?? null;
    $field = $post['field'] ?? null;
    $values = $post['value'] ?? [];

    if (!$id || !$field) {
        return ['success' => false, 'message' => 'Faltan parámetros.'];
    }

    try {
        // Primero buscar el diseño
        $diseno = Diseno::findOne($id);
        if (!$diseno) {
            return ['success' => false, 'message' => 'Diseño no encontrado.'];
        }

        // Obtener la venta relacionada
        $venta = $diseno->venta; // Asumiendo que tienes esta relación definida en el modelo Diseno
        if (!$venta) {
            return ['success' => false, 'message' => 'Venta relacionada no encontrada.'];
        }

        // Asegurarnos de que $values sea un array
        if (!is_array($values)) {
            $values = empty($values) ? [] : [$values];
        }

        // Filtrar valores vacíos
        $values = array_filter($values, function($val) {
            return !empty($val) && $val !== '0';
        });

        // Determinar la relación - usando el ID de la venta
        if ($field === 'adicionales') {
            $relationClass = \app\models\VentasAdicionales::class;
            $relationField = 'adicional_id';
            $foreignKey = 'venta_id';
        } elseif ($field === 'extras') {
            $relationClass = \app\models\VentasExtras::class;
            $relationField = 'extra_id';
            $foreignKey = 'venta_id';
        } else {
            return ['success' => false, 'message' => 'Campo no editable de esta forma.'];
        }

        // Usar transacción
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Eliminar relaciones existentes usando el ID de la venta
            $relationClass::deleteAll([$foreignKey => $venta->id]);

            // Insertar nuevas relaciones
            foreach ($values as $val) {
                if (!empty($val) && $val !== '0') {
                    $model = new $relationClass();
                    $model->{$foreignKey} = $venta->id; // Usar el ID de la venta
                    $model->{$relationField} = $val;
                    if (!$model->save()) {
                        throw new \Exception('Error al guardar relación: ' . json_encode($model->errors));
                    }
                }
            }
            
            $transaction->commit();
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Generar el HTML con badges de colores
        $newContent = $this->generateBadgeContent($values, $field);

        return [
            'success' => true,
            'newContent' => $newContent,
            'message' => ucfirst($field) . ' actualizado correctamente'
        ];

    } catch (\Exception $e) {
        Yii::error("Error actualizando {$field} del diseño {$id}: " . $e->getMessage(), __METHOD__);
        return ['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()];
    }
}

/**
 * Genera el HTML con badges de colores para campos many-to-many
 */
private function generateBadgeContent($values, $field)
{
    if (empty($values)) {
        return '<span class="badge bg-light text-dark">Ninguno</span>';
    }

    // Obtener los nombres de los elementos seleccionados
    $catalogos = Catalogos::find()
        ->select(['id', 'nombre'])
        ->where(['id' => $values])
        ->all();

    if (empty($catalogos)) {
        return '<span class="badge bg-light text-dark">Ninguno</span>';
    }

    $badges = '';
    foreach ($catalogos as $catalogo) {
        $colores = $this->generarColorUnico($catalogo->nombre);
        $badges .= '<span class="badge me-1 mb-1" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                   Html::encode($catalogo->nombre) . '</span>';
    }

    return $badges;
}


/**
 * Genera contenido HTML para campos many-to-many sin usar el modelo problemático
 */
private function generateManyToManyContent($disenoId, $field)
{
    $generarColorUnico = function($texto) {
        $textoLower = strtolower(trim($texto));
        
        if ($textoLower === 'urgente') {
            return ['bg' => '#dc3545', 'text' => '#ffffff'];
        }
        
        $coloresSuaves = [
            ['bg' => '#e3f2fd', 'text' => '#1565c0'],
            ['bg' => '#e8f5e8', 'text' => '#2e7d32'],
            ['bg' => '#fff3e0', 'text' => '#ef6c00'],
            ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
            ['bg' => '#e0f2f1', 'text' => '#00695c'],
            ['bg' => '#fce4ec', 'text' => '#c2185b'],
            ['bg' => '#f5f5f5', 'text' => '#424242'],
            ['bg' => '#e1f5fe', 'text' => '#0277bd'],
            ['bg' => '#fff8e1', 'text' => '#f57f17'],
            ['bg' => '#f9fbe7', 'text' => '#689f38'],
            ['bg' => '#fef7ff', 'text' => '#8e24aa'],
            ['bg' => '#e8eaf6', 'text' => '#3f51b5'],
        ];
        
        $hash = crc32($texto);
        $indice = abs($hash) % count($coloresSuaves);
        
        return $coloresSuaves[$indice];
    };
    
    // Primero obtener el diseño y su venta relacionada
    $diseno = Diseno::findOne($disenoId);
    if (!$diseno || !$diseno->venta) {
        return '<span class="badge bg-light text-dark">Ninguno</span>';
    }
    
    $ventaId = $diseno->venta->id;
    
    if ($field === 'adicionales') {
        $items = Catalogos::find()
            ->innerJoin('ventas_adicionales', 'catalogos.id = ventas_adicionales.adicional_id')
            ->where(['ventas_adicionales.venta_id' => $ventaId])
            ->all();
    } else {
        $items = Catalogos::find()
            ->innerJoin('ventas_extras', 'catalogos.id = ventas_extras.extra_id')
            ->where(['ventas_extras.venta_id' => $ventaId])
            ->all();
    }
    
    if (empty($items)) {
        return '<span class="badge bg-light text-dark">Ninguno</span>';
    }
    
    $badges = '';
    foreach ($items as $item) {
        $colores = $generarColorUnico($item->nombre);
        $badges .= '<span class="badge me-1 mb-1" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                  Html::encode($item->nombre) . '</span>';
    }
    
    return $badges;
}
    // Función de colores (opcional, si la quieres dentro del controlador)
    private function generarColorUnico($texto)
    {
        $textoLower = strtolower(trim($texto));
        if ($textoLower === 'urgente') {
            return ['bg' => '#dc3545', 'text' => '#ffffff'];
        }

        $coloresSuaves = [
            ['bg' => '#e3f2fd', 'text' => '#1565c0'],
            ['bg' => '#e8f5e8', 'text' => '#2e7d32'],
            ['bg' => '#fff3e0', 'text' => '#ef6c00'],
            ['bg' => '#f3e5f5', 'text' => '#7b1fa2'],
            ['bg' => '#e0f2f1', 'text' => '#00695c'],
            ['bg' => '#fce4ec', 'text' => '#c2185b'],
            ['bg' => '#f5f5f5', 'text' => '#424242'],
            ['bg' => '#e1f5fe', 'text' => '#0277bd'],
            ['bg' => '#fff8e1', 'text' => '#f57f17'],
            ['bg' => '#f9fbe7', 'text' => '#689f38'],
            ['bg' => '#fef7ff', 'text' => '#8e24aa'],
            ['bg' => '#e8eaf6', 'text' => '#3f51b5'],
        ];

        $hash = crc32($texto);
        $indice = abs($hash) % count($coloresSuaves);

        return $coloresSuaves[$indice];
    }

    public function actionUpdateAvance()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    try {
        $id = Yii::$app->request->post('id');
        $field = Yii::$app->request->post('field');
        $value = Yii::$app->request->post('value');
        $avance = Yii::$app->request->post('avance');

        // Buscar el diseño
        $model = Diseno::findOne($id);
        if (!$model) {
            return ['status' => 'error', 'message' => 'Diseño no encontrado'];
        }

        // Si se mandó un campo dinámico, actualizarlo
        if ($field && $model->hasAttribute($field)) {
            $model->$field = $value;
        }

        // Actualizar avance
        $model->avance = (int) $avance;

        // Determinar automáticamente el estatus según el avance
        $estatusNombre = ($avance >= 100) ? 'Listo' : 'Pendiente';

        $estatus = Catalogos::find()
            ->where(['tipo' => 'estatus', 'nombre' => $estatusNombre])
            ->one();

        if (!$estatus) {
            return [
                'status' => 'error',
                'message' => "El estatus '{$estatusNombre}' no existe en catalogos"
            ];
        }

        $model->estatus_id = $estatus->id;

        // Guardar sin validación extra (ya tenemos reglas definidas)
        if ($model->save(false)) {
            return [
                'status' => 'ok',
                'message' => 'Avance y estatus actualizados correctamente',
                'avance' => $model->avance,
                'estatus' => $estatus->nombre
            ];
        }

        return ['status' => 'error', 'message' => 'Error al guardar los cambios'];
    } catch (\Throwable $e) {
        Yii::error("Error en updateAvance: " . $e->getMessage(), __METHOD__);
        return [
            'status' => 'error',
            'message' => 'Error interno del servidor',
            'detalle' => $e->getMessage()
        ];
    }
}
   public function actionUpdateFecha()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    $id = Yii::$app->request->post('id');
    $fecha = Yii::$app->request->post('fecha_confirmacion');

    $model = Diseno::findOne($id);
    if (!$model) {
        return ['success' => false, 'message' => 'Modelo no encontrado'];
    }

    $model->fecha_confirmacion = $fecha ?: null;

    if ($model->save(false)) {
        return [
            'success' => true,
            'fecha' => $model->fecha_confirmacion 
                ? Yii::$app->formatter->asDate($model->fecha_confirmacion, 'php:d/m/Y') 
                : null
        ];
    }

    return ['success' => false, 'message' => 'No se pudo guardar'];
}
 public function actionExportExcel()
{
    $disenos = Diseno::find()->all();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Título
    $mes = date('F Y');
    $sheet->setCellValue('A1', "Diseños del mes: $mes");
    $sheet->mergeCells('A1:N1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados
    $headers = [
        'ID','Tipo Letrero','Nombre Letrero','Entrega','Adicionales','Extras',
        'Teléfono','Responsable','Precio Extra','Fecha Confirmación','Vectorizado',
        'Contacto Cliente','Avance','Estatus'
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
    foreach($disenos as $diseño){
        $sheet->setCellValue('A'.$row, $diseño->id);

        // Tipo Letrero
        $tipo = $diseño->tipoLetrero->nombre ?? 'No definido';
        $color = $getBadgeColor($tipo);
        $sheet->setCellValue('B'.$row, $tipo);
        $sheet->getStyle('B'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('B'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Nombre Letrero
        $sheet->setCellValue('C'.$row, $diseño->nombre_letrero);

        // Entrega
        $entrega = $diseño->entrega->nombre ?? 'No definido';
        $color = $getBadgeColor($entrega);
        $sheet->setCellValue('D'.$row, $entrega);
        $sheet->getStyle('D'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('D'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Adicionales
        $sheet->setCellValue('E'.$row, $diseño->adicionalesNombres);

        // Extras
        $sheet->setCellValue('F'.$row, $diseño->extrasNombres);

        // Teléfono
        $sheet->setCellValue('G'.$row, $diseño->telefono);

        // Responsable
        $responsable = $diseño->responsable->nombre ?? 'Sin asignar';
        $color = $getBadgeColor($responsable);
        $sheet->setCellValue('H'.$row, $responsable);
        $sheet->getStyle('H'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('H'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Precio Extra
        $extra = $diseño->extra_precio ?? 0;
        $sheet->setCellValue('I'.$row, $extra);
        $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');

        // Fecha Confirmación
        $fechaConf = $diseño->fecha_confirmacion ? Yii::$app->formatter->asDate($diseño->fecha_confirmacion,'php:d/m/Y') : 'Pendiente';
        $sheet->setCellValue('J'.$row, $fechaConf);

        // Vectorizado
        $vectorizado = $diseño->vectorizado_id == 1 ? '✓' : '✗';
        $sheet->setCellValue('K'.$row, $vectorizado);
        $sheet->getStyle('K'.$row)->getFont()->setBold(true);
        $sheet->getStyle('K'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Contacto Cliente
        $contacto = $diseño->contacto_cliente_id == 1 ? '✓' : '✗';
        $sheet->setCellValue('L'.$row, $contacto);
        $sheet->getStyle('L'.$row)->getFont()->setBold(true);
        $sheet->getStyle('L'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Avance
        $sheet->setCellValue('M'.$row, $diseño->avance.'%');

        // Estatus
        $estatus = $diseño->estatus->nombre ?? 'Pendiente';
        $colores = match($estatus){
            'Pendiente'=>['bg'=>'dc3545','text'=>'ffffff'],
            'Listo'=>['bg'=>'28a745','text'=>'ffffff'],
            default => $getBadgeColor($estatus)
        };
        $sheet->setCellValue('N'.$row, $estatus);
        $sheet->getStyle('N'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colores['bg']);
        $sheet->getStyle('N'.$row)->getFont()->getColor()->setRGB($colores['text']);

        $row++;
    }

    // Auto-ajustar columnas
    foreach(range('A','N') as $colID){
        $sheet->getColumnDimension($colID)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = "disenos_$mes.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

}
