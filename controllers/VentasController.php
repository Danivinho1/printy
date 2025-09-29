<?php

namespace app\controllers;

use Yii;
use app\models\Ventas;
use app\models\VentasSearch;
use app\models\Metas;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use app\models\VentasAdicionales;
use app\models\VentasExtras;
use app\models\Catalogos;
use yii\web\Response;
use yii\helpers\html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * VentasController implements the CRUD actions for Ventas model.
 */
class VentasController extends Controller
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
                        'update-field' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Ventas models.
     *
     * @return string
     */
    public function actionIndex()
{
    $searchModel = new VentasSearch();
    $searchModel->load(Yii::$app->request->queryParams);

    // Query principal con join y orden personalizado
    $query = Ventas::find()
        ->joinWith('entrega') // asegúrate de que el alias sea correcto
        ->orderBy([
            // Primero las ventas con fecha
            new \yii\db\Expression('CASE WHEN fecha_entrega IS NOT NULL THEN 0 ELSE 1 END ASC'),
            // Dentro de las que tienen fecha, las más próximas primero
            'fecha_entrega' => SORT_ASC,
            // Para las que no tienen fecha: poner Urgente primero
            new \yii\db\Expression("CASE WHEN catalogos.nombre = 'Urgente' THEN 0 ELSE 1 END ASC"),
            // Finalmente, por id Ascendente
            'id' => SORT_ASC,
        ]);

    // Aplicar filtros desde VentasSearch
    if ($searchModel->entrega_id) {
        $query->andFilterWhere(['entrega_id' => $searchModel->entrega_id]);
    }

    $filtroEntrega = \Yii::$app->request->get('entrega');
    if ($filtroEntrega === 'urgente') {
        $query->andWhere(['catalogos.nombre' => 'Urgente']);
    }

    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
    ]);

    // Modelo para el modal
    $modeloNuevo = new Ventas();
    if ($modeloNuevo->load(Yii::$app->request->post()) && $modeloNuevo->save()) {
        Yii::$app->session->setFlash('success', 'Venta guardada correctamente.');
        return $this->refresh();
    }

    return $this->render('index', [
        'searchModel' => $searchModel, // para los filtros en el GridView
        'dataProvider' => $dataProvider,
        'modeloNuevo' => $modeloNuevo,
    ]);
}

    /**
     * Creates a new Ventas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    
    
    
    public function actionCreate()
    {

        $model = new Ventas();

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
     * Updates an existing Ventas model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
{
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
        // Guardamos el modelo principal primero
        if ($model->save()) {
            // Guardar adicionales
            $adicionales = Yii::$app->request->post('Ventas')['adicionales_ids'] ?? [];
            VentasAdicionales::deleteAll(['venta_id' => $model->id]);
            foreach ($adicionales as $adicional) {
                $va = new VentasAdicionales();
                $va->venta_id = $model->id;
                $va->adicional_id = $adicional;
                $va->save();
            }

            // Guardar extras
            $extras = Yii::$app->request->post('Ventas')['extras_ids'] ?? [];
            VentasExtras::deleteAll(['venta_id' => $model->id]);
            foreach ($extras as $extra) {
                $ve = new VentasExtras();
                $ve->venta_id = $model->id;
                $ve->extra_id = $extra;
                $ve->save();
            }

            Yii::$app->session->setFlash('success', 'Venta actualizada correctamente');
            return $this->redirect(['index']);
        }
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}


    /**
     * Deletes an existing Ventas model.
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

    private function generarColorUnico($texto)
{
    $textoLower = strtolower(trim($texto));

    // Estilo especial para "urgente"
    if ($textoLower === 'urgente') {
        return ['bg' => '#dc3545', 'text' => '#ffffff']; // Rojo brillante
    }

    // Paleta de colores suaves
    $coloresSuaves = [
        ['bg' => '#e3f2fd', 'text' => '#1565c0'], // Azul suave
        ['bg' => '#e8f5e8', 'text' => '#2e7d32'], // Verde suave
        ['bg' => '#fff3e0', 'text' => '#ef6c00'], // Naranja suave
        ['bg' => '#f3e5f5', 'text' => '#7b1fa2'], // Morado suave
        ['bg' => '#e0f2f1', 'text' => '#00695c'], // Verde agua suave
        ['bg' => '#fce4ec', 'text' => '#c2185b'], // Rosa suave
        ['bg' => '#f5f5f5', 'text' => '#424242'], // Gris suave
        ['bg' => '#e1f5fe', 'text' => '#0277bd'], // Cian suave
        ['bg' => '#fff8e1', 'text' => '#f57f17'], // Amarillo suave
        ['bg' => '#f9fbe7', 'text' => '#689f38'], // Verde lima suave
        ['bg' => '#fef7ff', 'text' => '#8e24aa'], // Lavanda suave
        ['bg' => '#e8eaf6', 'text' => '#3f51b5'], // Índigo suave
    ];

    // Generar índice basado en el texto para consistencia
    $hash = crc32($texto);
    $indice = abs($hash) % count($coloresSuaves);

    return $coloresSuaves[$indice];
}


    /**
     * Actualiza un campo específico de una venta via AJAX
     */
