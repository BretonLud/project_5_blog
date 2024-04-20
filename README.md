# project\_5\_blog

### Prérequis

***

Installer PHP 8.3

Installer Apache2

Installer Node.js et npm

Installer Composer

Installer Mysql

Installer MailHog

### Installation du projet

***

Pour commencer on clone le projet:

<pre><code><strong>git clone git@github.com:BretonLud/project_5_blog.git
</strong></code></pre>

Ensuite il faut se rendre dans le dossier du projet et installer les dépendance NPM :&#x20;

```
cd project_5_blog
npm i
npm run build
```

Créer un fichier la racine du projet .env.local

```
touch .env.local
```

Paramétrer les variables d'environnement avec vos informations: (le smtp paramétré est celui de MailHog)

```
DB_HOST=localhost
DB_NAME=db_name
DB_USER=db_user
DB_PASS=db_pass
API_KEY=secret_api_key
MAILER_DSN=smtp://localhost:1025
```

Pour générer dans votre terminal une valeur sur pour votre API\_KEY, vous pouvez utiliser la commande suivant:&#x20;

```
php -r "echo bin2hex(random_bytes(16));"
```

Installer les librairies avec composer:

```
composer i
```

Créer la base de données et les tables:&#x20;

```
mysql -u user -p
SOURCE /var/www/project_5_blog/src/Database/create.sql;
```

Paramétrer votre serveur Apache2 pour pointer vers:&#x20;

```
/var/www/project_5_blog/public
```

Donner les droits au serveur pour le dossier pictures dans public:&#x20;

```
 chown www-data:www-data public/pictures
```

Lancer MailHog:

```
~/go/bin/MailHog
```

Créer votre premier utilisateur sur le site et aller sur localhost:8025 pour récupérer le mail sur MailHog.\
Donner a cet utilisateur le droit admin en retournant sur mysql et en utilisant la commande suivante:&#x20;

<pre><code>mysql -u user -p
<strong>UPDATE user SET role = 'ADMIN' WHERE id = 1;
</strong></code></pre>
