#!/bin/bash

# Script pour charger les fixtures des référentiels, planches et prises de vue
# Sprint 3 - Référentiels dynamiques + Sprint 4 - Gestion des planches + Sprint 5 - Création de prise de vue

echo "🚀 Chargement des fixtures pour les référentiels, planches et prises de vue..."

# Vérifier si l'environnement Docker est actif
if [ -f "compose.yaml" ]; then
    echo "📦 Utilisation de Docker Compose..."
    docker compose exec app php bin/console doctrine:fixtures:load --group=referential --append
    docker compose exec app php bin/console doctrine:fixtures:load --group=planches --append
    docker compose exec app php bin/console doctrine:fixtures:load --group=prises-de-vue --append
else
    echo "🐘 Utilisation de PHP local..."
    php bin/console doctrine:fixtures:load --group=referential --append
    php bin/console doctrine:fixtures:load --group=planches --append
    php bin/console doctrine:fixtures:load --group=prises-de-vue --append
fi

echo "✅ Fixtures chargées avec succès !"
echo ""
echo "📊 Référentiels disponibles :"
echo "   - Types de prise : Photo individuelle, Photo de classe, etc."
echo "   - Types de vente : Vente libre, Vente groupée, etc."  
echo "   - Thèmes : Automne, Hiver, Printemps, Noël, etc."
echo ""
echo "📋 Planches disponibles :"
echo "   - Planches individuelles : Portrait A4, Photo 10x15, etc."
echo "   - Planches fratries : Pack Fratrie A4, Photo Fratrie 13x18, etc."
echo "   - Planches seules : Poster A3, Canvas 20x30, etc."
echo ""
echo "📸 Prises de vue d'exemple :"
echo "   - 6 prises de vue complètes avec relations"
echo "   - Différents types d'établissements (primaire, collège, lycée)"
echo "   - Variété de thèmes et de planches sélectionnées"
echo ""
echo "🎯 Vous pouvez maintenant tester l'application complète !" 