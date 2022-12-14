# Projet nosql

Le but de ce projet est de constituer une application web simple, avec au moins une page avec un formulaire qui puisse récupérer des données écrites par un utilisateur et qui les persiste dans **au moins deux systemes différents** parmi : 
- postgresql 
- redis 
- mongodb 
- neo4j 
- elasticsearch

Le projet doit avoir au moins un fichier docker compose qui orchestre donc au moins 3 docker (un pour la partie server, et un par techno de stockage)

Je doit pourvoir **git clone**, jouer **docker compose up** pour interagir avec l'interface web, **lancer au moins deux terminaux pour me connecter à chacun des systemes de persistance et constater la persistance**.
La date limite de publication du projet sur github est le 23 novembre.

### Membres du groupe
- DEJEAN Dorian
- HESSINI Yanis

### Choix des technologies
- Web : Serveur Nginx avec développement sous PHP
- Système de DB : Postgresql et Redis

### Application développée
Système de mémos partagés (pas de compte utilisateur)

## Commandes

### Build docker
```bash
docker-compose up -d --build
```
### Connexion aux db
#### Postgres
```bash
docker exec -it postgresql-memo bash
psql -U admin
```
#### Redis
```bash
docker exec -it redis-memo redis-cli
```

### Structure du projet

Le projet contient 5 containers docker :
- nginx-memo : Permet la gestion du serveur
- php-memo : Gère la partie php sur serveur
- postgresql-memo : Gère la BD Postgres
- redis-memo : Gère la BD Redis
- adminer-memo : Permet de gérer Postgres via une interface WEB
