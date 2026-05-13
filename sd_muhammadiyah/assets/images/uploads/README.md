# Upload Directory

This directory stores all user-uploaded files for the SD Muhammadiyah 1 Gentasari CMS.

## Structure

```
uploads/
├── announcements/   ← Thumbnail images for announcements
├── achievements/    ← Photos for student achievements
├── complaints/      ← Attachments for complaints (pengaduan)
├── ekskul/          ← Photos for extracurricular activities
├── facilities/      ← Photos for school facilities
├── gallery/         ← Gallery images
├── general/         ← General purpose uploads (OG image, etc.)
├── heroes/          ← Hero/banner images
├── logos/           ← School logo
├── staff/           ← Staff photos
└── teachers/        ← Teacher photos
```

## Security

- PHP execution is **blocked** in this directory via `.htaccess`
- Only `jpg`, `jpeg`, `png`, `gif`, `webp`, `pdf` files are allowed
- Max file size: **5MB** per file
- All files are renamed on upload (random `img_xxx.ext` filename)
- Images are automatically resized to max 1400×1050px

## Permissions

Set directory permissions to `755` on the server:
```bash
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```

> ⚠️ **Never** upload executable files (.php, .sh, .py, etc.) to this directory.