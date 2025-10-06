<?php
/* 
 * Archivo: views/site/_dashboard_controls.php  
 * Controles adicionales del navbar del dashboard
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div style="display: flex; align-items: center; gap: 0.5rem;">
    <!-- Selector de período -->
    <svg style="width: 1rem; height: 1rem; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
    </svg>
    
    <?= Html::dropDownList(
        'period',
        date('Y-m'),
        [
            date('Y-m') => date('F Y'),
            date('Y-m', strtotime('-1 month')) => date('F Y', strtotime('-1 month')),
            date('Y-m', strtotime('-2 months')) => date('F Y', strtotime('-2 months')),
            date('Y-m', strtotime('-3 months')) => date('F Y', strtotime('-3 months')),
            date('Y-m', strtotime('-4 months')) => date('F Y', strtotime('-4 months')),
            date('Y-m', strtotime('-5 months')) => date('F Y', strtotime('-5 months'))
        ],
        [
            'class' => 'search-input',
            'style' => 'width: auto; padding-left: 0.75rem; margin: 0;',
            'onchange' => 'location.href = "' . Url::current() . '?period=" + this.value'
        ]
    ) ?>
</div>