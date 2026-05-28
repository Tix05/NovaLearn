# NovaLearn

## Installation

### 1. Cloner le projet

```bash
git clone git@github.com:Tix05/Apiplateforme.git
cd Apiplateforme
```

---

### 2. Backend — Symfony

```bash
cd apiplateforme
composer install
```

Copier le fichier d'environnement :

```bash
cp .env .env.local
```

Éditer `.env.local` et remplir les variables suivantes :

```env
# Clé secrète de l'application (chaîne aléatoire)
APP_SECRET=une_chaine_aleatoire_32_caracteres

# Base de données (adapter user, password et nom de la BDD)
DATABASE_URL="mysql://user:password@127.0.0.1:3306/db_apiplateforme?serverVersion=8.0"

# Clé API Google Gemini
GOOGLE_GEMINI_API_KEY=votre_cle_gemini

# JWT — généré automatiquement à l'étape suivante
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase
```

---

### 3. Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

> Les clés sont générées dans `config/jwt/`. La `JWT_PASSPHRASE` doit correspondre à celle définie dans `.env.local`.

---

### 4. Base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

---

### 5. Lancer le serveur

```bash
symfony server:start ou php -S 127.0.0.1:8000 -t public
```

---

### 6. Frontend — React/Vite

```bash
cd frontend
npm install
npm run dev
```

> Application disponible sur `http://localhost:5173`

---

## Prérequis

- PHP 8.1
- Composer
- Node.js 18+
- npm