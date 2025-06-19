# Cahier des charges - Application Studio Prunelle

  

# 🎯 Objectif

  

Développer une application web sécurisée pour la gestion des prises de vue scolaires, permettant à plusieurs rôles (admin, photographe, superadmin) de collaborer efficacement. L’interface doit refléter la logique de la fiche papier (B.A.T) en facilitant la saisie et la gestion des informations photographiques et commerciales.

  

# 👥 Rôles utilisateurs & accès

  

\- SuperAdmin : Gestion des utilisateurs

\- Admin : Gestion des prises de vue, écoles, planches et référentiels

\- Photographe : Consultation de ses prises de vue et ajout de commentaires

  

# 📄 Pages à développer

  

## **1\. Page d’authentification**

  

Connexion sécurisée avec redirection selon le rôle de l'utilisateur. Utilisation de Symfony Security.

  

a. Page d’accueil (prises de vue) : liste avec actions consulter, supprimer, ajouter

b. Création de prise de vue : formulaire complet avec ajout dynamique des options

c. Détail de prise de vue : fidèle à la fiche papier avec bouton modifier

d. Gestion des écoles : liste des écoles, bouton ajouter, consulter, modifier, supprimer

e. Formulaire de création et édition d’une école

  

a. Page d’accueil : liste de ses prises de vue (lecture seule + consulter)

b. Détail de prise de vue : lecture seule sauf champ commentaire modifiable

  

a. Page d’accueil : liste des utilisateurs avec bouton ajouter

b. Détail utilisateur : avec actions modifier et supprimer

c. Formulaire de création d’utilisateur

  

# 🧩 Logique base de données

  

Entités principales : User, Ecole, PriseDeVue, Planche, TypePrise, TypeVente, Theme

Relations :

\- PriseDeVue → ManyToOne vers User, Ecole, TypePrise, TypeVente, Theme

\- PriseDeVue → ManyToMany vers Planche (individuelle + fratrie)

  

# 🧪 Technologies à utiliser

  

\- Symfony 6.4, Doctrine ORM, MySQL, Symfony Security

\- Twig, Bootstrap ou Tailwind CSS, JavaScript

\- AJAX pour les ajouts dynamiques dans les formulaires

\- EasyAdmin (facultatif pour l’admin back-office)

  

# ✅ Bonnes pratiques

  

\- Respect PSR-12, Voters pour les droits, Annotations Assert pour validation

\- Enum PHP 8.1+ pour les référentiels dynamiques si besoin

\- Migrations Doctrine versionnées, Collections pour relations

  

## **2\. Interface Admin (détails)**

  

a. Page d’accueil – Liste des prises de vue :

  - Tableau affichant : école, date, photographe, type de prise, thème, nombre d’élèves et classes.

  - Actions disponibles : consulter, supprimer

  - Bouton 'Ajouter une prise de vue' redirigeant vers le formulaire de création.

b. Création d’une prise de vue :

  - Formulaire en plusieurs sections :

    ▪ Informations générales : date, photographe, école, nombre d’élèves, classes

    ▪ Type de prise, thème, type de vente (sélection depuis référentiels dynamiques)

    ▪ Sélection de pochettes (planches individuelles et fratries), avec checkboxes

    ▪ Prix école / prix parents

    ▪ Commentaire libre

  - Ajout dynamique : lien ou bouton 'Ajouter un nouveau' à côté des select (type de vente, type de prise, planche, thème), affichant un mini-formulaire AJAX.

c. Détail d’une prise de vue :

  - Mise en page calquée sur la fiche papier (colonnes, blocs d’information bien alignés)

  - Tous les champs affichés en lecture seule

  - Bouton 'Modifier' activant le formulaire complet avec tous les champs éditables

d. Gestion des écoles :

  - Page listant toutes les écoles (tableau avec code, nom, ville, contact, actif ou non)

  - Actions : consulter, modifier, supprimer

  - Bouton 'Ajouter une école' redirigeant vers le formulaire de création

  - Détail de l’école : fiche contenant les informations + liste des dernières prises de vue

  - Formulaire : code, nom, genre, adresse, ville, code postal, téléphone, mail, statut (actif)

  

## **3\. Interface Photographe (détails)**

  

a. Page d’accueil :

  - Affiche uniquement les prises de vue liées au photographe connecté

  - Informations visibles : date, école, type de prise

  - Action disponible : consulter

b. Détail de prise de vue :

  - Interface calquée sur la fiche papier

  - Tous les champs sont en lecture seule sauf le champ 'commentaire' (modifiable)

  - Bouton 'Enregistrer le commentaire' visible uniquement pour les photographes

  

## **4\. Interface SuperAdmin (détails)**

  

﻿a. Page d’accueil – Liste des utilisateurs :

  - Affiche nom, email, rôle

  - Actions : consulter, supprimer

  - Bouton 'Ajouter un utilisateur'

b. Détail utilisateur :

  - Informations : nom, email, rôle

  - Boutons : modifier, supprimer

c. Formulaire de création :

  - Champs requis : nom, email, mot de passe, rôle (choix entre admin / photographe / superadmin)