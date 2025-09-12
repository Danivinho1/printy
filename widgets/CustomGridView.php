<?php

namespace app\widgets;

use yii\grid\GridView;
use yii\helpers\Html;

/**
 * CustomGridView - Widget personalizado para tablas con estilo uniforme
 * 
 * Uso:
 * echo CustomGridView::widget([
 *     'dataProvider' => $dataProvider,
 *     'columns' => [...],
 * ]);
 */
class CustomGridView extends GridView
{
    /**
     * @var string template para el layout del grid
     */
    public $layout = '<div class="table-header"></div><div class="custom-table-container">{items}</div><div class="table-footer">{pager}</div>';
    
    /**
     * @var array opciones HTML para la tabla
     */
    public $tableOptions = ['class' => 'table'];
    
    /**
     * @var array opciones para las filas del header
     */
    public $headerRowOptions = ['style' => 'border: none;'];
    
    /**
     * @var bool desactivar el ordenamiento por defecto
     */
    public $enableSorting = false;
    
    /**
     * @var array opciones para el summary
     */
    public $summaryOptions = ['class' => 'summary'];
    
    /**
     * @var array opciones para el pager
     */
    public $pager = [
        'class' => 'yii\widgets\LinkPager',
        'options' => ['class' => 'pagination justify-content-center'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
    ];

    /**
     * Inicializa el widget
     */
    public function init()
    {
        parent::init();
        
        // Registrar CSS automáticamente
        $this->view->registerCssFile('@web/css/table-styles.css', [
            'depends' => [\yii\web\YiiAsset::class],
        ]);
        
        // Agregar clase CSS al contenedor
        Html::addCssClass($this->options, 'custom-grid-view');
    }
    
    /**
     * Método helper para crear badges de estado
     */
    public static function createStatusBadge($status, $type = 'normal')
    {
        $class = 'status-badge ' . $type;
        return Html::tag('span', $status, ['class' => $class]);
    }
    
    /**
     * Método helper para crear información con iconos
     */
    public static function createInfoWithIcon($info, $iconType)
    {
        $class = 'info-with-icon ' . $iconType;
        return Html::tag('span', $info, ['class' => $class]);
    }
    
    /**
     * Método helper para crear códigos/IDs estilizados
     */
    public static function createCodeId($code, $prefix = '', $padding = 3)
    {
        if ($prefix && is_numeric($code)) {
            $formattedCode = $prefix . str_pad($code, $padding, '0', STR_PAD_LEFT);
        } else {
            $formattedCode = $code;
        }
        
        return Html::tag('span', $formattedCode, ['class' => 'codigo-id']);
    }
    
    /**
     * Método helper para crear contadores numéricos
     */
    public static function createCounter($number)
    {
        return Html::tag('span', $number, ['class' => 'numero-contador']);
    }
    
    /**
     * Método helper para crear kit badges
     */
    public static function createKitBadge($kitNumber, $type = 1)
    {
        $class = 'kit-badge';
        if ($type > 1) {
            $class .= ' tipo-' . $type;
        }
        
        return Html::tag('span', $kitNumber . ' Kit', ['class' => $class]);
    }
}