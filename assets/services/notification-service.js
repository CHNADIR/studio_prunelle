/**
 * Service de notification centralisé
 * Implémente le pattern Service Layer pour la gestion des notifications utilisateur
 */
export class NotificationService {
    /**
     * Affiche un message de succès
     */
    static showSuccess(message, duration = 3000) {
        this.showNotification(message, 'success', duration);
    }
    
    /**
     * Affiche un message d'erreur
     */
    static showError(message, duration = 5000) {
        this.showNotification(message, 'danger', duration);
    }
    
    /**
     * Affiche un message d'avertissement
     */
    static showWarning(message, duration = 4000) {
        this.showNotification(message, 'warning', duration);
    }
    
    /**
     * Affiche un message d'information
     */
    static showInfo(message, duration = 3000) {
        this.showNotification(message, 'info', duration);
    }
    
    /**
     * Méthode interne pour afficher une notification avec le type spécifié
     */
    static showNotification(message, type = 'info', duration = 3000) {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `toast align-items-center text-white bg-${type} border-0 animate__animated animate__fadeInDown`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        
        notification.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Créer le conteneur s'il n'existe pas déjà
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
        }
        
        // Ajouter la notification au conteneur
        container.appendChild(notification);
        
        // Afficher la notification avec Bootstrap
        const toast = new bootstrap.Toast(notification, {
            animation: true,
            autohide: true,
            delay: duration
        });
        toast.show();
        
        // Supprimer la notification après disparition
        notification.addEventListener('hidden.bs.toast', () => {
            notification.remove();
        });
    }
}