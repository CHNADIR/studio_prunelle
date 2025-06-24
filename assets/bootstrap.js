import { startStimulusApp } from '@symfony/stimulus-bridge';

// Enregistrer manuellement nos contrôleurs personnalisés
import TileSelectorController from './controllers/tile_selector_controller.js';
import ReferentialModalController from './controllers/referential_modal_controller.js';
import ReferentialManagerController from './controllers/referential_manager_controller.js';

// Démarrer l'application Stimulus
const app = startStimulusApp();

// Enregistrer nos contrôleurs
app.register('tile-selector', TileSelectorController);
app.register('referential-modal', ReferentialModalController);
app.register('referential-manager', ReferentialManagerController);

console.log('🎯 Stimulus Controllers enregistrés:', {
    'tile-selector': TileSelectorController,
    'referential-modal': ReferentialModalController,
    'referential-manager': ReferentialManagerController
});

export { app };