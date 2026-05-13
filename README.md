# SD Muhammadiyah 1 Gentasari — Modern School Profile & CMS

## Stack
- **Backend**: PHP Native (PDO, OOP/procedural hybrid)
- **Database**: MySQL 8+ / MariaDB 10.6+
- **Frontend**: Tailwind CSS CDN + custom CSS
- **Icons**: Lucide Icons (CDN)
- **Alerts**: SweetAlert2

## Folder Structure

```
sd_muhammadiyah/
│
├── index.php                  ← Public homepage (Hero + Navbar)
├── includes/
│   └── config.php             ← DB config, PDO singleton, helpers
│
├── pages/
│   ├── profile/
│   │   ├── sekolah.php        ← School profile page
│   │   ├── guru-staff.php     ← Teachers & staff listing
│   │   └── siswa.php          ← Students page
│   ├── media/
│   │   ├── galeri.php         ← Photo gallery
│   │   └── fasilitas.php      ← Facilities
│   ├── aktivitas/
│   │   ├── ekskul.php         ← Extracurricular
│   │   └── pengumuman.php     ← Announcements
│   └── interaksi/
│       ├── pengaduan.php      ← Complaints form
│       └── kontak.php         ← Contact form
│
├── admin/
│   ├── login.php              ← Admin login (glassmorphism)
│   ├── logout.php             ← Destroy session
│   ├── index.php              ← Dashboard with stats
│   ├── includes/
│   │   └── auth.php           ← Session guard middleware
│   └── pages/
│       ├── profile.php        ← Edit school profile
│       ├── teachers.php       ← CRUD teachers & staff
│       ├── students.php       ← CRUD student stats
│       ├── gallery.php        ← CRUD gallery
│       ├── facilities.php     ← CRUD facilities
│       ├── ekskul.php         ← CRUD extracurricular
│       ├── announcements.php  ← CRUD announcements
│       ├── complaints.php     ← View & respond complaints
│       ├── messages.php       ← View contact messages
│       ├── settings.php       ← Site settings
│       └── users.php          ← Admin user management
│
├── assets/
│   ├── css/                   ← Custom CSS overrides (production)
│   ├── js/                    ← Custom scripts
│   └── images/
│       └── uploads/           ← User-uploaded files (writable)
│
└── database/
    └── schema.sql             ← Full database schema + seeds
```

## Quick Start

### 1. Database Setup
```bash
mysql -u root -p < database/schema.sql
```

### 2. Configure Connection
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sd_muhammadiyah');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('APP_URL',  'http://localhost/sd_muhammadiyah');
```

### 3. Permissions
```bash
chmod 755 assets/images/uploads/
```

### 4. Default Admin Login
- URL: `/admin/login.php`
- Username: `admin`
- Password: `admin123`
- **⚠️ Change immediately after first login!**

## Design System

### Glassmorphism Classes
```css
.glass        /* bg-white/7, blur-16px, border-white/13 */
.glass-strong /* bg-white/14, blur-24px, border-white/22 */
.glass-dark   /* bg-black/35, blur-20px, border-white/8  */
```

### Colors
| Token        | Hex       | Usage                  |
|--------------|-----------|------------------------|
| gold-300     | #f0d898   | Text highlights        |
| gold-400     | #e8c860   | Icons, accents         |
| gold-500     | #d4aa3a   | Primary CTA buttons    |
| gold-600     | #b8921e   | Hover states           |

### Typography
- **Display**: Cormorant Garamond (headings, elegant text)
- **Body**: DM Sans (UI, labels, paragraphs)

## Security Features
- PDO prepared statements (no SQL injection)
- CSRF tokens on all forms
- bcrypt password hashing
- Session regeneration on login
- HTTPOnly session cookies
- Input sanitization via `e()` helper

## "Terbaru" Badge Logic
Announcements automatically receive a "Terbaru" badge if published
within the last 7 days, via the `isNew(string $datetime, int $days)` helper.
