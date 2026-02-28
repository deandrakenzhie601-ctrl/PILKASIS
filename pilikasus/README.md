# Pilkasis - A Minimal PHP OOP Voting App

A simple voting application built with PHP OOP principles.

## Setup Instructions

### 1. Create MySQL Database
```sql
CREATE DATABASE pilkasis;
```

### 2. Run Seed Script
From terminal/command prompt in the project root:
```bash
php scripts/seed.php
```

This will:
- Create necessary tables (users, candidates, votes)
- Seed default user accounts
- Seed sample candidates

### 3. Configure Database (if needed)
Edit `config/Database.php` if your MySQL credentials are different:
```php
private $db_user = 'root';
private $db_pass = '';
```

### 4. Run PHP Server
```bash
php -S localhost:8000 -t public
```

### 5. Access Application
Open your browser and go to:
```
http://localhost:8000
```

## Default Accounts

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Voter Accounts:**
- Username: `siswa1` / Password: `siswa123`
- Username: `siswa2` / Password: `siswa123`
- Username: `siswa3` / Password: `siswa123`

## Features

✓ User Authentication (Login)
✓ Voting System (One vote per user)
✓ Real-time Vote Count
✓ Voting Results Display
✓ OOP Architecture
✓ PDO Database Connections
✓ Responsive UI

## Folder Structure

```
pilikasus/
├── config/          # Database configuration
├── src/             # Core classes (User, Candidate, Vote)
├── scripts/         # Seed script for database
├── public/          # Main application file
├── views/           # View templates (for future expansion)
└── README.md        # This file
```

## Technologies Used

- PHP 7.4+
- MySQL
- HTML5 / CSS3
- PDO for database connection
