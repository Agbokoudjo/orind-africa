<img width="1366" height="768" alt="Capture du 2025-10-29 10-36-23" src="https://github.com/user-attachments/assets/41dc2112-e3de-49f0-b8cf-d4946409fb9e" />

<img width="1366" height="768" alt="Capture du 2025-10-29 10-36-14" src="https://github.com/user-attachments/assets/bd56ae98-1c4f-4aaa-a3c5-123f7e8f46b8" />

<img width="1354" height="600" alt="Capture du 2025-10-29 12-01-13" src="https://github.com/user-attachments/assets/8100175b-816c-4d75-9c54-d8b034265365" />

<img width="1366" height="768" alt="Capture du 2025-10-29 12-02-30" src="https://github.com/user-attachments/assets/e21d1c8e-1913-4c2a-933c-ad7ec22c35ef" />

<img width="1366" height="768" alt="Capture du 2025-10-29 10-03-38" src="https://github.com/user-attachments/assets/4f857f41-56b0-4d8e-9f56-00c3f0a43560" />



````markdown
# ORIND-Africa

**Plateforme numérique officielle de l’organisation ORIND-Africa**  
*(Organisation des Réalisateurs, Informaticiens, Numériques et Développeurs Africains)*

---

## 🚀 Présentation
Ce projet constitue le siège numérique d’ORIND-Africa, centralisant la gestion des membres, projets, services et outils collaboratifs.  
Il regroupe à la fois le **back-office** et le **front-office** de la plateforme.

---

## 🛠️ Stack technique

- **Symfony 7.3** (PHP >= 8.3) avec **architecture hexagonale**
- **Sonata Admin 5.x** pour le back-office centralisé
- **ReactJS + TypeScript** pour le front et les composants interactifs
- **AssetMapper** pour la gestion moderne des assets
- **PostgreSQL** pour la persistance relationnelle
- **MongoDB (NoSQL)** pour la messagerie temps réel
- **Mercure** pour la diffusion temps réel
- **Nginx + Ngrok** pour l’exposition sécurisée du projet

---

## 📦 Bundles Sonata utilisés
- SonataAdminBundle  
- SonataUserBundle  
- SonataClassificationBundle  
- SonataMediaBundle  
- SonataPageBundle  
- SonataDoctrineORMAdminBundle  
- SonataDoctrineDBALBundle  

---

## 🌍 Fonctionnalités principales
- Intégration **multilingue FR/EN** et évolutive
- Gestion granulaire des rôles (Fondateur, Ministres, Membres actifs)
- Modules clés :
  - Gestion des projets
  - Services numériques
  - Messagerie instantanée
  - Système de votes internes
  - Formations internes
  - Paiements & soldes personnels

---

## ⚡ Installation rapide

### 1. Cloner le dépôt
```bash
git clone https://github.com/Agbokoudjo/orind-africa.git
cd orind-africa
````

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances front-end

```bash
yarn install
# ou
npm install
```

### 4. Configurer les variables d’environnement

Copiez le fichier `.env` et configurez vos identifiants (DB, Mercure, etc.) :

```bash
cp .env .env.local
```

Exemple de configuration PostgreSQL :

```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/orindafrica?serverVersion=16&charset=utf8"
```

### 5. Créer la base de données et exécuter les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Lancer le serveur de développement

```bash
symfony serve -d
```

Accédez à l’application :
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 📖 Objectif

Ce dépôt regroupe l’ensemble du développement **back-end et front-end** de la plateforme ORIND-Africa.

```

---
```