public function actionUpdateField()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
        return ['success' => false, 'message' => 'Solicitud inválida'];
    }

    $id = Yii::$app->request->post('id');
    $field = Yii::$app->request->post('field');
    $value = Yii::$app->request->post('value');

    if (!$id || !$field) {
        return ['success' => false, 'message' => 'Parámetros faltantes'];
    }

    $model = Ventas::findOne($id);
    if (!$model) {
        return ['success' => false, 'message' => 'Registro no encontrado'];
    }

    $allowedFields = [
        'nombre_letrero', 'telefono', 'unidades', 'extra_precio', 'precio_total', 
        'anticipo', 'restante', 'fecha_compra', 'tipo_letrero_id', 'entrega_id', 
        'medio_id', 'campaña_id', 'asesor_id', 'adicionales', 'extras'
    ];

    if (!in_array($field, $allowedFields)) {
        return ['success' => false, 'message' => 'Campo no editable'];
    }

    try {
        // --- Campos many-to-many ---
        if (in_array($field, ['adicionales', 'extras'])) {
            $ids = !empty($value) ? array_map('intval', explode(',', $value)) : [];
            $tipoCatalogo = $field;
            
            // Validar que todos los IDs existan
            if (!empty($ids)) {
                $countValid = Catalogos::find()
                    ->where(['id' => $ids, 'tipo' => $tipoCatalogo, 'activo' => 1])
                    ->count();
                if ($countValid !== count($ids)) {
                    return ['success' => false, 'message' => 'Algunos elementos seleccionados no son válidos'];
                }
            }

            // Guardar relaciones
            if (!$this->saveManyToMany($model->id, $field, $ids)) {
                return ['success' => false, 'message' => 'Error guardando relaciones'];
            }

            $model->refresh(); // refrescar modelo con relaciones
            $newContent = $this->generateFieldContent($model, $field);

            return ['success' => true, 'message' => 'Campo actualizado correctamente', 'newContent' => $newContent];
        }

        // --- Campos simples ---
        switch ($field) {
            case 'unidades':
                $value = (int) $value;
                if ($value < 0) return ['success' => false, 'message' => 'Las unidades no pueden ser negativas'];
                break;
            case 'extra_precio':
            case 'precio_total':
            case 'anticipo':
            case 'restante':
                $value = (float) $value;
                if ($value < 0) return ['success' => false, 'message' => 'Los precios no pueden ser negativos'];
                break;
            case 'fecha_compra':
                $value = $value ? \DateTime::createFromFormat('Y-m-d', $value)?->format('Y-m-d') : null;
                if ($value === false) return ['success' => false, 'message' => 'Formato de fecha inválido'];
                break;
            case 'tipo_letrero_id':
            case 'entrega_id':
            case 'medio_id':
            case 'campaña_id':
            case 'asesor_id':
                $value = $value ? (int)$value : null;
                if ($value && !$this->validateRelationId($field, $value)) {
                    return ['success' => false, 'message' => 'Opción seleccionada no válida'];
                }
                break;
            case 'nombre_letrero':
            case 'telefono':
                $value = trim($value);
                if ($value === '') return ['success' => false, 'message' => 'Este campo no puede estar vacío'];
                break;
        }

        $model->$field = $value;

        if (!$model->validate([$field])) {
            $errors = $model->getFirstErrors();
            return ['success' => false, 'message' => reset($errors) ?: 'Error de validación'];
        }

        $model->save(false, [$field]);
        $model->refresh();

        $newContent = $this->generateFieldContent($model, $field);

        return ['success' => true, 'message' => 'Campo actualizado correctamente', 'newContent' => $newContent];

    } catch (\Exception $e) {
        Yii::error("Excepción en updateField: " . $e->getMessage(), __METHOD__);
        return ['success' => false, 'message' => 'Error interno del servidor'];
    }
}

/**
 * Guarda relaciones many-to-many (adicionales/extras)
 */
