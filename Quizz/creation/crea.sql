CREATE TABLE quizzes (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    question_type ENUM('QCM', 'Vrai/Faux', 'Ouverte') NOT NULL,
    option1 TEXT NULL,
    option2 TEXT NULL,
    option3 TEXT NULL,
    correct_option INT NULL,
    formatted_answer TEXT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
);

ALTER TABLE `users` ADD `score` INT NULL DEFAULT NULL AFTER `active`;

ALTER TABLE questions ADD qcm_rep INT NULL;