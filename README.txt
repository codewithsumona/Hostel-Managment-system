HOSTEL MEAL MANAGEMENT SYSTEM (HTML/CSS + PHP + MySQL)

1) Requirements
- XAMPP (Apache + MySQL)

2) Setup
A) Copy project folder
- Copy the whole folder "hostel-meal" into:
  C:\xampp\htdocs\
  So it becomes:
  C:\xampp\htdocs\hostel-meal\

B) Start services
- Open XAMPP Control Panel
- Start: Apache, MySQL

C) Import database
- Go to: http://localhost/phpmyadmin
- Click "Import"
- Choose this file:
  hostel-meal\sql\hostel_meal.sql
- Click "Go"

D) Test in browser
- Open: http://localhost/hostel-meal/

3) Login
Admin:
- username: admin
- password: admin123

Student demo:
- username: S-1001
- password: 12345

4) How it works (Meal Unit)
- Breakfast = 0.5 unit
- Lunch = 1 unit
- Dinner = 1 unit

Student creates "Meal OFF" request by date range and selecting meals.
If any request says OFF for a meal on a date, that meal is OFF.

5) Notes
- Database config is in: config/db.php
  Default XAMPP MySQL user: root, password: (empty)

- Admin -> Manage Students
  Adding student automatically creates a student login
  Default student password: 12345

