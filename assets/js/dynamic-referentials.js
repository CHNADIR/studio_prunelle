import NotificationService from './services/notification-service';

/**
 * Gestion des référentiels dynamiques (TypePrise, TypeVente, Theme)
 * 
 * Ce script permet d'ajouter un nouveau référentiel sans quitter le formulaire
 * en utilisant une modal et AJAX.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les selects avec l'attribut data-add-url
    const selectsWithAdd = document.querySelectorAll('select.select-with-add');
    
    selectsWithAdd.forEach(select => {
        const addUrl = select.dataset.addUrl;
        if (!addUrl) return;
        
        // Créer le bouton + à côté du select
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex align-items-center';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn btn-sm btn-outline-primary ms-2';
        addButton.innerHTML = '<i class="bi bi-plus-circle"></i>';
        addButton.title = 'Ajouter un nouvel élément';
        wrapper.appendChild(addButton);
        
        // Événement de clic sur le bouton
        addButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Créer et afficher la modal
            const modal = createModal(select.id);
            document.body.appendChild(modal);
            
            // Ajouter une classe pour l'animation d'entrée
            setTimeout(() => {
                modal.querySelector('.modal-dialog').classList.add('animate__animated', 'animate__fadeInDown');
            }, 50);
            
            // Charger le contenu de la modal via AJAX
            fetch(addUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = html;
                    
                    // Initialiser le formulaire dans la modal
                    const form = modalBody.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            // Afficher un indicateur de chargement
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalBtnText = submitBtn.innerHTML;
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';
                            
                            // Nettoyer les messages d'erreur précédents
                            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                            form.querySelectorAll('.alert').forEach(el => el.remove());
                            
                            // Envoyer le formulaire via AJAX
                            const formData = new FormData(form);
                            
                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(data => {
                                        throw data;
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Ajouter la nouvelle option au select et la sélectionner
                                    const option = new Option(data.text, data.id, true, true);
                                    select.add(option);
                                    
                                    // Afficher une notification de succès
                                    NotificationService.showSuccess(data.message || 'Élément ajouté avec succès');
                                    
                                    // Fermer la modal avec animation
                                    modal.querySelector('.modal-dialog').classList.remove('animate__fadeInDown');
                                    modal.querySelector('.modal-dialog').classList.add('animate__fadeOutUp');
                                    
                                    setTimeout(() => {
                                        const modalInstance = bootstrap.Modal.getInstance(modal);
                                        modalInstance.hide();
                                    }, 300);
                                    
                                    // Supprimer la modal après fermeture
                                    modal.addEventListener('hidden.bs.modal', function() {
                                        modal.remove();
                                    });
                                }
                            })
                            .catch(error => {
                                // Réactiver le bouton
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnText;
                                
                                // Afficher les erreurs avec le service de notification
                                if (error.errors && Array.isArray(error.errors)) {
                                    // Afficher les erreurs dans le formulaire
                                    error.errors.forEach(errorMsg => {
                                        const errorAlert = document.createElement('div');
                                        errorAlert.className = 'alert alert-danger mb-3';
                                        errorAlert.textContent = errorMsg;
                                        form.prepend(errorAlert);
                                    });
                                    
                                    // Afficher aussi une notification
                                    NotificationService.showError('Veuillez corriger les erreurs dans le formulaire');
                                } else {
                                    const errorMessage = 'Une erreur est survenue lors de l\'ajout.';
                                    
                                    const errorAlert = document.createElement('div');
                                    errorAlert.className = 'alert alert-danger mb-3';
                                    errorAlert.textContent = errorMessage;
                                    form.prepend(errorAlert);
                                    
                                    NotificationService.showError(errorMessage);
                                }
                            });
                        });
                    }
                    
                    // Initialiser et afficher la modal Bootstrap
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    
                    // Utiliser le service de notification pour une gestion centralisée des erreurs
                    NotificationService.showError('Erreur lors du chargement du formulaire', 5000);
                    
                    // Ajouter une animation de sortie (conforme au pattern UX mentionné)
                    modal.classList.add('animate__animated', 'animate__fadeOut');
                    setTimeout(() => {
                        modal.remove();
                    }, 300);
                });
        });
    });
    
    // Fonction pour créer une modal
    function createModal(targetId) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = `modal-${targetId}`;
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', `modal-${targetId}-label`);
        modal.setAttribute('aria-hidden', 'true');
        
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-${targetId}-label">Ajouter un nouvel élément</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }
});