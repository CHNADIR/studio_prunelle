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
        const addUrl = select.getAttribute('data-add-url');
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
            
            // Charger le contenu de la modal via AJAX
            fetch(addUrl)
                .then(response => response.text())
                .then(html => {
                    const modalBody = modal.querySelector('.modal-body');
                    modalBody.innerHTML = html;
                    
                    // Initialiser le formulaire dans la modal
                    const form = modalBody.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            // Envoyer le formulaire via AJAX
                            const formData = new FormData(form);
                            
                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Ajouter la nouvelle option au select et la sélectionner
                                    const option = new Option(data.text, data.id, true, true);
                                    select.add(option);
                                    
                                    // Fermer la modal
                                    bootstrap.Modal.getInstance(modal).hide();
                                    modal.remove();
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de l\'ajout:', error);
                            });
                        });
                    }
                    
                    // Afficher la modal
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                });
        });
    });
    
    // Fonction pour créer une modal
    function createModal(targetId) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = `modal-${targetId}`;
        modal.tabIndex = -1;
        
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un nouvel élément</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p>Chargement...</p>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }
});