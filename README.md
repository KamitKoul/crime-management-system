# 🛡️ CrimeWatch - Crime Management System

CrimeWatch is a modern, web-based platform designed to bridge the gap between citizens and law enforcement. It allows for efficient incident reporting, real-time case tracking, and streamlined investigation management.

## 🚀 Key Features

### 👤 Citizen Portal
- **Incident Reporting:** Submit crime or vehicle-related incidents with detailed descriptions.
- **Geo-Tagging:** Automated location capture using GPS coordinates for precise incident mapping.
- **Evidence Upload:** Securely attach photos and videos to reports.
- **Real-time Tracking:** Monitor the status of reported cases from "Pending" to "Closed" via a modern dashboard.

### 👮 Officer Portal
- **Case Management:** View and manage assigned investigations.
- **Investigation Notes:** Add progress notes and findings directly to case files.
- **Status Updates:** Update case status (e.g., "Investigating", "Closed") to keep citizens informed.

### 🛠️ Admin Dashboard
- **Case Assignment:** Dispatch pending reports to the nearest or most appropriate officer.
- **System Overview:** Track total incidents, active officers, and pending actions via a centralized hub.
- **Search & Filter:** Quickly find cases by ID, title, or status.

## 💻 Tech Stack
- **Backend:** PHP (8.x recommended)
- **Database:** MySQL
- **Frontend:** HTML5, Modern CSS (Inter Font, Responsive Grid Layouts)
- **Tools:** XAMPP / Apache

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/KamitKoul/crime-management-system.git
   ```

2. **Database Setup:**
   - Open **phpMyAdmin**.
   - Create a new database named `crime_management`.
   - Import the provided SQL structure (if available) or use the initialization scripts provided in the project root.

3. **Configure Connection:**
   - Open `config/db.php`.
   - Update the credentials (`host`, `user`, `password`, `dbname`) to match your local XAMPP/MySQL environment.

4. **Run the Application:**
   - Move the project folder to your `htdocs` directory.
   - Start Apache and MySQL in XAMPP.
   - Access the system at `http://localhost/crime-management-system`.

## 📂 Project Structure
- `/admin` - Admin dashboard and resource management.
- `/officer` - Officer-specific case management views.
- `/user` - Citizen reporting and tracking dashboard.
- `/api` - Backend logic for reporting, assignment, and updates.
- `/auth` - Login, Registration, and Session management.
- `/uploads` - Storage for uploaded evidence files.

## 📝 License
This project is for educational/community purposes. All rights reserved.
