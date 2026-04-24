# ExploreSphere
### Tourist Guide Web Application — Makola (25 km Radius)
**ITE2953 | Athiya MIF | E2320129 | University of Moratuwa**

## File Structure

exploresphere/
├── index.php           ← Homepage (hero, categories, featured)
├── places.php          ← All places listing + search + filter
├── place.php           ← Single place detail + map + add to plan
├── categories.php      ← All categories overview
├── planner.php         ← One-day visit planner
├── config/
│   └── db.php          ← Database connection
├── assets/
│   └── css/
│       └── style.css   ← Global styles
└── database/
    └── exploresphere.sql ← Database setup SQL

## Setup Instructions (XAMPP)

### Step 1 — Copy Files
Place the `exploresphere` folder inside:
C:\xampp\htdocs\exploresphere\

### Step 2 — Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### Step 3 — Create Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Create a database named: `exploresphere`
4. Click the database → go to **"Import"** tab
5. Choose file: `exploresphere/database/exploresphere.sql`
6. Click **"Go"** to import

### Step 4 — Run the App
Open your browser and visit:
http://localhost/exploresphere/

## Pages

| Page | URL | Description |
|------|-----|-------------|
| Home | `/index.php` | Hero, stats, categories, featured places |
| Explore | `/places.php` | All places, search, category filter |
| Place Detail | `/place.php?id=1` | Full info, map, add to plan |
| Categories | `/categories.php` | Browse by category |
| Planner | `/planner.php` | One-day visit planner |


## Tech Stack
- **Frontend:** HTML5, CSS3
- **Backend:** PHP 8+
- **Database:** MySQL (via XAMPP)
- **Maps:** Google Maps Embed API (no API key needed for embeds)
- **Fonts:** Google Fonts (Playfair Display, DM Sans, Space Mono)
  
- ## 🔗 GitHub Repository
Project source code:  
https://github.com/athiyaimthiyas6-cloud/Exploresphere-tourist-application

## Features
- [x] 10+ tourist places within 25 km of Makola
- [x] 6 categories (Religious, Nature, Historical, Entertainment, Food, Leisure)
- [x] MySQL database storage and retrieval
- [x] Detailed place information (hours, fees, distance, description)
- [x] Google Maps integration on each place page
- [x] One-day visit planner with reorder & remove
- [x] Search functionality
- [x] Category filtering
- [x] Responsive design
- [x] Printable day plan



*© 2026 Athiya MIF · ExploreSphere · ITE2953*