private function saveManyToMany($ventaId, $field, $ids)
{
    $class = $field === 'adicionales' ? VentasAdicionales::class : VentasExtras::class;
    $column = $field === 'adicionales' ? 'adicional_id' : 'extra_id';

    $class::deleteAll(['venta_id' => $ventaId]);

    foreach ($ids as $id) {
        $rel = new $class();
        $rel->venta_id = $ventaId;
        $rel->$column = $id;
        if (!$rel->save()) {
            Yii::error("Error guardando relación $field: " . print_r($rel->getErrors(), true), __METHOD__);
            return false;
        }
    }

    return true;
}


    /**
     * Valida que un ID de relación exista en su tabla correspondiente
     */
    private function validateRelationId($field, $id)
    {
        try {
            // Mapear campos a tipos de catálogos para validación específica
            $tiposCatalogos = [
                'tipo_letrero_id' => 'tipo_letrero',
                'entrega_id' => 'entrega',
                'medio_id' => 'medio',
                'campaña_id' => 'campaña',
                'asesor_id' => 'asesor'
            ];
            
            if (isset($tiposCatalogos[$field])) {
                $tipoCatalogo = $tiposCatalogos[$field];
                return Catalogos::find()
                    ->where(['id' => $id, 'tipo' => $tipoCatalogo])
                    ->exists();
            }
            
            // Si no es un campo de relación conocido, solo verificar que el ID existe
            return Catalogos::find()->where(['id' => $id])->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Genera el contenido formateado para mostrar en la tabla
     * Incluye tanto campos básicos como campos de relación
     */
    private function generateFieldContent($model, $field)
    {
        // Función auxiliar para generar colores
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
        
        switch ($field) {
            case 'extra_precio':
            case 'precio_total':
            case 'anticipo':
            case 'restante':
                $value = $model->$field ? $model->$field : 0;
                return '$' . number_format($value, 2);
                
            case 'fecha_compra':
                return $model->fecha_compra ? 
                       Yii::$app->formatter->asDate($model->fecha_compra, 'php:d/m/Y') : '';
                
            case 'unidades':
                return (string) $model->$field;
                
            case 'tipo_letrero_id':
                if (!$model->tipoLetrero) {
                    return '<span class="badge bg-secondary">No definido</span>';
                }
                $colores = $generarColorUnico($model->tipoLetrero->nombre);
                return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                       $model->tipoLetrero->nombre . '</span>';
                       
            case 'entrega_id':
                if (!$model->entrega) {
                    return '<span class="badge bg-secondary">No definido</span>';
                }
                $colores = $generarColorUnico($model->entrega->nombre);
                return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                       $model->entrega->nombre . '</span>';
                       
            case 'medio_id':
                if (!$model->medio) {
                    return '<span class="badge bg-secondary">No definido</span>';
                }
                $colores = $generarColorUnico($model->medio->nombre);
                return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                       $model->medio->nombre . '</span>';
                       
            case 'campaña_id':
                if (!$model->campaña) {
                    return '<span class="badge bg-secondary">No definido</span>';
                }
                $colores = $generarColorUnico($model->campaña->nombre);
                return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                       $model->campaña->nombre . '</span>';
                       
            case 'asesor_id':
                if (!$model->asesor) {
                    return '<span class="badge bg-secondary">No definido</span>';
                }
                $colores = $generarColorUnico($model->asesor->nombre);
                return '<span class="badge" style="background-color: ' . $colores['bg'] . '; color: ' . $colores['text'] . ';">' . 
                       $model->asesor->nombre . '</span>';
                
            case 'nombre_letrero':
            case 'telefono':
            default:
                return Html::encode($model->$field);
        }
    }

    /**
     * Obtiene las opciones para campos select de relaciones
     */
    public function actionGetSelectOptions()
    {
        Yii::$app->response->format =Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isAjax) {
            return ['options' => []];
        }
        
        $field = Yii::$app->request->get('field');
        
        try {
            // Mapear campos a tipos de catálogos
            $tiposCatalogos = [
                'tipo_letrero_id' => 'tipo_letrero',
                'entrega_id' => 'entrega',
                'medio_id' => 'medio',
                'campaña_id' => 'campaña',
                'asesor_id' => 'asesor'
            ];
            
            if (isset($tiposCatalogos[$field])) {
                $tipoCatalogo = $tiposCatalogos[$field];
                
                // Usar el método getLista del modelo y considerar solo registros activos
                $opciones = Catalogos::find()
                    ->select(['id as value', 'nombre as text'])
                    ->where(['tipo' => $tipoCatalogo, 'activo' => 1])
                    ->orderBy('orden ASC, nombre ASC')
                    ->asArray()
                    ->all();
                
                return ['options' => $opciones];
                
            } else {
                return ['options' => []];
            }
            
        } catch (\Exception $e) {
            Yii::error("Error cargando opciones para campo {$field}: " . $e->getMessage(), __METHOD__);
            return ['options' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Finds the Ventas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ventas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ventas::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Obtiene las opciones para campos multiselect (adicionales/extras)
     */
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

/* * Acción específica para manejar campos many-to-many sin interferir con el modelo
 */
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
        $venta = Ventas::findOne($id);
        if (!$venta) {
            return ['success' => false, 'message' => 'Venta no encontrada.'];
        }

        // Asegurarnos de que $values sea un array
        if (!is_array($values)) {
            $values = empty($values) ? [] : [$values];
        }

        // Filtrar valores vacíos
        $values = array_filter($values, function($val) {
            return !empty($val) && $val !== '0';
        });

        // Determinar la relación
        if ($field === 'adicionales') {
            $relationClass = VentasAdicionales::class;
            $relationField = 'adicional_id';
        } elseif ($field === 'extras') {
            $relationClass = VentasExtras::class;
            $relationField = 'extra_id';
        } else {
            return ['success' => false, 'message' => 'Campo no editable de esta forma.'];
        }

        // Eliminar relaciones existentes
        $relationClass::deleteAll(['venta_id' => $id]);

        // Insertar nuevas relaciones
        foreach ($values as $val) {
            if (!empty($val) && $val !== '0') {
                $model = new $relationClass();
                $model->venta_id = $id;
                $model->{$relationField} = $val;
                $model->save();
            }
        }

        // Generar el HTML con badges de colores
        $newContent = $this->generateBadgeContent($values, $field);

        return [
            'success' => true,
            'newContent' => $newContent,
            'message' => ucfirst($field) . ' actualizado correctamente'
        ];

    } catch (\Exception $e) {
        Yii::error("Error actualizando {$field} de la venta {$id}: " . $e->getMessage(), __METHOD__);
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
private function generateManyToManyContent($ventaId, $field)
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

public function actionInlineUpdate($id)
{
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        $this->sincronizarAdicionales($model);
        $this->sincronizarExtras($model);
        return 'success';
    }

    return 'error';
}
/**
 * Sincronizar adicionales
 */
protected function sincronizarAdicionales($model)
{
    $actuales = VentasAdicionales::find()->select('adicional_id')->where(['venta_id'=>$model->id])->column();
    $nuevos = $model->adicionales_ids;

    $agregar = array_diff($nuevos, $actuales);
    foreach($agregar as $adicional){
        $va = new VentasAdicionales();
        $va->venta_id = $model->id;
        $va->adicional_id = $adicional;
        $va->save();
    }

    $eliminar = array_diff($actuales, $nuevos);
    if(!empty($eliminar)){
        VentasAdicionales::deleteAll(['venta_id'=>$model->id,'adicional_id'=>$eliminar]);
    }
}

/**
 * Sincronizar extras
 */
protected function sincronizarExtras($model)
{
    $actuales = VentasExtras::find()->select('extra_id')->where(['venta_id'=>$model->id])->column();
    $nuevos = $model->extras_ids;

    $agregar = array_diff($nuevos, $actuales);
    foreach($agregar as $extra){
        $ve = new VentasExtras();
        $ve->venta_id = $model->id;
        $ve->extra_id = $extra;
        $ve->save();
    }

    $eliminar = array_diff($actuales, $nuevos);
    if(!empty($eliminar)){
        VentasExtras::deleteAll(['venta_id'=>$model->id,'extra_id'=>$eliminar]);
    }
}
public function actionActualizarRestante()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
        return ['success' => false, 'message' => 'Solicitud inválida'];
    }

    $id = Yii::$app->request->post('id');

    if (!$id) {
        return ['success' => false, 'message' => 'ID faltante'];
    }

    $model = Ventas::findOne($id);
    if (!$model) {
        return ['success' => false, 'message' => 'Registro no encontrado'];
    }

    // Calcular restante
    $precioTotal = $model->precio_total ?? 0;
    $anticipo = $model->anticipo ?? 0;
    $restante = max(0, $precioTotal - $anticipo);
    $model->restante = $restante;

    if (!$model->save(false, ['restante'])) {
        return ['success' => false, 'message' => 'No se pudo actualizar restante'];
    }

    // Generar HTML con formato de badge
    $restanteHtml = '<span class="badge" style="color:red; font-weight:bold;">$' . number_format($restante, 2) . '</span>';

    return [
        'success' => true,
        'restanteHtml' => $restanteHtml
    ];
}    
public function actionSyncFromLogistica()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $id = Yii::$app->request->post('id');

    $venta = Ventas::findOne($id);
    if (!$venta) return ['success' => false, 'message' => 'Venta no encontrada'];

    // Formatear HTML para cada campo
    $precioTotalHtml = '<span class="badge" style="color:green; font-weight:bold;">$' . number_format($venta->precio_total,2) . '</span>';
    $anticipoHtml = '<span class="badge" style="color:orange; font-weight:bold;">$' . number_format($venta->anticipo,2) . '</span>';
    $restanteHtml = '<span class="badge" style="color:red; font-weight:bold;">$' . number_format($venta->restante,2) . '</span>';

    return [
        'success' => true,
        'precio_total' => $venta->precio_total,
        'anticipo' => $venta->anticipo,
        'restante' => $venta->restante,
        'precioTotalHtml' => $precioTotalHtml,
        'anticipoHtml' => $anticipoHtml,
        'restanteHtml' => $restanteHtml
    ];
}

