import { Controller } from "@hotwired/stimulus"


export default class extends Controller {
    static targets = [
        "tilesContainer", 
        "hiddenSelect"
    ]
    
    static values = {
        fieldName: String,      // Nom du champ (planches, pochettesIndiv, etc.)
        multiple: Boolean,      // Autoriser sélection multiple
        color: String          // Couleur Bootstrap (primary, success, warning, etc.)
    }

    connect() {
        console.log('🎨 Tile Selector connecté:', {
            fieldName: this.fieldNameValue,
            multiple: this.multipleValue,
            color: this.colorValue,
            element: this.element
        });
        
        // Vérifier que les targets existent
        console.log('🎯 Targets disponibles:', {
            hasHiddenSelectTarget: this.hasHiddenSelectTarget,
            hasTilesContainerTarget: this.hasTilesContainerTarget
        });
        
        if (!this.hasHiddenSelectTarget) {
            console.error('❌ Hidden select target manquant pour', this.fieldNameValue);
            return;
        }
        
        if (!this.hasTilesContainerTarget) {
            console.error('❌ Tiles container target manquant pour', this.fieldNameValue);
            return;
        }
        
        this.loadOptionsFromSelect()
        this.setupInterface()
    }

    /**
     * Charge les options depuis le select caché
     */
    loadOptionsFromSelect() {
        if (!this.hasHiddenSelectTarget) {
            console.error('❌ Select caché non trouvé');
            return;
        }

        this.options = []
        const select = this.hiddenSelectTarget
        
        // Extraire les options du select
        Array.from(select.options).forEach(option => {
            if (option.value) { // Ignorer l'option vide
                this.options.push({
                    id: option.value,
                    label: option.textContent.trim(),
                    selected: option.selected
                })
            }
        })

        console.log(`📋 ${this.options.length} options chargées pour ${this.fieldNameValue}:`, this.options);
    }

