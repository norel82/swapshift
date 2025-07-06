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

