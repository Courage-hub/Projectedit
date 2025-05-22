/**
 * FORVIA - Portal Principal
 * Script personalizado para el portal principal
 * Fecha: 16 de mayo de 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    // Animación de carga de la página
    document.body.classList.add('loaded');
    
    // Efecto de spotlight para elementos con la clase .spotlight
    const spotlightElements = document.querySelectorAll('.spotlight');
    
    spotlightElements.forEach(element => {
        element.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Actualizar la posición del efecto spotlight
            this.style.setProperty('--spotlight-x', `${x}px`);
            this.style.setProperty('--spotlight-y', `${y}px`);
        });
    });
    
    // Detección de scroll para animar elementos cuando entran en viewport
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    // Callback para la intersección
    const onIntersection = (entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Una vez que se ha mostrado, ya no necesitamos observarlo
                observer.unobserve(entry.target);
            }
        });
    };
    
    // Configurar el observador de intersección
    const observer = new IntersectionObserver(onIntersection, {
        root: null,       // viewport
        threshold: 0.1,   // elemento es visible cuando un 10% está en viewport
        rootMargin: '0px' // sin margen
    });
    
    // Observar todos los elementos con la clase animate-on-scroll
    animatedElements.forEach(el => {
        observer.observe(el);
    });
    
    // Cambio de modo oscuro/claro
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-mode');
            
            // Guardar preferencia en localStorage
            if (document.documentElement.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
            } else {
                localStorage.setItem('darkMode', 'disabled');
            }
        });
        
        // Verificar si el usuario tenía modo oscuro habilitado
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.classList.add('dark-mode');
        }
    }
    
    // Efecto hover mejorado para las tarjetas
    const versionCards = document.querySelectorAll('.version-card');
    versionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            versionCards.forEach(c => {
                if (c !== this) {
                    c.style.opacity = '0.7';
                    c.style.transform = 'scale(0.98)';
                }
            });
        });
        
        card.addEventListener('mouseleave', function() {
            versionCards.forEach(c => {
                c.style.opacity = '1';
                c.style.transform = '';
            });
        });
    });
      // Añadir la clase "animate-on-scroll" a elementos que aún no la tengan pero deberían animarse
    document.querySelectorAll('.version-card').forEach((card, index) => {
        if (!card.parentElement.classList.contains('animate-fadeInUp')) {
            card.parentElement.classList.add('animate-on-scroll');
            card.parentElement.style.transitionDelay = `${0.1 * index}s`;
        }
    });
    
    // Filtrado de versiones
    const filterButtons = document.querySelectorAll('[data-filter]');
    if (filterButtons.length > 0) {
        // Preparar las tarjetas con atributos de filtrado
        prepareCardFilters();
        
        // Agregar escuchadores de eventos a los botones de filtro
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Eliminar clase activa de todos los botones
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Añadir clase activa al botón pulsado
                this.classList.add('active');
                
                // Filtrar tarjetas según el filtro seleccionado
                const filterValue = this.getAttribute('data-filter');
                filterCards(filterValue);
            });
        });
    }
});

/**
 * Prepara las tarjetas de versión añadiendo atributos de filtrado
 */
function prepareCardFilters() {
    // Obtener todas las tarjetas de versión
    const cards = document.querySelectorAll('.version-card');
    
    cards.forEach(card => {
        const cardContainer = card.closest('.col-md-6');
        
        // Marcar la versión más reciente
        if (card.querySelector('.badge')) {
            cardContainer.setAttribute('data-latest', 'true');
        } else {
            cardContainer.setAttribute('data-latest', 'false');
        }
        
        // Marcar versiones con migración
        if (card.querySelector('a[href*="Migration"]')) {
            cardContainer.setAttribute('data-migration', 'true');
        } else {
            cardContainer.setAttribute('data-migration', 'false');
        }
    });
}

/**
 * Filtra las tarjetas según el criterio seleccionado
 * @param {string} filter - El valor del filtro a aplicar ('all', 'latest', 'migration')
 */
function filterCards(filter) {
    const cards = document.querySelectorAll('.version-card');
    
    cards.forEach(card => {
        const cardContainer = card.closest('.col-md-6');
        
        switch (filter) {
            case 'latest':
                if (cardContainer.getAttribute('data-latest') === 'true') {
                    showCard(cardContainer);
                } else {
                    hideCard(cardContainer);
                }
                break;
            case 'migration':
                if (cardContainer.getAttribute('data-migration') === 'true') {
                    showCard(cardContainer);
                } else {
                    hideCard(cardContainer);
                }
                break;
            default: // 'all'
                showCard(cardContainer);
                break;
        }
    });
}

/**
 * Muestra una tarjeta con animación
 * @param {HTMLElement} card - El elemento contenedor de la tarjeta
 */
function showCard(card) {
    card.style.display = 'block';
    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 50);
}

/**
 * Oculta una tarjeta con animación
 * @param {HTMLElement} card - El elemento contenedor de la tarjeta
 */
function hideCard(card) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    setTimeout(() => {
        card.style.display = 'none';
    }, 300);
}

// Función para añadir una clase después de un breve retraso (útil para animaciones secuenciales)
function addClassWithDelay(elements, className, delay = 100, startIndex = 0) {
    Array.from(elements).forEach((el, index) => {
        setTimeout(() => {
            el.classList.add(className);
        }, delay * (index + startIndex));
    });
}
