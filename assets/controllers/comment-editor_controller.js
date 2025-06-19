import { Controller } from '@hotwired/stimulus';
import { NotificationService } from '../services/notification-service';

export default class extends Controller {
    static targets = ['display', 'form', 'loading', 'content'];
    
    connect() {
        // Configuration initiale
    }
    
    edit() {
        this.displayTarget.classList.add('animate__animated', 'animate__fadeOut');
        setTimeout(() => {
            this.displayTarget.style.display = 'none';
            this.formTarget.style.display = 'block';
            this.formTarget.classList.add('animate__animated', 'animate__fadeIn');
            this.contentTarget.focus();
        }, 300);
    }
    
    cancel() {
        this.formTarget.classList.add('animate__animated', 'animate__fadeOut');
        setTimeout(() => {
            this.formTarget.style.display = 'none';
            this.displayTarget.style.display = 'block';
            this.displayTarget.classList.remove('animate__fadeOut');
            this.displayTarget.classList.add('animate__animated', 'animate__fadeIn');
        }, 300);
    }
    
    async save(event) {
        event.preventDefault();
        
        // Afficher l'indicateur de chargement avec animation
        this.formTarget.style.display = 'none';
        this.loadingTarget.style.display = 'block';
        this.loadingTarget.classList.add('animate__animated', 'animate__fadeIn');
        
        const form = event.currentTarget;
        const url = form.action;
        const formData = new FormData(form);
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mettre à jour l'affichage avec le nouveau commentaire
                this.updateDisplayContent(data.commentaire);
                
                // Afficher une notification de succès
                NotificationService.showSuccess(data.message);
            } else {
                // Afficher les erreurs
                NotificationService.showError(data.message || 'Une erreur est survenue');
                console.error(data.errors);
            }
        } catch (error) {
            console.error('Erreur:', error);
            NotificationService.showError('Une erreur inattendue est survenue');
        } finally {
            // Masquer le chargement et réafficher l'affichage
            this.loadingTarget.classList.add('animate__fadeOut');
            setTimeout(() => {
                this.loadingTarget.style.display = 'none';
                this.displayTarget.style.display = 'block';
                this.displayTarget.classList.add('animate__fadeIn');
            }, 300);
        }
    }
    
    updateDisplayContent(commentaire) {
        if (commentaire) {
            this.displayTarget.innerHTML = commentaire.replace(/\n/g, '<br>');
        } else {
            this.displayTarget.innerHTML = '<p class="text-muted">Aucun commentaire</p>';
        }
    }
}