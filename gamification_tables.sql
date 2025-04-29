-- Donation Streaks and Badges Tables
CREATE TABLE IF NOT EXISTS donation_streaks (
  user_id INT NOT NULL,
  current_streak INT DEFAULT 0,
  longest_streak INT DEFAULT 0,
  last_donation_date DATE DEFAULT NULL,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_streaks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS badges (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  badge_type ENUM('donation', 'knowledge', 'referral') NOT NULL,
  icon VARCHAR(255) NOT NULL,
  requirement_count INT DEFAULT 1,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS user_badges (
  user_id INT NOT NULL,
  badge_id INT NOT NULL,
  earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, badge_id),
  CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Insert default badges
INSERT INTO badges (name, description, badge_type, icon, requirement_count) VALUES
('First Time Donor', 'Complete your first blood donation', 'donation', 'fa-award', 1),
('Regular Donor', 'Complete 5 blood donations', 'donation', 'fa-tint', 5),
('Hero Donor', 'Complete 10 blood donations', 'donation', 'fa-medal', 10),
('Lifesaver', 'Complete 25 blood donations', 'donation', 'fa-heart', 25),
('Novice Learner', 'Score at least 5 points in a knowledge game', 'knowledge', 'fa-book', 5),
('Blood Expert', 'Score at least 8 points in a knowledge game', 'knowledge', 'fa-graduation-cap', 8),
('Perfect Score', 'Score 10/10 in a knowledge game', 'knowledge', 'fa-star', 10),
('Helpful Friend', 'Refer 1 friend who completes a donation', 'referral', 'fa-user-friends', 1),
('Community Builder', 'Refer 5 friends who complete donations', 'referral', 'fa-users', 5),
('Donation Ambassador', 'Refer 10 friends who complete donations', 'referral', 'fa-certificate', 10),

-- New badges for Blood Cell Defenders game
('Cell Defender Rookie', 'Score at least 5 points in Blood Cell Defenders game', 'knowledge', 'cell_defender_rookie.svg', 5),
('Cell Defender Pro', 'Score at least 8 points in Blood Cell Defenders game', 'knowledge', 'cell_defender_pro.svg', 8),
('Cell Defender Master', 'Get a perfect score in Blood Cell Defenders game', 'knowledge', 'cell_defender_master.svg', 10),

-- New badges for Blood Word Guess game
('Word Guess Beginner', 'Score at least 5 points in Blood Word Guess game', 'knowledge', 'word_guess_beginner.svg', 5),
('Word Guess Expert', 'Score at least 8 points in Blood Word Guess game', 'knowledge', 'word_guess_expert.svg', 8),
('Word Guess Champion', 'Get a perfect score in Blood Word Guess game', 'knowledge', 'word_guess_champion.svg', 10);