public function actionDashboard()
{
    $this->layout = 'main'; // O el layout que uses normalmente
    
    // Obtener todas las ventas del mes actual
    $mesActual = date('Y-m');
    $ventasDelMes = Ventas::find()
        ->where(['like', 'fecha_compra', $mesActual])
        ->with(['tipoLetrero', 'asesor', 'medio', 'campaña', 'entrega'])
        ->all();

    // Calcular totales del mes
    $totalUnidades = 0;
    $totalDinero = 0;
    $totalAnticipo = 0;
    $totalRestante = 0;

    foreach ($ventasDelMes as $venta) {
        $totalUnidades += (int)$venta->unidades;
        $totalDinero += (float)$venta->precio_total;
        $totalAnticipo += (float)$venta->anticipo;
        $totalRestante += (float)$venta->restante;
    }

    // ===== CAMBIO PRINCIPAL: Usar metas de la base de datos =====
    // ANTES:
    // $metaUnidades = 100;
    // $metaDinero = 500000;
    
    // AHORA:
    // Crear metas por defecto si no existen
    Metas::crearMetasPorDefecto($mesActual);
    
    // Obtener las metas desde la base de datos
    $metaUnidades = Metas::getMetaUnidades($mesActual);
    $metaDinero = Metas::getMetaDinero($mesActual);
    // ============================================================

    // Agrupar datos por categorías (tu código existente sin cambios)
    $conteoProductos = [];
    $conteoAsesores = [];
    $conteoMedios = [];

    foreach ($ventasDelMes as $venta) {
        // Productos más vendidos
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
            $conteoProductos[$tipoId]['total_ventas'] += (float)$venta->precio_total;
        }
        
        // Asesores más productivos
        if ($venta->asesor) {
            $asesorId = $venta->asesor_id;
            $nombreAsesor = $venta->asesor->nombre;
            
            if (!isset($conteoAsesores[$asesorId])) {
                $conteoAsesores[$asesorId] = [
                    'id' => $asesorId,
                    'nombre' => $nombreAsesor,
                    'cantidad' => 0,
                    'total_ventas' => 0
                ];
            }
            
            $conteoAsesores[$asesorId]['cantidad']++;
            $conteoAsesores[$asesorId]['total_ventas'] += (float)$venta->precio_total;
        }
        
        // Medios más efectivos
        if ($venta->medio) {
            $medioId = $venta->medio_id;
            $nombreMedio = $venta->medio->nombre;
            
            if (!isset($conteoMedios[$medioId])) {
                $conteoMedios[$medioId] = [
                    'id' => $medioId,
                    'nombre' => $nombreMedio,
                    'cantidad' => 0,
                    'total_ventas' => 0
                ];
            }
            
            $conteoMedios[$medioId]['cantidad']++;
            $conteoMedios[$medioId]['total_ventas'] += (float)$venta->precio_total;
        }
    }

    // Ordenar arrays por rendimiento (tu código existente sin cambios)
    uasort($conteoProductos, function($a, $b) {
        return $b['cantidad'] - $a['cantidad'];
    });

    uasort($conteoAsesores, function($a, $b) {
        return $b['total_ventas'] - $a['total_ventas'];
    });

    uasort($conteoMedios, function($a, $b) {
        return $b['total_ventas'] - $a['total_ventas'];
    });

    // Tomar solo los primeros 5 de cada categoría
    $productosVendidos = array_slice($conteoProductos, 0, 5, true);

    // Productos con mayor potencial (precio promedio alto)
    $productosPotencial = [];
    foreach ($conteoProductos as $producto) {
        if ($producto['cantidad'] >= 2) {
            $promedioVenta = $producto['total_ventas'] / $producto['cantidad'];
            $productosPotencial[] = array_merge($producto, [
                'promedio_precio' => $promedioVenta
            ]);
        }
    }

    // Ordenar por precio promedio
    usort($productosPotencial, function($a, $b) {
        return $b['promedio_precio'] - $a['promedio_precio'];
    });

    $productosPotencial = array_slice($productosPotencial, 0, 5);

    return $this->render('dashboard', [
        'ventasDelMes' => $ventasDelMes,
        'totalUnidades' => $totalUnidades,
        'totalDinero' => $totalDinero,
        'totalAnticipo' => $totalAnticipo,
        'totalRestante' => $totalRestante,
        'metaUnidades' => $metaUnidades,
        'metaDinero' => $metaDinero,
        'productosVendidos' => $productosVendidos,
        'productosPotencial' => $productosPotencial,
        'conteoAsesores' => $conteoAsesores,
        'conteoMedios' => $conteoMedios,
        'mesActual' => $mesActual
    ]);
}

