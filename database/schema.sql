-- Create the database (optional - you may have already created it)
CREATE DATABASE IF NOT EXISTS lumenpoledb;
USE lumenpoledb;

-- 1. USERS TABLE - Store voter information
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. ELECTIONS TABLE - Store election information
CREATE TABLE IF NOT EXISTS elections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
    start_date DATETIME,
    end_date DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. CANDIDATES TABLE - Store candidates for each election
CREATE TABLE IF NOT EXISTS candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    position VARCHAR(100),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_candidate (election_id, name)
);

-- 4. VOTES TABLE - Store individual votes
CREATE TABLE IF NOT EXISTS votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (election_id, user_id)
);

-- Create indexes for better performance
CREATE INDEX idx_election_status ON elections(status);
CREATE INDEX idx_election_user ON elections(created_by);
CREATE INDEX idx_candidate_election ON candidates(election_id);
CREATE INDEX idx_vote_election ON votes(election_id);
CREATE INDEX idx_vote_user ON votes(user_id);