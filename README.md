
<img width="1361" height="591" alt="Capture du 2025-11-23 22-12-05" src="https://github.com/user-attachments/assets/227027b0-7415-4bce-91af-251370349be3" />


<img width="1352" height="591" alt="app_home" src="https://github.com/user-attachments/assets/806c485f-2cc6-490e-b30b-14db29aeca9d" />


<img width="1361" height="763" alt="activity_logger" src="https://github.com/user-attachments/assets/b02f7239-8d9b-42db-b783-4e62ca54ee3a" />

<img width="1362" height="595" alt="user_1" src="https://github.com/user-attachments/assets/dba91ada-a064-4c7c-bc43-0d936093c74b" />

<img width="1355" height="299" alt="admin_suer_suite" src="https://github.com/user-attachments/assets/c03ee21c-7d13-4fd8-8783-ba4984c1cc1f" />


<img width="1365" height="593" alt="Capture du 2025-11-23 13-22-18" src="https://github.com/user-attachments/assets/1b728cad-e38e-48da-81f2-ba83cfbbd544" />

<img width="1359" height="595" alt="Capture du 2025-11-23 13-14-18" src="https://github.com/user-attachments/assets/fd303e6c-e2c5-4a26-97e5-2164bc416e8b" />


<img width="1358" height="591" alt="cancel_d" src="https://github.com/user-attachments/assets/b3531f8e-93ea-46de-b0b9-37c7915fc097" />


<img width="1366" height="768" alt="Capture du 2025-09-12 00-01-56" src="https://github.com/user-attachments/assets/48508dfd-c8e5-470a-a91b-02130895b7c4" />


<img width="1358" height="604" alt="Capture du 2025-11-18 21-58-50" src="https://github.com/user-attachments/assets/1af7f97e-d924-46bd-9bb3-f6f819fe390d" />

<img width="1351" height="603" alt="Capture du 2025-11-19 10-55-34" src="https://github.com/user-attachments/assets/973f0fb1-299a-41e0-8de1-c38278ae29f1" />

<img width="1366" height="768" alt="Capture du 2025-11-20 17-06-07" src="https://github.com/user-attachments/assets/24a55a84-040a-4b28-910f-86a02b3160b8" />



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
php bin/console importmap:install

```

### 4. Configurer les variables d’environnement

Copiez le fichier `.env` et configurez vos identifiants (DB, Mercure, etc.) :

```bash
cp .env .env.local
```

Exemple de configuration PostgreSQL :

```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/orindafrica_dev?serverVersion=16&charset=utf8"
```

### 5. Créer la base de données et exécuter les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Lancer le serveur de développement

```bash
symfony serve -d ou configurer avec nginx puis configurer avec ngrok pour le partage public 
```

Accédez à l’application :
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 📖 Objectif

Ce dépôt regroupe l’ensemble du développement **back-end et front-end** de la plateforme ORIND-Africa.

```

---
```