// ===== NUEVAS ACCIONES PARA EDITAR METAS =====

/**
 * Actualiza la meta de unidades
 * @return array
 */
public function actionActualizarMetaUnidades()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isPost) {
        return [
            'success' => false,
            'message' => 'Método no permitido'
        ];
    }
    
    try {
        $metaUnidades = Yii::$app->request->post('meta_unidades');
        $periodo = date('Y-m'); // Mes actual
        
        // Validaciones
        if (!is_numeric($metaUnidades) || $metaUnidades < 0) {
            return [
                'success' => false,
                'message' => 'El valor debe ser un número válido mayor o igual a 0'
            ];
        }
        
        $metaUnidades = (int) $metaUnidades;
        
        // Validación adicional
        if ($metaUnidades > 1000000) { // 1 millón como límite
            return [
                'success' => false,
                'message' => 'El valor parece demasiado alto. Verifique el número ingresado.'
            ];
        }
        
        // Guardar en la base de datos usando el modelo
        if (Metas::setMetaUnidades($metaUnidades, $periodo)) {
            
            // Calcular nuevo porcentaje usando tus totales actuales
            $totalUnidades = $this->getTotalUnidadesMes($periodo);
            $porcentaje = $metaUnidades > 0 
                ? round(($totalUnidades / $metaUnidades) * 100, 2) 
                : 0;
            
            return [
                'success' => true,
                'message' => 'Meta de unidades actualizada correctamente',
                'data' => [
                    'nueva_meta' => $metaUnidades,
                    'porcentaje' => $porcentaje
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al guardar la meta en la base de datos'
            ];
        }
        
    } catch (\Exception $e) {
        Yii::error('Error al actualizar meta de unidades: ' . $e->getMessage(), __METHOD__);
        
        return [
            'success' => false,
            'message' => 'Error interno del servidor. Por favor intente nuevamente.'
        ];
    }
}

/**
 * Actualiza la meta de dinero
 * @return array
 */
public function actionActualizarMetaDinero()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    if (!Yii::$app->request->isPost) {
        return [
            'success' => false,
            'message' => 'Método no permitido'
        ];
    }
    
    try {
        $metaDinero = Yii::$app->request->post('meta_dinero');
        $periodo = date('Y-m'); // Mes actual
        
        // Validaciones
        if (!is_numeric($metaDinero) || $metaDinero < 0) {
            return [
                'success' => false,
                'message' => 'El valor debe ser un número válido mayor o igual a 0'
            ];
        }
        
        $metaDinero = (float) $metaDinero;
        
        // Validación adicional para montos razonables
        if ($metaDinero > 10000000) { // 10 millones como límite
            return [
                'success' => false,
                'message' => 'El monto ingresado parece demasiado alto. Verifique el valor.'
            ];
        }
        
        // Guardar en la base de datos usando el modelo
        if (Metas::setMetaDinero($metaDinero, $periodo)) {
            
            // Calcular nuevo porcentaje usando tus totales actuales
            $totalDinero = $this->getTotalDineroMes($periodo);
            $porcentaje = $metaDinero > 0 
                ? round(($totalDinero / $metaDinero) * 100, 2) 
                : 0;
            
            return [
                'success' => true,
                'message' => 'Meta de dinero actualizada correctamente',
                'data' => [
                    'nueva_meta' => $metaDinero,
                    'porcentaje' => $porcentaje
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al guardar la meta en la base de datos'
            ];
        }
        
    } catch (\Exception $e) {
        Yii::error('Error al actualizar meta de dinero: ' . $e->getMessage(), __METHOD__);
        
        return [
            'success' => false,
            'message' => 'Error interno del servidor. Por favor intente nuevamente.'
        ];
    }
}

// ===== MÉTODOS AUXILIARES =====

/**
 * Obtiene el total de unidades de un período específico
 * @param string $periodo Formato Y-m
 * @return int
 */
private function getTotalUnidadesMes($periodo)
{
    $ventas = Ventas::find()
        ->where(['like', 'fecha_compra', $periodo])
        ->all();
    
    $total = 0;
    foreach ($ventas as $venta) {
        $total += (int)$venta->unidades;
    }
    
    return $total;
}

/**
 * Obtiene el total de dinero de un período específico
 * @param string $periodo Formato Y-m
 * @return float
 */
private function getTotalDineroMes($periodo)
{
    $ventas = Ventas::find()
        ->where(['like', 'fecha_compra', $periodo])
        ->all();
    
    $total = 0;
    foreach ($ventas as $venta) {
        $total += (float)$venta->precio_total;
    }
    
    return $total;
}

/**
 * Obtiene las metas actuales (para sincronización AJAX)
 * @return array
 */
public function actionObtenerMetas()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    try {
        $periodo = date('Y-m');
        $metas = Metas::getMetasPeriodo($periodo);
        
        return [
            'success' => true,
            'data' => [
                'meta_unidades' => $metas['unidades'],
                'meta_dinero' => $metas['dinero'],
                'total_unidades' => $this->getTotalUnidadesMes($periodo),
                'total_dinero' => $this->getTotalDineroMes($periodo),
                'porcentaje_unidades' => $this->calcularPorcentaje($this->getTotalUnidadesMes($periodo), $metas['unidades']),
                'porcentaje_dinero' => $this->calcularPorcentaje($this->getTotalDineroMes($periodo), $metas['dinero']),
                'periodo' => $periodo
            ]
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al obtener las metas'
        ];
    }
}

/**
 * Calcula porcentaje de cumplimiento
 * @param float $actual
 * @param float $meta
 * @return float
 */
private function calcularPorcentaje($actual, $meta)
{
    return $meta == 0 ? 0 : round(($actual / $meta) * 100, 2);
}

