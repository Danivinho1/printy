/**
 * Dashboard de Ventas - Funcionalidad JavaScript
 * Versión: 2.0
 * Autor: Sistema de Ventas
 */

class SalesDashboard {
    constructor() {
        this.config = {
            autoRefreshInterval: 300000, // 5 minutos
            animationDuration: 300,
            chartColors: [
                '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                '#6f42c1', '#20c997', '#fd7e14', '#6610f2'
            ],
            storageKeys: {
                lastUpdate: 'dashboard_last_update',
                userPreferences: 'dashboard_preferences'
            }
        };
        
        this.state = {
            isRefreshing: false,
            lastUpdateTime: null,
            userPreferences: this.loadUserPreferences()
        };
        
        this.intervals = {};
        this.init();
    }

    /**
     * Inicialización del dashboard
     */
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.startAutoRefresh();
        this.loadStoredData();
        this.animateOnLoad();
        console.log('Dashboard de Ventas iniciado correctamente');
    }

    /**
     * Vinculación de eventos
     */
    bindEvents() {
        // Botón de actualización
        $(document).on('click', '#refresh-dashboard', (e) => {
            e.preventDefault();
            this.refreshDashboard();
        });

        // Eventos de hover para tarjetas
        $('.kpi-card, .dashboard-card').hover(
            function() {
                $(this).addClass('shadow-hover');
            },
            function() {
                $(this).removeClass('shadow-hover');
            }
        );

        // Eventos de teclado para accesibilidad
        $(document).on('keydown', (e) => {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.refreshDashboard();
            }
        });

        // Evento para cambio de visibilidad de página
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkForUpdates();
            }
        });

        // Eventos de scroll para lazy loading
        $(window).on('scroll', this.throttle(() => {
            this.handleScroll();
        }, 100));

        // Eventos de redimensionamiento
        $(window).on('resize', this.debounce(() => {
            this.handleResize();
        }, 250));
    }

    /**
     * Inicialización de componentes
     */
    initializeComponents() {
        this.initializeTooltips();
        this.initializeProgressBars();
        this.initializeCounters();
        this.initializeCharts();
        this.setupKeyboardNavigation();
    }

    /**
     * Inicializar tooltips
     */
    initializeTooltips() {
        $('[data-toggle="tooltip"], [title]').tooltip({
            placement: 'top',
            trigger: 'hover focus',
            delay: { show: 500, hide: 100 }
        });
    }

    /**
     * Inicializar barras de progreso con animación
     */
    initializeProgressBars() {
        $('.progress-bar').each(function() {
            const $bar = $(this);
            const width = $bar.attr('style').match(/width:\s*(\d+\.?\d*)%/);
            
            if (width) {
                $bar.css('width', '0%');
                setTimeout(() => {
                    $bar.animate({ width: width[0] }, 1000, 'easeOutCubic');
                }, 200);
            }
        });
    }

    /**
     * Inicializar contadores animados
     */
    initializeCounters() {
        $('.metric-value, .kpi-value, .value-primary').each(function() {
            const $element = $(this);
            const text = $element.text();
            const number = text.replace(/[^\d.-]/g, '');
            
            if (number && !isNaN(number)) {
                $element.prop('Counter', 0).animate({
                    Counter: parseFloat(number)
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        if (text.includes('$')) {
                            $element.text('$' + this.formatNumber(now, 2));
                        } else if (text.includes('%')) {
                            $element.text(this.formatNumber(now, 1) + '%');
                        } else {
                            $element.text(this.formatNumber(now, 0));
                        }
                    }.bind(this)
                });
            }
        }.bind(this));
    }

    /**
     * Inicializar gráficos (si hay librerías de gráficos)
     */
    initializeCharts() {
        // Aquí se pueden inicializar gráficos con Chart.js, D3, etc.
        if (typeof Chart !== 'undefined') {
            this.createSalesChart();
            this.createProductChart();
        }
    }

    /**
     * Configurar navegación por teclado
     */
    setupKeyboardNavigation() {
        // Hacer elementos interactivos accesibles por teclado
        $('.ranking-item, .kpi-card, .metric-item').attr('tabindex', '0');
        
        // Manejar eventos de teclado
        $(document).on('keydown', '.ranking-item, .kpi-card, .metric-item', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                $(e.target).trigger('click');
            }
        });
    }

    /**
     * Actualizar dashboard
     */
    async refreshDashboard() {
        if (this.state.isRefreshing) return;
        
        this.state.isRefreshing = true;
        this.showRefreshNotification();
        
        try {
            // Simular llamada AJAX para obtener datos actualizados
            await this.fetchDashboardData();
            
            // Actualizar timestamp
            this.state.lastUpdateTime = new Date();
            localStorage.setItem(this.config.storageKeys.lastUpdate, this.state.lastUpdateTime.toISOString());
            
            // Actualizar UI
            this.updateLastUpdateTime();
            this.reinitializeComponents();
            
        } catch (error) {
            console.error('Error al actualizar dashboard:', error);
            this.showErrorNotification('Error al actualizar los datos');
        } finally {
            this.state.isRefreshing = false;
            setTimeout(() => {
                $('.refresh-notification').fadeOut();
            }, 2000);
        }
    }

    /**
     * Obtener datos del dashboard (simulado)
     */
    async fetchDashboardData() {
        return new Promise((resolve) => {
            // Simular delay de red
            setTimeout(() => {
                // Aquí iría la llamada real al servidor
                resolve({
                    success: true,
                    data: {
                        timestamp: new Date().toISOString()
                    }
                });
            }, 1500);
        });
    }

    /**
     * Mostrar notificación de actualización
     */
    showRefreshNotification() {
        const notification = $(`
            <div class="refresh-notification">
                <i class="fas fa-sync-alt fa-spin me-2"></i>
                Actualizando datos...
            </div>
        `);
        
        $('body').append(notification);
        notification.fadeIn(this.config.animationDuration);
    }

    /**
     * Mostrar notificación de error
     */
    showErrorNotification(message) {
        const notification = $(`
            <div class="refresh-notification" style="background: linear-gradient(135deg, #e74a3b, #dc3545);">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        notification.fadeIn(this.config.animationDuration);
        
        setTimeout(() => {
            notification.fadeOut();
        }, 5000);
    }

    /**
     * Actualizar tiempo de última actualización
     */
    updateLastUpdateTime() {
        const now = new Date();
        const timeString = now.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        $('.dashboard-subtitle').html(
            $('.dashboard-subtitle').html().replace(
                /Actualizado: .+$/,
                `Actualizado: ${timeString}`
            )
        );
    }

    /**
     * Iniciar actualización automática
     */
    startAutoRefresh() {
        this.intervals.autoRefresh = setInterval(() => {
            if (!document.hidden && !this.state.isRefreshing) {
                this.refreshDashboard();
            }
        }, this.config.autoRefreshInterval);
    }

    /**
     * Verificar actualizaciones
     */
    checkForUpdates() {
        const lastUpdate = localStorage.getItem(this.config.storageKeys.lastUpdate);
        if (lastUpdate) {
            const lastUpdateTime = new Date(lastUpdate);
            const now = new Date();
            const diffMinutes = (now - lastUpdateTime) / (1000 * 60);
            
            if (diffMinutes > 10) { // Si han pasado más de 10 minutos
                this.showUpdateAvailableNotification();
            }
        }
    }

    /**
     * Mostrar notificación de actualización disponible
     */
    showUpdateAvailableNotification() {
        const notification = $(`
            <div class="refresh-notification" style="background: linear-gradient(135deg, #f6c23e, #fd7e14); cursor: pointer;" data-action="refresh">
                <i class="fas fa-info-circle me-2"></i>
                Hay datos más recientes disponibles. Clic para actualizar.
            </div>
        `);
        
        notification.on('click', () => {
            notification.remove();
            this.refreshDashboard();
        });
        
        $('body').append(notification);
        notification.fadeIn();
    }

    /**
     * Cargar datos almacenados
     */
    loadStoredData() {
        const lastUpdate = localStorage.getItem(this.config.storageKeys.lastUpdate);
        if (lastUpdate) {
            this.state.lastUpdateTime = new Date(lastUpdate);
        }
    }

    /**
     * Cargar preferencias de usuario
     */
    loadUserPreferences() {
        const stored = localStorage.getItem(this.config.storageKeys.userPreferences);
        return stored ? JSON.parse(stored) : {
            theme: 'light',
            autoRefresh: true,
            animations: true
        };
    }

    /**
     * Guardar preferencias de usuario
     */
    saveUserPreferences() {
        localStorage.setItem(
            this.config.storageKeys.userPreferences, 
            JSON.stringify(this.state.userPreferences)
        );
    }

    /**
     * Animar elementos al cargar
     */
    animateOnLoad() {
        // Animar tarjetas con delay escalonado
        $('.kpi-card, .dashboard-card').each(function(index) {
            $(this).css({
                opacity: '0',
                transform: 'translateY(20px)'
            }).delay(index * 100).animate({
                opacity: 1
            }, {
                duration: this.config.animationDuration,
                complete: function() {
                    $(this).css('transform', 'translateY(0)');
                }
            });
        }.bind(this));

        // Animar elementos de ranking
        $('.ranking-item').each(function(index) {
            $(this).css({
                opacity: '0',
                transform: 'translateX(-20px)'
            }).delay(200 + (index * 50)).animate({
                opacity: 1
            }, {
                duration: this.config.animationDuration,
                complete: function() {
                    $(this).css('transform', 'translateX(0)');
                }
            });
        });
    }

    /**
     * Reinicializar componentes después de actualización
     */
    reinitializeComponents() {
        this.initializeProgressBars();
        this.initializeCounters();
        this.initializeTooltips();
    }

    /**
     * Manejar scroll de página
     */
    handleScroll() {
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        
        // Lazy loading para elementos fuera de vista
        $('.dashboard-card:not(.loaded)').each(function() {
            const elementTop = $(this).offset().top;
            if (elementTop < scrollTop + windowHeight + 100) {
                $(this).addClass('loaded fade-in');
            }
        });
    }

    /**
     * Manejar redimensionamiento de ventana
     */
    handleResize() {
        // Reajustar gráficos si existen
        if (typeof Chart !== 'undefined') {
            Chart.helpers.each(Chart.instances, (instance) => {
                instance.resize();
            });
        }
        
        // Reajustar tooltips
        $('[data-toggle="tooltip"]').tooltip('hide');
    }

    /**
     * Crear gráfico de ventas
     */
    createSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ventas',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: this.config.chartColors[0],
                    backgroundColor: this.config.chartColors[0] + '20',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Crear gráfico de productos
     */
    createProductChart() {
        const ctx = document.getElementById('productChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Producto A', 'Producto B', 'Producto C'],
                datasets: [{
                    data: [30, 25, 45],
                    backgroundColor: this.config.chartColors.slice(0, 3)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Formatear números
     */
    formatNumber(num, decimals = 0) {
        return parseFloat(num).toLocaleString('es-ES', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    /**
     * Utilidad: Throttle
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Utilidad: Debounce
     */
    debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    /**
     * Limpiar recursos al destruir
     */
    destroy() {
        // Limpiar intervalos
        Object.values(this.intervals).forEach(interval => {
            clearInterval(interval);
        });
        
        // Remover event listeners
        $(document).off('click', '#refresh-dashboard');
        $(window).off('scroll resize');
        
        // Limpiar tooltips
        $('[data-toggle="tooltip"]').tooltip('dispose');
        
        console.log('Dashboard limpiado correctamente');
    }
}

// Extensión de jQuery para animaciones personalizadas
$.extend($.easing, {
    easeOutCubic: function (x, t, b, c, d) {
        return c*((t=t/d-1)*t*t + 1) + b;
    }
});

// Inicialización cuando el DOM está listo
$(document).ready(function() {
    // Crear instancia global del dashboard
    window.salesDashboard = new SalesDashboard();
    
    // Agregar clases de utilidad
    $('body').addClass('dashboard-ready');
    
    // Configurar eventos globales
    $(window).on('beforeunload', function() {
        if (window.salesDashboard) {
            window.salesDashboard.destroy();
        }
    });
    
    // Agregar soporte para PWA (si aplica)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(console.error);
    }
    
    console.log('Dashboard JavaScript cargado completamente');
});