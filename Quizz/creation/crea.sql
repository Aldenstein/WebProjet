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

ALTER TABLE `users` ADD `score` INT NULL DEFAULT NULL AFTER `active`;