    /**
     * Configure l'interface avec des carreaux
     */
    setupInterface() {
        const container = this.tilesContainerTarget
        container.innerHTML = '' // Nettoyer le spinner

        if (this.options.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted p-3">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0">Aucune option disponible</p>
                </div>
            `
            return
        }

        // Créer les carreaux
        const tilesWrapper = document.createElement('div')
        tilesWrapper.className = 'tiles-wrapper d-flex flex-wrap gap-2'
        
        this.options.forEach(option => {
            const tile = this.createTile(option)
            tilesWrapper.appendChild(tile)
        })

        container.appendChild(tilesWrapper)
    }

    /**
     * Crée un carreau sélectionnable
     */
    createTile(option) {
        const tile = document.createElement('div')
        tile.className = `tile-item btn btn-outline-${this.colorValue} btn-sm position-relative`
        tile.dataset.optionId = option.id
        tile.innerHTML = `
            <i class="bi bi-check-circle-fill position-absolute top-0 start-100 translate-middle text-success d-none"></i>
            <span class="tile-label">${option.label}</span>
        `
        
        // Style du carreau
        tile.style.cssText = `
            min-width: 100px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 8px;
            position: relative;
        `

        // Marquer comme sélectionné si nécessaire
        if (option.selected) {
            this.markTileAsSelected(tile)
        }

        // Gestionnaire de clic
        tile.addEventListener('click', () => {
            this.toggleTile(tile)
        })

        // Effet hover
        tile.addEventListener('mouseenter', () => {
            if (!tile.classList.contains('selected')) {
                tile.style.transform = 'scale(1.05)'
                tile.style.boxShadow = `0 4px 8px rgba(var(--bs-${this.colorValue}-rgb), 0.3)`
            }
        })

        tile.addEventListener('mouseleave', () => {
            if (!tile.classList.contains('selected')) {
                tile.style.transform = 'scale(1)'
                tile.style.boxShadow = 'none'
            }
        })

        return tile
    }

    /**
     * Toggle un carreau (sélectionné/non sélectionné)
     */
    toggleTile(tile) {
        const isSelected = tile.classList.contains('selected')
        
        if (isSelected) {
            this.markTileAsUnselected(tile)
        } else {
            // Si sélection unique, désélectionner les autres
            if (!this.multipleValue) {
                this.clearAllSelections()
            }
            this.markTileAsSelected(tile)
        }

        this.updateHiddenSelect()
        this.showSelectionFeedback()
    }

    /**
     * Marque un carreau comme sélectionné
     */
    markTileAsSelected(tile) {
        tile.classList.add('selected')
        tile.classList.remove(`btn-outline-${this.colorValue}`)
        tile.classList.add(`btn-${this.colorValue}`)
        
        // Afficher l'icône de validation
        const icon = tile.querySelector('.bi-check-circle-fill')
        if (icon) {
            icon.classList.remove('d-none')
        }

        // Animation
        tile.style.transform = 'scale(1.1)'
        setTimeout(() => {
            tile.style.transform = 'scale(1)'
        }, 150)
    }

    /**
     * Marque un carreau comme non sélectionné
     */
    markTileAsUnselected(tile) {
        tile.classList.remove('selected')
        tile.classList.remove(`btn-${this.colorValue}`)
        tile.classList.add(`btn-outline-${this.colorValue}`)
        
        // Masquer l'icône de validation
        const icon = tile.querySelector('.bi-check-circle-fill')
        if (icon) {
            icon.classList.add('d-none')
        }
    }

    /**
     * Déselectionne tous les carreaux
     */
    clearAllSelections() {
        const selectedTiles = this.tilesContainerTarget.querySelectorAll('.tile-item.selected')
        selectedTiles.forEach(tile => {
            this.markTileAsUnselected(tile)
        })
    }

    /**
     * Met à jour le select caché avec les valeurs sélectionnées
     */
    updateHiddenSelect() {
        const select = this.hiddenSelectTarget
        const selectedIds = this.getSelectedIds()

        // Déselectionner toutes les options
        Array.from(select.options).forEach(option => {
            option.selected = false
        })

        // Sélectionner les options correspondantes
        selectedIds.forEach(id => {
            const option = select.querySelector(`option[value="${id}"]`)
            if (option) {
                option.selected = true
            }
        })

        // Déclencher l'événement change
        select.dispatchEvent(new Event('change', { bubbles: true }))
        
        console.log(`✅ Select ${this.fieldNameValue} mis à jour:`, selectedIds);
    }

    /**
     * Retourne les IDs des carreaux sélectionnés
     */
    getSelectedIds() {
        const selectedTiles = this.tilesContainerTarget.querySelectorAll('.tile-item.selected')
        return Array.from(selectedTiles).map(tile => tile.dataset.optionId)
    }

    /**
     * Affiche un feedback visuel de sélection
     */
    showSelectionFeedback() {
        const selectedCount = this.getSelectedIds().length
        const container = this.tilesContainerTarget.parentElement
        
        // Enlever ancien badge s'il existe
        const oldBadge = container.querySelector('.selection-badge')
        if (oldBadge) oldBadge.remove()
        
        if (selectedCount > 0) {
            const badge = document.createElement('span')
            badge.className = `selection-badge badge bg-${this.colorValue} position-absolute top-0 end-0 m-2`
            badge.textContent = selectedCount
            badge.style.cssText = 'z-index: 10;'
            
            container.style.position = 'relative'
            container.appendChild(badge)
            
            // Animation du badge
            badge.style.transform = 'scale(0)'
            setTimeout(() => {
                badge.style.transform = 'scale(1)'
                badge.style.transition = 'transform 0.2s ease'
            }, 50)
        }
    }

    /**
     * Recharge les données depuis une API (pour après ajout)
     */
    async refresh() {
        console.log(`🔄 Rafraîchissement des données pour ${this.fieldNameValue}...`);
        
        // Pour l'instant, on recharge simplement depuis le select
        // TODO: Implémenter le rechargement via API si nécessaire
        this.loadOptionsFromSelect()
        this.setupInterface()
    }
} 