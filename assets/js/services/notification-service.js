/**
 * Service de gestion des notifications
 * Permet d'afficher des messages à l'utilisateur de manière standardisée
 */
export default class NotificationService {
    /**
     * Affiche une notification de succès
     * @param {string} message - Message à afficher
     * @param {number} duration - Durée d'affichage en ms (défaut: 3000ms)
     */
    static showSuccess(message, duration = 3000) {
        this._showNotification(message, 'success', duration);
    }

    /**
     * Affiche une notification d'erreur
     * @param {string} message - Message à afficher
     * @param {number} duration - Durée d'affichage en ms (défaut: 5000ms)
     */
    static showError(message, duration = 5000) {
        this._showNotification(message, 'danger', duration);
    }

    /**
     * Affiche une notification d'information
     * @param {string} message - Message à afficher
     * @param {number} duration - Durée d'affichage en ms (défaut: 3000ms)
     */
    static showInfo(message, duration = 3000) {
        this._showNotification(message, 'info', duration);
    }

    /**
     * Méthode privée pour créer et afficher une notification
     */
    static _showNotification(message, type, duration) {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `toast align-items-center text-white bg-${type} border-0`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1050';
        notification.style.minWidth = '250px';

        // Contenu de la notification
        notification.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        // Ajouter au corps du document
        document.body.appendChild(notification);

        // Initialiser et afficher avec animation
        const toast = new bootstrap.Toast(notification, {
            autohide: true,
            delay: duration
        });
        toast.show();

        // Supprimer après fermeture
        notification.addEventListener('hidden.bs.toast', () => {
            notification.remove();
        });
    }
}