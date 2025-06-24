import { Controller } from "@hotwired/stimulus"
import { Modal } from "bootstrap"

export default class extends Controller {
    static targets = [
        "selectContainer", 
        "originalSelect"
    ]
    
    static values = {
        type: String,           
        multiple: Boolean,      
        modalUrl: String,       
        apiUrl: String         
    }

    connect() {
        console.log('🎯 Stimulus Controller: referential-manager connecté', {
            type: this.typeValue,
            multiple: this.multipleValue,
            modalUrl: this.modalUrlValue,
            apiUrl: this.apiUrlValue
        });
        
        this.setupInterface()
        
        this.loadDataWithFallbackFirst()
    }

   
    setupInterface() {
        console.log('🔧 Setup interface pour', this.typeValue);
        
        if (this.hasOriginalSelectTarget) {
            console.log('📦 Select original trouvé, masquage...');
            this.originalSelectTarget.style.display = 'none'
        } else {
            console.log('❌ Select original non trouvé');
        }

        console.log('🎨 Création interface', this.multipleValue ? 'checkbox' : 'radio');
        this.createReferentialInterface()
        
        console.log('🔘 Création bouton ajout');
        this.createAddButton()
    }

    
    createReferentialInterface() {
        const container = document.createElement('div')
        container.className = `referential-interface ${this.typeValue}-interface`
        
        if (this.multipleValue) {
            container.className += ' checkbox-interface'
        } else {
            container.className += ' radio-interface'
        }

        if (this.hasSelectContainerTarget) {
            this.selectContainerTarget.appendChild(container)
        } else {
            this.originalSelectTarget.parentNode.insertBefore(container, this.originalSelectTarget.nextSibling)
        }
        
        this.interfaceContainer = container
    }

    
    createAddButton() {
        const button = document.createElement('button')
        button.type = 'button'
        button.className = 'btn btn-sm btn-outline-primary mt-2 add-referential-btn'
        button.innerHTML = '<i class="bi bi-plus-circle"></i> Ajouter'
        button.title = `Ajouter un nouveau ${this.getEntityLabel()}`
        
        button.addEventListener('click', this.openModal.bind(this))
        
        this.interfaceContainer.parentNode.insertBefore(button, this.interfaceContainer.nextSibling)
        
        this.addButton = button
    }

    
    async loadReferentialData() {
        try {
            console.log(`🔄 Chargement API pour ${this.typeValue}:`, this.apiUrlValue);
            const response = await fetch(this.apiUrlValue)
            
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
            
            const data = await response.json()
            console.log(`✅ Données API reçues pour ${this.typeValue}:`, data);
            this.populateInterface(data)
            
        } catch (error) {
            console.warn(`⚠️ Erreur API pour ${this.typeValue}, utilisation fallback:`, error.message);
            this.loadFallbackData()
        }
    }

    
    loadFallbackData() {
        console.log(`🔄 Chargement fallback pour ${this.typeValue}`);
        
        if (!this.hasOriginalSelectTarget) {
            console.error(`❌ Pas de select original pour ${this.typeValue}`);
            this.showError(`Impossible de charger les ${this.getEntityLabel()}s`)
            return
        }

        const data = []
        const options = this.originalSelectTarget.querySelectorAll('option')
        
        options.forEach(option => {
            if (option.value) { 
                data.push({
                    id: option.value,
                    libelle: option.textContent.trim(),
                    description: option.getAttribute('data-description') || null,
                    categorie: option.getAttribute('data-category') || null
                })
            }
        })

        console.log(`✅ Données fallback pour ${this.typeValue}:`, data);
        
        if (data.length > 0) {
            this.populateInterface(data)
        } else {
            console.warn(`⚠️ Aucune donnée disponible pour ${this.typeValue}`);
            this.showError(`Aucun ${this.getEntityLabel()} disponible`)
        }
    }

    
    populateInterface(data) {
        const container = this.interfaceContainer
        
        container.innerHTML = '' // Nettoyer

        const title = document.createElement('h6')
        title.className = 'referential-title mb-3'
        title.innerHTML = `<i class="bi bi-${this.getEntityIcon()}"></i> ${this.getEntityLabel()}s disponibles`
        container.appendChild(title)

        const optionsContainer = document.createElement('div')
        optionsContainer.className = 'options-container'
        container.appendChild(optionsContainer)

        data.forEach((item, index) => {
            const option = this.createOptionElement(item, index)
            optionsContainer.appendChild(option)
        })

        this.syncWithOriginalSelect()
    }

    
    createOptionElement(item, index) {
        const div = document.createElement('div')
        div.className = 'form-check referential-option'

        const inputType = this.multipleValue ? 'checkbox' : 'radio'
        const inputName = `${this.typeValue}_${this.element.id}`
        const inputId = `${inputName}_${index}`

        const input = document.createElement('input')
        input.type = inputType
        input.className = 'form-check-input'
        input.id = inputId
        input.name = inputName
        input.value = item.id
        input.addEventListener('change', this.handleSelectionChange.bind(this))

        const label = document.createElement('label')
        label.className = 'form-check-label'
        label.htmlFor = inputId
        label.innerHTML = `
            <strong>${item.libelle}</strong>
            ${item.description ? `<br><small class="text-muted">${item.description}</small>` : ''}
            ${item.categorie ? `<span class="badge bg-secondary ms-1">${item.categorie}</span>` : ''}
        `

        div.appendChild(input)
        div.appendChild(label)

        return div
    }

    
    handleSelectionChange(event) {
        const input = event.target
        const value = input.value

        if (this.multipleValue) {
            this.handleMultipleSelection(input, value)
        } else {
            this.handleSingleSelection(input, value)
        }

        this.dispatch('selectionChanged', {
            detail: {
                type: this.typeValue,
                value: value,
                selected: input.checked,
                multiple: this.multipleValue
            }
        })
    }

    
    handleSingleSelection(input, value) {
        this.originalSelectTarget.value = input.checked ? value : ''
        
        this.originalSelectTarget.dispatchEvent(new Event('change', { bubbles: true }))
    }

    
    handleMultipleSelection(input, value) {
        const selectedValues = this.getSelectedValues()
        
        this.updateOriginalSelectForMultiple(selectedValues)
    }

    
    getSelectedValues() {
        const checkboxes = this.interfaceContainer.querySelectorAll('input[type="checkbox"]:checked')
        return Array.from(checkboxes).map(cb => cb.value)
    }

    
    updateOriginalSelectForMultiple(selectedValues) {
        const primaryValue = selectedValues.length > 0 ? selectedValues[0] : ''
        this.originalSelectTarget.value = primaryValue
        this.originalSelectTarget.dispatchEvent(new Event('change', { bubbles: true }))
    }

    
    syncWithOriginalSelect() {
        const currentValue = this.originalSelectTarget.value
        if (!currentValue) return

        const input = this.element.querySelector(`input[value="${currentValue}"]`)
        if (input) {
            input.checked = true
        }
    }

    
    async openModal() {
        try {
            this.addButton.disabled = true
            this.addButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Chargement...'

            const modal = this.createModalElement()
            document.body.appendChild(modal)

            // Charger le contenu
            const response = await fetch(this.modalUrlValue)
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
            
            const html = await response.text()
            const modalBody = modal.querySelector('.modal-body')
            modalBody.innerHTML = html

            // Initialiser le formulaire
            this.initializeModalForm(modal)

            // Afficher la modal
            const modalInstance = new Modal(modal)
            modalInstance.show()

            // Nettoyer au closing
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove()
                this.resetAddButton()
            })

        } catch (error) {
            console.error('Erreur ouverture modal:', error)
            this.showError('Impossible d\'ouvrir le formulaire d\'ajout')
            this.resetAddButton()
        }
    }

    /**
     * Création de l'élément modal
     * Pattern: Factory Method
     */
    createModalElement() {
        const modal = document.createElement('div')
        modal.className = 'modal fade'
        modal.setAttribute('tabindex', '-1')
        modal.setAttribute('aria-hidden', 'true')
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle"></i>
                            Ajouter ${this.getEntityLabel()}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
        `
        
        return modal
    }

    /**
     * Initialisation du formulaire dans la modal
     * Pattern: Command Pattern
     */
    initializeModalForm(modal) {
        const form = modal.querySelector('form')
        if (!form) return

        form.addEventListener('submit', async (e) => {
            e.preventDefault()
            await this.handleModalFormSubmit(form, modal)
        })
    }

    /**
     * Gestion de la soumission du formulaire modal
     * Pattern: Command Pattern
     */
    async handleModalFormSubmit(form, modal) {
        try {
            const submitBtn = form.querySelector('button[type="submit"]')
            const originalBtnText = submitBtn.innerHTML
            
            // Indicateur de chargement
            submitBtn.disabled = true
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Création...'

            // Nettoyer les erreurs précédentes
            this.clearFormErrors(form)

            // Soumission AJAX
            const formData = new FormData(form)
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })

            const data = await response.json()

            if (data.success) {
                // Succès : fermer modal et mettre à jour interface
                this.handleSuccessfulCreation(data, modal)
            } else {
                // Erreurs : afficher dans le formulaire
                this.displayFormErrors(form, data.errors || ['Une erreur est survenue'])
                submitBtn.disabled = false
                submitBtn.innerHTML = originalBtnText
            }

        } catch (error) {
            console.error('Erreur soumission formulaire:', error)
            this.showError('Erreur lors de la création')
        }
    }

    /**
     * Gestion du succès de création
     * Pattern: Observer Pattern
     */
    handleSuccessfulCreation(data, modal) {
        // Fermer la modal
        const modalInstance = Modal.getInstance(modal)
        modalInstance.hide()

        // Ajouter la nouvelle option au select original
        const option = new Option(data.text || data.libelle, data.id, true, true)
        this.originalSelectTarget.add(option)

        // Recharger l'interface
        this.loadReferentialData()

        // Notification de succès
        this.showSuccess(data.message || `${this.getEntityLabel()} ajouté avec succès`)

        // Événement personnalisé
        this.dispatch('itemCreated', {
            detail: {
                type: this.typeValue,
                item: data
            }
        })
    }

    /**
     * Nettoyage des erreurs de formulaire
     */
    clearFormErrors(form) {
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove())
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'))
        form.querySelectorAll('.alert-danger').forEach(el => el.remove())
    }

    /**
     * Affichage des erreurs de formulaire
     */
    displayFormErrors(form, errors) {
        const alertContainer = document.createElement('div')
        alertContainer.className = 'alert alert-danger'
        alertContainer.innerHTML = errors.map(error => `<div>${error}</div>`).join('')
        form.prepend(alertContainer)
    }

    /**
     * Remise à l'état initial du bouton d'ajout
     */
    resetAddButton() {
        this.addButton.disabled = false
        this.addButton.innerHTML = '<i class="bi bi-plus-circle"></i> Ajouter'
    }

    /**
     * Récupération du libellé de l'entité
     */
    getEntityLabel() {
        const labels = {
            'planche': 'Planche',
            'theme': 'Thème', 
            'type-prise': 'Type de prise',
            'type-vente': 'Type de vente',
            'pochette-indiv': 'Pochette individuelle',
            'pochette-fratrie': 'Pochette fratrie'
        }
        return labels[this.typeValue] || 'Élément'
    }

    /**
     * Récupération de l'icône de l'entité
     */
    getEntityIcon() {
        const icons = {
            'planche': 'card-image',
            'theme': 'palette',
            'type-prise': 'camera',
            'type-vente': 'cart',
            'pochette-indiv': 'person-square',
            'pochette-fratrie': 'people'
        }
        return icons[this.typeValue] || 'bookmark'
    }

    /**
     * Affichage de notification de succès
     */
    showSuccess(message) {
        // Utilisation du service de notification s'il existe
        if (window.NotificationService) {
            window.NotificationService.showSuccess(message)
        } else {
            this.showSimpleNotification(message, 'success')
        }
    }

    /**
     * Affichage de notification d'erreur
     */
    showError(message) {
        if (window.NotificationService) {
            window.NotificationService.showError(message)
        } else {
            this.showSimpleNotification(message, 'error')
        }
    }

    /**
     * Notification simple (fallback)
     */
    showSimpleNotification(message, type) {
        const toast = document.createElement('div')
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;'
        toast.textContent = message
        
        document.body.appendChild(toast)
        
        setTimeout(() => {
            toast.remove()
        }, 3000)
    }

    /**
     * Charger les données avec fallback first strategy
     * Pattern: Fallback First Strategy
     */
    async loadDataWithFallbackFirst() {
        console.log(`🔄 Démarrage fallback-first pour ${this.typeValue}`);
        
        // 1. Charger immédiatement les données du select (affichage rapide)
        this.loadFallbackData()
        
        // 2. Tenter de charger les données via API (mise à jour si possible)
        try {
            await this.loadReferentialData()
        } catch (error) {
            console.log(`⚠️ API finale échouée pour ${this.typeValue}, garde le fallback`);
        }
    }
} 