// Tu método existente de exportExcel sin cambios
public function actionExportExcel()
{
    $ventas = Ventas::find()->all();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Obtener mes actual para título
    $mes = date('F Y'); // Ejemplo: September 2025
    $sheet->setCellValue('A1', "Ventas del mes: $mes");

    // Combinar primera fila para el título (16 columnas de la A a P)
    $sheet->mergeCells('A1:P1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Encabezados (fila 2)
    $headers = [
        'ID','Tipo Letrero','Nombre Letrero','Entrega','Adicionales','Extras',
        'Medio','Teléfono','Campaña','Asesor','Unidades',
        'Precio Extra','Precio Total','Anticipo','Restante','Fecha de Compra'
    ];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col.'2', $header);
        $sheet->getStyle($col.'2')->getFont()->setBold(true);
        $col++;
    }

    $row = 3; // Datos empiezan en la fila 3
    foreach ($ventas as $venta) {
        $sheet->setCellValue('A'.$row, $venta->id);

        $getBadgeColor = function($nombre) {
            if (!$nombre) return ['bg'=>'CCCCCC','text'=>'000000'];
            $hash = substr(md5($nombre),0,6);
            return ['bg'=>$hash,'text'=>'FFFFFF'];
        };

        // Tipo Letrero
        $tipo = $venta->tipoLetrero->nombre ?? '';
        $color = $getBadgeColor($tipo);
        $sheet->setCellValue('B'.$row, $tipo);
        $sheet->getStyle('B'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('B'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Nombre Letrero
        $sheet->setCellValue('C'.$row, $venta->nombre_letrero);

        // Entrega
        $entrega = $venta->entrega->nombre ?? '';
        $color = $getBadgeColor($entrega);
        $sheet->setCellValue('D'.$row, $entrega);
        $sheet->getStyle('D'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('D'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Adicionales
        $adicionales = array_map(fn($a) => $a->nombre, $venta->adicionales);
        $sheet->setCellValue('E'.$row, implode(', ',$adicionales));

        // Extras
        $extras = array_map(fn($e) => $e->nombre, $venta->extras);
        $sheet->setCellValue('F'.$row, implode(', ',$extras));

        // Medio
        $medio = $venta->medio->nombre ?? '';
        $color = $getBadgeColor($medio);
        $sheet->setCellValue('G'.$row, $medio);
        $sheet->getStyle('G'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('G'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Teléfono
        $sheet->setCellValue('H'.$row, $venta->telefono);

        // Campaña
        $campaña = $venta->campaña->nombre ?? '';
        $color = $getBadgeColor($campaña);
        $sheet->setCellValue('I'.$row, $campaña);
        $sheet->getStyle('I'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('I'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Asesor
        $asesor = $venta->asesor->nombre ?? '';
        $color = $getBadgeColor($asesor);
        $sheet->setCellValue('J'.$row, $asesor);
        $sheet->getStyle('J'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color['bg']);
        $sheet->getStyle('J'.$row)->getFont()->getColor()->setRGB($color['text']);

        // Unidades
        $sheet->setCellValue('K'.$row, $venta->unidades);

        // Precio Extra
        $sheet->setCellValue('L'.$row, $venta->extra_precio ?? 0);
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');

        // Precio Total
        $sheet->setCellValue('M'.$row, $venta->precio_total ?? 0);
        $sheet->getStyle('M'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('M'.$row)->getFont()->getColor()->setRGB('008000'); // verde

        // Anticipo
        $sheet->setCellValue('N'.$row, $venta->anticipo ?? 0);
        $sheet->getStyle('N'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('N'.$row)->getFont()->getColor()->setRGB('FFA500'); 


        // Restante
        $sheet->setCellValue('O'.$row, $venta->restante ?? 0);
        $sheet->getStyle('O'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('O'.$row)->getFont()->getColor()->setRGB('FF0000'); // rojo

        // Fecha de compra
        $fecha = $venta->fecha_compra ? Yii::$app->formatter->asDate($venta->fecha_compra,'php:d/m/Y') : '';
        $sheet->setCellValue('P'.$row, $fecha);

        $row++;
    }

    // Auto-ajustar columnas
    foreach(range('A','P') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);

    // Nombre del archivo con mes
    $fileName = "ventas_$mes.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

/**
 * Exporta todos los datos del dashboard a Excel
 * @return void
 */
public function actionExportDashboard()
{
    // Obtener los mismos datos que el dashboard
    $mesActual = date('Y-m');
    $ventasDelMes = Ventas::find()
        ->where(['like', 'fecha_compra', $mesActual])
        ->with(['tipoLetrero', 'asesor', 'medio', 'campaña', 'entrega'])
        ->all();

    // Calcular totales
    $totalUnidades = 0;
    $totalDinero = 0;
    $totalAnticipo = 0;
    $totalRestante = 0;

    foreach ($ventasDelMes as $venta) {
        $totalUnidades += (int)$venta->unidades;
        $totalDinero += (float)$venta->precio_total;
        $totalAnticipo += (float)$venta->anticipo;
        $totalRestante += (float)$venta->restante;
    }

    // Obtener metas
    Metas::crearMetasPorDefecto($mesActual);
    $metaUnidades = Metas::getMetaUnidades($mesActual);
    $metaDinero = Metas::getMetaDinero($mesActual);

    // Calcular porcentajes
    $porcentajeUnidades = $metaUnidades > 0 ? round(($totalUnidades / $metaUnidades) * 100, 2) : 0;
    $porcentajeDinero = $metaDinero > 0 ? round(($totalDinero / $metaDinero) * 100, 2) : 0;

    // Obtener rankings (igual que en dashboard)
    $conteoProductos = [];
    $conteoAsesores = [];
    $conteoMedios = [];

    foreach ($ventasDelMes as $venta) {
        // Productos
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
            $conteoProductos[$tipoId]['total_ventas'] += (float)$venta->precio_total;
        }
        
        // Asesores
        if ($venta->asesor) {
            $asesorId = $venta->asesor_id;
            $nombreAsesor = $venta->asesor->nombre;
            
            if (!isset($conteoAsesores[$asesorId])) {
                $conteoAsesores[$asesorId] = [
                    'id' => $asesorId,
                    'nombre' => $nombreAsesor,
                    'cantidad' => 0,
                    'total_ventas' => 0
                ];
            }
            
            $conteoAsesores[$asesorId]['cantidad']++;
            $conteoAsesores[$asesorId]['total_ventas'] += (float)$venta->precio_total;
        }
        
        // Medios
        if ($venta->medio) {
            $medioId = $venta->medio_id;
            $nombreMedio = $venta->medio->nombre;
            
            if (!isset($conteoMedios[$medioId])) {
                $conteoMedios[$medioId] = [
                    'id' => $medioId,
                    'nombre' => $nombreMedio,
                    'cantidad' => 0,
                    'total_ventas' => 0
                ];
            }
            
            $conteoMedios[$medioId]['cantidad']++;
            $conteoMedios[$medioId]['total_ventas'] += (float)$venta->precio_total;
        }
    }

    // Ordenar por rendimiento
    uasort($conteoProductos, function($a, $b) {
        return $b['cantidad'] - $a['cantidad'];
    });

    uasort($conteoAsesores, function($a, $b) {
        return $b['total_ventas'] - $a['total_ventas'];
    });

    uasort($conteoMedios, function($a, $b) {
        return $b['total_ventas'] - $a['total_ventas'];
    });

    // Calcular productos con potencial
    $productosPotencial = [];
    foreach ($conteoProductos as $producto) {
        if ($producto['cantidad'] >= 2) {
            $promedioVenta = $producto['total_ventas'] / $producto['cantidad'];
            $productosPotencial[] = array_merge($producto, [
                'promedio_precio' => $promedioVenta
            ]);
        }
    }

    usort($productosPotencial, function($a, $b) {
        return $b['promedio_precio'] - $a['promedio_precio'];
    });

    // Crear el archivo Excel
    $spreadsheet = new Spreadsheet();
    
    // =================== HOJA 1: RESUMEN EJECUTIVO ===================
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resumen Ejecutivo');
    
    // Título principal
    $sheet->setCellValue('A1', "DASHBOARD EJECUTIVO - " . strtoupper(date('F Y')));
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
    $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
    
    // KPIs principales
    $sheet->setCellValue('A3', 'INDICADORES CLAVE DE RENDIMIENTO (KPI)');
    $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E6E6');
    
    $sheet->setCellValue('A4', 'Métrica');
    $sheet->setCellValue('B4', 'Meta');
    $sheet->setCellValue('C4', 'Actual');
    $sheet->setCellValue('D4', 'Cumplimiento');
    $sheet->setCellValue('E4', 'Estado');
    $sheet->getStyle('A4:E4')->getFont()->setBold(true);
    
    // KPI Unidades
    $sheet->setCellValue('A5', 'Unidades Vendidas');
    $sheet->setCellValue('B5', number_format($metaUnidades));
    $sheet->setCellValue('C5', number_format($totalUnidades));
    $sheet->setCellValue('D5', $porcentajeUnidades . '%');
    $sheet->setCellValue('E5', $porcentajeUnidades >= 100 ? '✅ CUMPLIDO' : ($porcentajeUnidades >= 80 ? '⚠️ EN PROGRESO' : '❌ BAJO'));
    
    // KPI Dinero
    $sheet->setCellValue('A6', 'Ventas en Dinero');
    $sheet->setCellValue('B6', '$' . number_format($metaDinero, 2));
    $sheet->setCellValue('C6', '$' . number_format($totalDinero, 2));
    $sheet->setCellValue('D6', $porcentajeDinero . '%');
    $sheet->setCellValue('E6', $porcentajeDinero >= 100 ? '✅ CUMPLIDO' : ($porcentajeDinero >= 80 ? '⚠️ EN PROGRESO' : '❌ BAJO'));
    
    // Resumen adicional
    $sheet->setCellValue('A8', 'RESUMEN FINANCIERO');
    $sheet->getStyle('A8')->getFont()->setBold(true);
    $sheet->setCellValue('A9', 'Total Anticipos:');
    $sheet->setCellValue('B9', '$' . number_format($totalAnticipo, 2));
    $sheet->setCellValue('A10', 'Total Restante:');
    $sheet->setCellValue('B10', '$' . number_format($totalRestante, 2));
    $sheet->setCellValue('A11', 'Total Ventas:');
    $sheet->setCellValue('B11', '$' . number_format($totalDinero, 2));
    
    // =================== HOJA 2: RANKING DE ASESORES ===================
    $asesoresSheet = $spreadsheet->createSheet();
    $asesoresSheet->setTitle('Ranking Asesores');
    
    $asesoresSheet->setCellValue('A1', 'RANKING DE ASESORES DE VENTAS');
    $asesoresSheet->mergeCells('A1:F1');
    $asesoresSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $asesoresSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $asesoresSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('70AD47');
    $asesoresSheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
    
    // Headers
    $headers = ['Posición', 'Asesor', 'Ventas Realizadas', 'Total en Dinero', 'Promedio por Venta', 'Participación %'];
    $col = 'A';
    foreach ($headers as $header) {
        $asesoresSheet->setCellValue($col.'3', $header);
        $asesoresSheet->getStyle($col.'3')->getFont()->setBold(true);
        $col++;
    }
    
    $row = 4;
    $position = 1;
    $totalVentasEquipo = array_sum(array_column($conteoAsesores, 'total_ventas'));
    
    foreach ($conteoAsesores as $asesor) {
        $promedio = $asesor['total_ventas'] / $asesor['cantidad'];
        $participacion = ($asesor['total_ventas'] / $totalVentasEquipo) * 100;
        
        $asesoresSheet->setCellValue('A'.$row, $position);
        $asesoresSheet->setCellValue('B'.$row, $asesor['nombre']);
        $asesoresSheet->setCellValue('C'.$row, $asesor['cantidad']);
        $asesoresSheet->setCellValue('D'.$row, $asesor['total_ventas']);
        $asesoresSheet->setCellValue('E'.$row, $promedio);
        $asesoresSheet->setCellValue('F'.$row, round($participacion, 2) . '%');
        
        // Formato de dinero
        $asesoresSheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $asesoresSheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        // Color para los top 3
        if ($position <= 3) {
            $colors = ['FFD700', 'C0C0C0', 'CD7F32']; // Oro, Plata, Bronce
            $asesoresSheet->getStyle('A'.$row.':F'.$row)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colors[$position-1]);
        }
        
        $row++;
        $position++;
    }
    
    // =================== HOJA 3: PRODUCTOS MÁS VENDIDOS ===================
    $productosSheet = $spreadsheet->createSheet();
    $productosSheet->setTitle('Productos Vendidos');
    
    $productosSheet->setCellValue('A1', 'PRODUCTOS MÁS VENDIDOS');
    $productosSheet->mergeCells('A1:F1');
    $productosSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $productosSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $productosSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E67C73');
    $productosSheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
    
    // Headers
    $headers = ['Posición', 'Producto', 'Cantidad Vendida', 'Unidades Totales', 'Total en Dinero', 'Promedio por Venta'];
    $col = 'A';
    foreach ($headers as $header) {
        $productosSheet->setCellValue($col.'3', $header);
        $productosSheet->getStyle($col.'3')->getFont()->setBold(true);
        $col++;
    }
    
    $row = 4;
    $position = 1;
    foreach ($conteoProductos as $producto) {
        $promedio = $producto['total_ventas'] / $producto['cantidad'];
        
        $productosSheet->setCellValue('A'.$row, $position);
        $productosSheet->setCellValue('B'.$row, $producto['nombre']);
        $productosSheet->setCellValue('C'.$row, $producto['cantidad']);
        $productosSheet->setCellValue('D'.$row, $producto['total_unidades']);
        $productosSheet->setCellValue('E'.$row, $producto['total_ventas']);
        $productosSheet->setCellValue('F'.$row, $promedio);
        
        $productosSheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $productosSheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        $row++;
        $position++;
    }
    
    // =================== HOJA 4: PRODUCTOS CON POTENCIAL ===================
    $potencialSheet = $spreadsheet->createSheet();
    $potencialSheet->setTitle('Productos Potencial');
    
    $potencialSheet->setCellValue('A1', 'PRODUCTOS CON MAYOR POTENCIAL');
    $potencialSheet->mergeCells('A1:F1');
    $potencialSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $potencialSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $potencialSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('9FC5E8');
    $potencialSheet->getStyle('A1')->getFont()->getColor()->setRGB('000000');
    
    // Headers
    $headers = ['Posición', 'Producto', 'Ventas', 'Total Dinero', 'Promedio por Venta', 'Nivel de Potencial'];
    $col = 'A';
    foreach ($headers as $header) {
        $potencialSheet->setCellValue($col.'3', $header);
        $potencialSheet->getStyle($col.'3')->getFont()->setBold(true);
        $col++;
    }
    
    $row = 4;
    $position = 1;
    foreach ($productosPotencial as $producto) {
        $nivel = $producto['promedio_precio'] >= 10000 ? 'ALTO' : 
                ($producto['promedio_precio'] >= 5000 ? 'MEDIO' : 'BÁSICO');
        
        $potencialSheet->setCellValue('A'.$row, $position);
        $potencialSheet->setCellValue('B'.$row, $producto['nombre']);
        $potencialSheet->setCellValue('C'.$row, $producto['cantidad']);
        $potencialSheet->setCellValue('D'.$row, $producto['total_ventas']);
        $potencialSheet->setCellValue('E'.$row, $producto['promedio_precio']);
        $potencialSheet->setCellValue('F'.$row, $nivel);
        
        $potencialSheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        $potencialSheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        $row++;
        $position++;
    }
    
    // =================== HOJA 5: ANÁLISIS DE MEDIOS ===================
    $mediosSheet = $spreadsheet->createSheet();
    $mediosSheet->setTitle('Análisis Medios');
    
    $mediosSheet->setCellValue('A1', 'ANÁLISIS DE MEDIOS DE CONTACTO');
    $mediosSheet->mergeCells('A1:E1');
    $mediosSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $mediosSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $mediosSheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4A7D6');
    $mediosSheet->getStyle('A1')->getFont()->getColor()->setRGB('000000');
    
    // Headers
    $headers = ['Posición', 'Medio', 'Contactos', 'Total Ventas', 'Efectividad'];
    $col = 'A';
    foreach ($headers as $header) {
        $mediosSheet->setCellValue($col.'3', $header);
        $mediosSheet->getStyle($col.'3')->getFont()->setBold(true);
        $col++;
    }
    
    $row = 4;
    $position = 1;
    $totalContactos = array_sum(array_column($conteoMedios, 'cantidad'));
    
    foreach ($conteoMedios as $medio) {
        $efectividad = ($medio['cantidad'] / $totalContactos) * 100;
        
        $mediosSheet->setCellValue('A'.$row, $position);
        $mediosSheet->setCellValue('B'.$row, $medio['nombre']);
        $mediosSheet->setCellValue('C'.$row, $medio['cantidad']);
        $mediosSheet->setCellValue('D'.$row, $medio['total_ventas']);
        $mediosSheet->setCellValue('E'.$row, round($efectividad, 2) . '%');
        
        $mediosSheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        $row++;
        $position++;
    }
    
    // Auto-ajustar columnas en todas las hojas
    foreach ($spreadsheet->getAllSheets() as $worksheet) {
        foreach(range('A','F') as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
    
    // Generar archivo
    $writer = new Xlsx($spreadsheet);
    $fileName = "Dashboard_" . date('Y-m') . "_" . date('d-m-Y_H-i') . ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit;
}
}