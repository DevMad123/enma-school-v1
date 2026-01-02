/**
 * Utilitaires de sécurité côté client
 */

// Protection CSRF automatique pour tous les formulaires
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter automatiquement le token CSRF aux formulaires
    const forms = document.querySelectorAll('form[method="POST"], form[method="PUT"], form[method="DELETE"], form[method="PATCH"]');
    forms.forEach(form => {
        if (!form.querySelector('input[name="_token"]')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfToken.content;
                form.appendChild(tokenInput);
            }
        }
    });
});

// Protection contre les soumissions multiples
class FormSubmissionProtection {
    constructor() {
        this.submittedForms = new Set();
        this.init();
    }

    init() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const formId = this.getFormId(form);
            
            if (this.submittedForms.has(formId)) {
                e.preventDefault();
                this.showWarning('Cette opération est déjà en cours...');
                return false;
            }
            
            this.submittedForms.add(formId);
            this.disableForm(form);
            
            // Réactiver le formulaire après 5 secondes en cas de problème
            setTimeout(() => {
                this.enableForm(form);
                this.submittedForms.delete(formId);
            }, 5000);
        });
    }

    getFormId(form) {
        return form.id || form.action + '_' + form.method;
    }

    disableForm(form) {
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(button => {
            button.disabled = true;
            button.dataset.originalText = button.textContent || button.value;
            if (button.textContent) {
                button.textContent = 'En cours...';
            } else {
                button.value = 'En cours...';
            }
        });
    }

    enableForm(form) {
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitButtons.forEach(button => {
            button.disabled = false;
            if (button.dataset.originalText) {
                if (button.textContent) {
                    button.textContent = button.dataset.originalText;
                } else {
                    button.value = button.dataset.originalText;
                }
            }
        });
    }

    showWarning(message) {
        if (window.toastr) {
            window.toastr.warning(message);
        } else if (window.Swal) {
            window.Swal.fire('Attention', message, 'warning');
        } else {
            alert(message);
        }
    }
}

// Validation côté client pour les mots de passe
class PasswordValidation {
    constructor() {
        this.init();
    }

    init() {
        const passwordInputs = document.querySelectorAll('input[type="password"][data-validate="true"]');
        passwordInputs.forEach(input => {
            input.addEventListener('input', (e) => this.validatePassword(e.target));
            input.addEventListener('blur', (e) => this.validatePassword(e.target));
        });
    }

    validatePassword(input) {
        const password = input.value;
        const requirements = [
            { regex: /.{8,}/, message: 'Au moins 8 caractères' },
            { regex: /[a-z]/, message: 'Une lettre minuscule' },
            { regex: /[A-Z]/, message: 'Une lettre majuscule' },
            { regex: /\d/, message: 'Un chiffre' },
            { regex: /[@$!%*?&]/, message: 'Un caractère spécial (@$!%*?&)' }
        ];

        const feedback = this.getOrCreateFeedback(input);
        const unmet = requirements.filter(req => !req.regex.test(password));
        
        if (password.length === 0) {
            feedback.style.display = 'none';
        } else if (unmet.length === 0) {
            feedback.innerHTML = '<span style="color: green;">✓ Mot de passe valide</span>';
            feedback.style.display = 'block';
        } else {
            feedback.innerHTML = '<div style="color: red;">Manquant:<ul>' + 
                unmet.map(req => `<li>${req.message}</li>`).join('') + 
                '</ul></div>';
            feedback.style.display = 'block';
        }
    }

    getOrCreateFeedback(input) {
        let feedback = input.parentNode.querySelector('.password-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'password-feedback text-sm mt-1';
            input.parentNode.appendChild(feedback);
        }
        return feedback;
    }
}

// Limitation de tentatives côté client
class ClientSideRateLimit {
    constructor() {
        this.attempts = new Map();
        this.init();
    }

    init() {
        // Surveiller les formulaires sensibles
        const sensitiveSelectors = [
            'form[action*="login"]',
            'form[action*="password"]',
            'form[action*="reset"]'
        ];
        
        sensitiveSelectors.forEach(selector => {
            const forms = document.querySelectorAll(selector);
            forms.forEach(form => {
                form.addEventListener('submit', (e) => this.checkRateLimit(e));
            });
        });
    }

    checkRateLimit(event) {
        const form = event.target;
        const key = form.action + '_' + (this.getUserIdentifier() || 'anonymous');
        const now = Date.now();
        const limit = 5; // 5 tentatives
        const window = 5 * 60 * 1000; // 5 minutes

        if (!this.attempts.has(key)) {
            this.attempts.set(key, []);
        }

        const attempts = this.attempts.get(key);
        // Nettoyer les anciennes tentatives
        const recentAttempts = attempts.filter(time => now - time < window);
        
        if (recentAttempts.length >= limit) {
            event.preventDefault();
            this.showRateLimitMessage();
            return false;
        }

        recentAttempts.push(now);
        this.attempts.set(key, recentAttempts);
    }

    getUserIdentifier() {
        const userMeta = document.querySelector('meta[name="user-id"]');
        return userMeta ? userMeta.content : null;
    }

    showRateLimitMessage() {
        const message = 'Trop de tentatives. Veuillez patienter 5 minutes avant de réessayer.';
        if (window.toastr) {
            window.toastr.error(message);
        } else {
            alert(message);
        }
    }
}

// Initialisation automatique
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

function init() {
    new FormSubmissionProtection();
    new PasswordValidation();
    new ClientSideRateLimit();
}

// Exporter pour utilisation dans d'autres scripts
window.SecurityUtils = {
    FormSubmissionProtection,
    PasswordValidation,
    ClientSideRateLimit
};