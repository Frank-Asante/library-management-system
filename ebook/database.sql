CREATE DATABASE ebook_store;

USE ebook_store;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cover_image VARCHAR(255) NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO books (title, author, price, cover_image) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 9.99, 'https://via.placeholder.com/150'),
('1984', 'George Orwell', 12.99, 'https://via.placeholder.com/150'),
('Pride and Prejudice', 'Jane Austen', 8.99, 'https://via.placeholder.com/150'),
('To Kill a Mockingbird', 'Harper Lee', 11.99, 'https://via.placeholder.com/150');