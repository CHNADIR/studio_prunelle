import { Controller } from "@hotwired/stimulus"
import { Modal } from "bootstrap"


export default class extends Controller {
    static values = {
        url: String,        // URL de la modal
        type: String       // Type de référentiel
    }

    connect() {
        console.log('🎭 Modal Controller connecté:', {
            type: this.typeValue,
            url: this.urlValue
        });
    }

    /**
     * Ouvre la modal d'ajout
     */
    async openModal() {
        console.log(`🔄 Ouverture modal ${this.typeValue}...`);
        
        try {
            const response = await fetch(this.urlValue)
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`)
            }
            
            const modalHtml = await response.text()
            
            // Créer et afficher la modal
            const modalElement = this.createModalElement(modalHtml)
            document.body.appendChild(modalElement)
            
            const modal = new Modal(modalElement)
            modal.show()
            
            // Nettoyer après fermeture
            modalElement.addEventListener('hidden.bs.modal', () => {
                modalElement.remove()
            })
            
        } catch (error) {
            console.error(`❌ Erreur modal ${this.typeValue}:`, error)
            this.showError(`Impossible d'ouvrir la modal d'ajout`)
        }
    }

    /**
     * Crée l'élément modal
     */
    createModalElement(html) {
        const modalWrapper = document.createElement('div')
        modalWrapper.innerHTML = html
        
        const modalElement = modalWrapper.querySelector('.modal') || modalWrapper.firstElementChild
        
        if (!modalElement) {
            throw new Error('Élément modal non trouvé dans la réponse')
        }
        
        return modalElement
    }

    /**
     * Affiche un message d'erreur
     */
    showError(message) {
        // Utiliser une notification toast simple
        const toast = document.createElement('div')
        toast.className = 'toast position-fixed top-0 end-0 m-3'
        toast.style.zIndex = '9999'
        toast.innerHTML = `
            <div class="toast-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong class="ms-2 me-auto">Erreur</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `
        
        document.body.appendChild(toast)
        
        const bsToast = new bootstrap.Toast(toast)
        bsToast.show()
        
        // Nettoyer après
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove()
        })
    }
} 