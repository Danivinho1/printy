<?php

namespace app\controllers;

use Yii;
use app\models\Ventas;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\VentasAdicionales;
use app\models\VentasExtras;
use app\models\Catalogos;
use yii\web\Response;
use yii\helpers\html;

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
    // Crear el dataProvider para el GridView
    $dataProvider = new ActiveDataProvider([
        'query' => Ventas::find()->orderBy(['id' => SORT_DESC]),
        'pagination' => [
            'pageSize' => 20,
        ],
    ]);

    // Modelo para el modal (tu código existente)
    $modeloNuevo = new Ventas();
    if ($modeloNuevo->load(Yii::$app->request->post()) && $modeloNuevo->save()) {
        Yii::$app->session->setFlash('success', 'Venta guardada correctamente.');
        return $this->refresh();
    }

    return $this->render('index', [
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
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

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
                $countValid = \app\models\Catalogos::find()
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
    $class = $field === 'adicionales' ? \app\models\VentasAdicionales::class : \app\models\VentasExtras::class;
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
                return \app\models\Catalogos::find()
                    ->where(['id' => $id, 'tipo' => $tipoCatalogo])
                    ->exists();
            }
            
            // Si no es un campo de relación conocido, solo verificar que el ID existe
            return \app\models\Catalogos::find()->where(['id' => $id])->exists();
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
                return \yii\helpers\Html::encode($model->$field);
        }
    }

    /**
     * Obtiene las opciones para campos select de relaciones
     */
    public function actionGetSelectOptions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
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
                $opciones = \app\models\Catalogos::find()
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
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
                $options = \app\models\Catalogos::find()
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
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

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
            $relationClass = \app\models\VentasAdicionales::class;
            $relationField = 'adicional_id';
        } elseif ($field === 'extras') {
            $relationClass = \app\models\VentasExtras::class;
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
    $catalogos = \app\models\Catalogos::find()
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
        $items = \app\models\Catalogos::find()
            ->innerJoin('ventas_adicionales', 'catalogos.id = ventas_adicionales.adicional_id')
            ->where(['ventas_adicionales.venta_id' => $ventaId])
            ->all();
    } else {
        $items = \app\models\Catalogos::find()
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
                  \yii\helpers\Html::encode($item->nombre) . '</span>';
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
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

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
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $id = Yii::$app->request->post('id');

    $venta = \app\models\Ventas::findOne($id);
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


}
