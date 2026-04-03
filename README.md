# ✅ Gestionnaire de Tâches

Application web de gestion des tâches développée en **PHP + MySQL**, avec un design moderne sombre.

## 📁 Structure

```
task-manager/
├── index.php      # Liste des tâches + filtres + stats
├── add_edit.php   # Formulaire ajout / modification
├── db.php         # Connexion MySQL + création de table
├── style.css      # Styles (dark theme)
└── README.md
```

## 🚀 Installation

1. Cloner le dépôt dans votre dossier `htdocs` (XAMPP) ou `www` (WAMP)  
2. Créer une base de données MySQL nommée `task_manager`  
3. Configurer `db.php` (host, user, password)  
4. Ouvrir `http://localhost/task-manager/`

> La table `tasks` est créée **automatiquement** au premier chargement.

## ✨ Fonctionnalités

- 📋 Liste des tâches avec **statistiques** (total, en attente, en cours, terminées)
- ➕ Ajouter / ✏️ Modifier une tâche
- 🗑️ Supprimer une tâche
- 🔄 Changer le statut (en attente → en cours → terminée) en 1 clic
- 🔍 Recherche full-text + filtres par **statut** et **priorité**
- 📅 Dates limites avec alerte si en retard
- 🎨 Design dark mode responsive

## 🛠️ Technologies

- PHP 8+
- MySQL / MariaDB
- HTML5 / CSS3
- Font Awesome 6
