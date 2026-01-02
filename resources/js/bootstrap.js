import axios from 'axios';
window.axios = axios;

// Configuration des en-têtes par défaut
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Protection CSRF automatique
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn('Token CSRF non trouvé. Assurez-vous d\'inclure la directive @csrf dans vos vues.');
}

// Intercepteur pour gérer les erreurs de sécurité
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            // Token CSRF expiré
            const message = 'Votre session a expiré. Veuillez actualiser la page.';
            if (window.toastr) {
                window.toastr.error(message);
            } else {
                alert(message);
            }
            setTimeout(() => window.location.reload(), 2000);
        } else if (error.response?.status === 429) {
            // Rate limit dépassé
            const message = error.response.data.message || 'Trop de requêtes. Veuillez patienter.';
            if (window.toastr) {
                window.toastr.warning(message);
            } else {
                alert(message);
            }
        } else if (error.response?.status === 422) {
            // Erreurs de validation
            if (error.response.data.errors && window.showValidationErrors) {
                window.showValidationErrors(error.response.data.errors);
            }
        }
        return Promise.reject(error);
    }
);

// Configuration globale pour les requêtes fetch
const originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    // Ajouter automatiquement le token CSRF
    if (!options.headers) {
        options.headers = {};
    }
    
    if (token && !options.headers['X-CSRF-TOKEN']) {
        options.headers['X-CSRF-TOKEN'] = token.content;
    }
    
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    
    return originalFetch(url, options)
        .then(response => {
            if (response.status === 419) {
                const message = 'Votre session a expiré. Veuillez actualiser la page.';
                if (window.toastr) {
                    window.toastr.error(message);
                } else {
                    alert(message);
                }
                setTimeout(() => window.location.reload(), 2000);
                throw new Error('CSRF Token expired');
            } else if (response.status === 429) {
                const message = 'Trop de requêtes. Veuillez patienter.';
                if (window.toastr) {
                    window.toastr.warning(message);
                } else {
                    alert(message);
                }
                throw new Error('Rate limit exceeded');
            }
            return response;
        });
};

// Fonction utilitaire pour afficher les erreurs de validation
window.showValidationErrors = function(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            // Supprimer les erreurs existantes
            const existingError = input.parentNode.querySelector('.text-red-600');
            if (existingError) {
                existingError.remove();
            }
            
            // Ajouter la nouvelle erreur
            const errorElement = document.createElement('div');
            errorElement.className = 'text-red-600 text-sm mt-1';
            errorElement.textContent = errors[field][0];
            input.parentNode.appendChild(errorElement);
            
            // Ajouter une classe d'erreur au champ
            input.classList.add('border-red-500');
            
            // Supprimer l'erreur lors de la saisie
            input.addEventListener('input', function() {
                errorElement.remove();
                input.classList.remove('border-red-500');
            }, { once: true });
        }
    });
};

// Protection contre les attaques XSS dans les formulaires dynamiques
window.sanitizeInput = function(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
};
