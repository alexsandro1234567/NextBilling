CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    version VARCHAR(20),
    is_active BOOLEAN DEFAULT FALSE,
    is_core BOOLEAN DEFAULT FALSE,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE module_dependencies (
    module_id INT,
    dependency_id INT,
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (dependency_id) REFERENCES modules(id)
); 