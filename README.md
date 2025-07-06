# Projet API - Gestion des échanges de poste

Ce projet propose une API PHP simple permettant de gérer des demandes d'échange de poste.

---

### Prérequis

- PHP 8.1 ou supérieur
- Serveur MySQL ou MariaDB

### Structure du projet
```
├── config
├──── database.php
├──── load_env.php
├── config
├──── schema.sql (Script de création de la base et des données de test)
├── .env (fichier à créer et à adapter en utilisant .env.example)
├── README.md
└── ...
```



## Étape 1 : Création de la base de données

Le fichier [`schema.sql`](./sql/schema.sql) permet de créer la base `planning` et d’y initialiser la table `swap_shift` avec quelques données de test.

### Ligne de commande MySQL

```bash
mysql -u root -p < sql/schema.sql
```

## Structure de la table swap_shift
| Colonne            | Type           | Description                                                 |
| ------------------ | -------------- | ----------------------------------------------------------- |
| `id`               | `INT UNSIGNED` | Identifiant unique auto-incrémenté de la demande d’échange. |
| `post_id`          | `VARCHAR(50)`  | Identifiant du poste concerné par la demande d’échange.     |
| `requester_id`     | `INT UNSIGNED` | Identifiant du salarié qui initie la demande.               |
| `receiver_id`      | `INT UNSIGNED` | Identifiant du salarié repreneur (nullable).                |
| `status`           | `VARCHAR(50)`  | Statut actuel (`pending`, `validated`, `rejected`).         |
| `created_at`       | `DATETIME`     | Date et heure de création.                                  |
| `validated_at`     | `DATETIME`     | Date et heure de validation/refus (nullable).               |
| `validator_id`     | `INT UNSIGNED` | Identifiant du manager validant/refusant (nullable).        |


## Étape 2 : Configuration du fichier .env
Créer un fichier .env à partir de l'exemple fourni.
```bash
cp .env.example .env
```
Adapter les valeurs suivantes :
```bash
DB_USER=root
DB_PASS=votre_mot_de_passe
```


##  Étape 3 : Exemples d'utilisation des api
### Listing complet
```bash
curl http://localhost:8000/api/swap_shift.php
```
### show d'une demande
```bash
curl http://localhost:8000/api/swap_shift.php?id=1
```
### Création d'une nouvelle demande
```bash
curl -X POST http://localhost:8000/api/swap_shift.php \
-H "Content-Type: application/json" \
-d '{
  "post_id": 1,
  "requester_id": 123
}'
```
