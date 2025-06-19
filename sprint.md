# Plan de développement par Sprints – Studio Prunelle

  

# Sprint 1 – Initialisation & Authentification

-   • Mise en place du projet Symfony 6.4 avec Docker
-   • Configuration de la base de données MySQL
-   • Installation des bundles nécessaires (security, maker, annotations, form, twig, etc.)
-   • Création de l'entité User avec rôles : ADMIN, PHOTOGRAPHE, SUPERADMIN
-   • Implémentation du système d’authentification (login + redirection par rôle)

# Sprint 2 – Gestion des écoles

-   • Création de l’entité Ecole (code, nom, adresse, ville, CP, genre, contact, actif)
-   • Création du CRUD complet pour les écoles (liste, fiche, ajouter, modifier, supprimer)
-   • Ajout de la relation OneToMany entre Ecole et PriseDeVue (préparation)

# Sprint 3 – Référentiels dynamiques

-   • Création des entités : TypePrise, TypeVente, Theme (modifiables depuis l’interface admin)
-   • Interface d’ajout et de modification rapide depuis le formulaire de prise de vue (AJAX ou modal)
-   • Préparation des données en fixtures si besoin pour le développement

# Sprint 4 – Gestion des planches (pochettes)

-   • Création de l’entité Planche avec les champs : nom, type (individuelle/fratrie/seule), formats (array/json), prixEcole, prixParents
-   • Ajout d’un champ usage ou type pour identifier les planches seules
-   • Création du formulaire d’ajout/édition de planches pour l’admin

# Sprint 5 – Création de prise de vue

-   • Création de l’entité PriseDeVue avec relations vers Ecole, User, TypePrise, TypeVente, Theme
-   • Ajout des champs : date, nb\_classes, nb\_eleves, commentaire, prix\_ecole, prix\_parents
-   • Ajout des relations ManyToMany avec les planches (individuelles et fratries)
-   • Formulaire complet de création avec logique de groupes (comme la fiche papier)

# Sprint 6 – Page de détail des prises de vue (Admin & Photographe)

-   • Création de la page de consultation d'une prise de vue avec mise en page inspirée de la fiche papier
-   • Pour Admin : affichage de toutes les données avec bouton Modifier
-   • Pour Photographe : affichage en lecture seule sauf champ Commentaire (modification autorisée)

# Sprint 7 – Interface SuperAdmin : gestion des utilisateurs

-   • CRUD complet pour les utilisateurs (liste, détail, ajouter, modifier, supprimer)
-   • Attribution du rôle à la création (admin, photographe, superadmin)
-   • Formulaire sécurisé avec hachage du mot de passe

# Sprint 8 – Interface utilisateur & ergonomie

-   • Amélioration de la lisibilité et alignement des champs comme sur la fiche papier
-   • Utilisation de Bootstrap ou Tailwind pour une interface claire et responsive
-   • Organisation des formulaires en sections (groupe, tarification, commentaire...)

# Sprint 9 – Recette et finalisation

-   • Vérification manuelle de tous les parcours utilisateurs (admin, photographe, superadmin)
-   • Correction des éventuels bugs ou oublis fonctionnels
-   • Nettoyage du code et ajustement des validations Symfony