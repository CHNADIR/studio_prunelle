import { Controller } from '@hotwired/stimulus';

/**
 * Stimulus controller pour gérer la sélection des planches
 */
export default class extends Controller {
    static targets = ['individualList', 'fratrieList', 'searchInput', 'selectedCount', 'totalPrice'];
    static values = {
        apiUrl: String
    };

    connect() {
        // Initialiser l'affichage du compteur et du prix total
        this.updateCounters();
        
        // Ajouter les listeners pour les changements dans les sélections
        this.individualListTarget.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateCounters());
        });
        
        this.fratrieListTarget.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateCounters());
        });
        
        // Configurer la recherche si disponible
        if (this.hasSearchInputTarget) {
            this.searchInputTarget.addEventListener('input', this.handleSearch.bind(this));
        }
    }
    
    updateCounters() {
        // Mettre à jour le compteur de planches sélectionnées
        if (this.hasSelectedCountTarget) {
            const individualCount = this.getSelectedCount(this.individualListTarget);
            const fratrieCount = this.getSelectedCount(this.fratrieListTarget);
            this.selectedCountTarget.textContent = `Planches sélectionnées: ${individualCount + fratrieCount}`;
        }
        
        // Mettre à jour le prix total si disponible
        if (this.hasTotalPriceTarget) {
            const totalPrice = this.calculateTotalPrice();
            this.totalPriceTarget.textContent = `Prix total: ${totalPrice.toFixed(2)} €`;
        }
    }
    
    getSelectedCount(container) {
        return container.querySelectorAll('input[type="checkbox"]:checked').length;
    }
    
    calculateTotalPrice() {
        let total = 0;
        
        // Parcourir les planches individuelles sélectionnées
        this.individualListTarget.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.price || 0);
            total += price;
        });
        
        // Parcourir les planches fratries sélectionnées
        this.fratrieListTarget.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.price || 0);
            total += price;
        });
        
        return total;
    }
    
    handleSearch(event) {
        const query = event.target.value.toLowerCase();
        
        // Filtrer les planches individuelles
        this.filterPlanches(this.individualListTarget, query);
        
        // Filtrer les planches fratries
        this.filterPlanches(this.fratrieListTarget, query);
    }
    
    filterPlanches(container, query) {
        container.querySelectorAll('.planche-item').forEach(item => {
            const plancheName = item.querySelector('label').textContent.toLowerCase();
            if (plancheName.includes(query)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    openModal(event) {
        // Ouvrir la modal pour ajouter une nouvelle planche
        const url = event.currentTarget.dataset.url;
        // Implémentation de l'ouverture de la modal...
    }
}