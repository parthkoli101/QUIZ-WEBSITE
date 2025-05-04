# QUIZ-WEBSITE
User Interactive Quiz Website

1) First create database to create tables:-
CREATE DATABASE PARTH;

2) To store the user login/signin details create the following table:-
CREATE TABLE quiz (
    firstname VARCHAR(50),
    lastname VARCHAR(50),
    username VARCHAR(90) NOT NULL PRIMARY KEY,
    password VARCHAR(50),
    confirmpassword VARCHAR(50)
);

3) To store quiz questions of different levels and categories create the following table:-
CREATE TABLE quiz_questions (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quiz_type VARCHAR(50),
    question VARCHAR(255),
    option_a VARCHAR(50),
    option_b VARCHAR(50),
    option_c VARCHAR(50),
    option_d VARCHAR(50),
    correct_option VARCHAR(50),
    quiz_level INT(11)
);

4) To store the results of the attempted quiz by the user create the following table:-
CREATE TABLE quizresults (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(90) NOT NULL,
    quiz_type VARCHAR(50) NOT NULL,
    attempts INT(11) DEFAULT 0,
    highest_score INT(11) DEFAULT 0,
    total_questions INT(11) NOT NULL,
    attempted_questions INT(11) DEFAULT 0,
    unattempted_questions INT(11) DEFAULT 0,
    wrong_answers INT(11) DEFAULT 0,
    average_score DECIMAL(5,2) DEFAULT 0.00,
    quiz_level INT(11),
    FOREIGN KEY (username) REFERENCES quiz(username)
);

5) To store the user feedback create the following table:-
CREATE TABLE feedback (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(90) NOT NULL,
    quizRating INT(11) NOT NULL,
    feedback TEXT NOT NULL,
    improvements TEXT NOT NULL,
    FOREIGN KEY (username) REFERENCES quiz(username)
);
