CREATE DATABASE IF NOT EXISTS planning DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE planning;


DROP TABLE IF EXISTS swap_shift;

CREATE TABLE swap_shift (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    requester_id INT NOT NULL,
    receiver_id INT DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    validated_at DATETIME DEFAULT NULL,
    validator_id INT DEFAULT NULL
);

-- Insertion de données de test
-- 1. Demande acceptée
INSERT INTO swap_shift
(post_id, requester_id, receiver_id, status, created_at, validated_at, validator_id)
VALUES
(5, 1, 2, 'validated', '2025-07-06 10:00:00', '2025-07-07 09:00:00', 10);

-- 2. Demande refusée
INSERT INTO swap_shift
(post_id, requester_id, receiver_id, status, created_at, validated_at, validator_id)
VALUES
(3, 3, 4, 'rejected', '2025-07-05 14:00:00', '2025-07-06 15:30:00', 11);

-- 3. Demande vierge
INSERT INTO swap_shift
(post_id, requester_id, status, created_at)
VALUES
(2, 5, 'pending', '2025-07-06 12:00:00');