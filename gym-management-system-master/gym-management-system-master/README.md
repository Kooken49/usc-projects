# Gym Management System

## Overview
The Gym Management System is a web application built using HTML, CSS, and PHP. This project allows gym administrators to manage memberships, track attendance, and handle payments effectively.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Setting Up GitHub Integration](#setting-up-github-integration-with-vscode)
- [Running the Application](#running-the-application)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)

## Prerequisites
Before you begin, ensure you have the following software installed on your system:

1. **XAMPP** - A free and open-source cross-platform web server solution stack package.
   - [Download XAMPP](https://www.apachefriends.org/index.html)

2. **MySQL** - A relational database management system.
   - MySQL is included with XAMPP, so installing XAMPP will install MySQL as well.

3. **Visual Studio Code (VSCode)** - A source-code editor.
   - [Download VSCode](https://code.visualstudio.com/)

4. **Git** - A version control system to manage your source code.
   - [Download Git](https://git-scm.com/)

## Installation

1. **Clone the Repository**:
   Open your terminal or command prompt and run:
   ```bash
   git clone https://github.com/yourusername/gym-management-system.git
   ```
   Replace `yourusername` with your GitHub username.

2. **Navigate to the Project Directory**:
   ```bash
   cd gym-management-system
   ```

3. **Set Up the Database**:
   - Open XAMPP Control Panel and start the Apache and MySQL modules.
   - Open your web browser and navigate to `http://localhost/phpmyadmin`.
   - Create a new database named `gym_management`.
   - Import the SQL files if provided (found in the `database` folder) using the "Import" tab.

## Setting Up GitHub Integration with VSCode

1. **Open Visual Studio Code**.
2. **Install the Git Extension** (if not already installed):
   - Go to Extensions (`Ctrl + Shift + X`) and search for "Git".
   - Install the Git extension (it usually comes pre-installed).

3. **Configure Git**:
   - Open the terminal in VSCode and run the following commands to set your Git username and email:
     ```bash
     git config --global user.name "Your Name"
     git config --global user.email "your_email@example.com"
     ```

4. **Initialize Git Repository** (if not done during cloning):
   ```bash
   git init
   ```

5. **Link Your Local Repository to GitHub**:
   - Run the following command to add the remote repository:
     ```bash
     git remote add origin https://github.com/yourusername/gym-management-system.git
     ```

6. **Pull Changes from Remote Repository**:
   - If needed, run:
     ```bash
     git pull origin main
     ```

## Running the Application
1. Place the project folder (`gym-management-system`) in the `htdocs` directory of your XAMPP installation. This is usually found in `C:\xampp\htdocs\` on Windows.

2. Open your web browser and navigate to:
   ```
   http://localhost/gym-management-system
   ```

## Technologies Used
- HTML
- CSS
- PHP
- MySQL
