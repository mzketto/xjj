CREATE TABLE video_apis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  api_url TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(100) NOT NULL UNIQUE,
  value TEXT NOT NULL
);

INSERT INTO config (key_name, value) VALUES ('player_parse_url', 'https://slslplay.com/?url=');
