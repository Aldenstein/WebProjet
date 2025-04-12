CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(50) NOT NULL,
    option1 VARCHAR(255) DEFAULT NULL,
    option2 VARCHAR(255) DEFAULT NULL,
    option3 VARCHAR(255) DEFAULT NULL,
    correct_option VARCHAR(255) NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

ALTER TABLE `users` CHANGE `score` `score` INT(11) NULL DEFAULT '0';

ALTER TABLE `users` ADD `score` INT NULL DEFAULT NULL AFTER `active`;

INSERT INTO quizzes (title) VALUES 
('Culture Générale'),
('Mathématiques de Base'),
('Informatique');

INSERT INTO questions (quiz_id, question_text, question_type, option1, option2, option3, correct_option) VALUES
(1, 'Quelle est la capitale de la France ?', 'QCM', 'Paris', 'Lyon', 'Marseille', '1'),
(1, 'La terre est-elle plate ?', 'Vrai/Faux', NULL, NULL, NULL, 'Faux'),
(1, 'Citez un monument célèbre à Paris.', 'Ouverte', NULL, NULL, NULL, 'Tour Eiffel');

INSERT INTO questions (quiz_id, question_text, question_type, option1, option2, option3, correct_option) VALUES
(2, 'Combien font 7 + 5 ?', 'QCM', '10', '12', '14', '12'),
(2, 'Zéro est un nombre pair.', 'Vrai/Faux', NULL, NULL, NULL, 'Vrai'),

INSERT INTO questions (quiz_id, question_text, question_type, option1, option2, option3, correct_option) VALUES
(3, 'Quel langage est utilisé pour créer des pages web ?', 'QCM', 'HTML', 'Python', 'C++', '2'),
(3, 'Un ordinateur ne peut fonctionner sans système d’exploitation.', 'Vrai/Faux', NULL, NULL, NULL, 'Vrai'),
(3, 'Citez un système d’exploitation open source.', 'Ouverte', NULL, NULL, NULL, 'Linux');

ALTER TABLE `questions` CHANGE `question_text` `question_text` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `question_type` `question_type` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `option1` `option1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `option2` `option2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `option3` `option3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `correct_option` `correct_option` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
