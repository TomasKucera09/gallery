# PhotoGallery CMS

Moderní, bezpečný a univerzální systém pro správu fotogalerie postavený na PHP, MySQL a Tailwind CSS.

## 🚀 Funkce

- **Moderní design** s Tailwind CSS a responzivním rozhraním
- **Bezpečnost** - CSRF ochrana, rate limiting, GDPR compliance
- **Automatické zpracování obrázků** - konverze do WebP, odstranění EXIF
- **Admin rozhraní** - nahrávání, správa a organizace fotek
- **Vícejazyčnost** - podpora češtiny a angličtiny
- **Konfigurovatelnost** - barvy, logo, navbar, zapnutí/vypnutí stránek
- **Desktopová aplikace** - instalátor a administrační rozhraní

## 📋 Požadavky

- PHP 8.0+
- MySQL 5.7+ nebo MariaDB 10.2+
- Web server (Apache/Nginx)
- Rozšíření PHP: GD, mysqli, mbstring

## 🛠️ Instalace

### Rychlá instalace (doporučeno)

1. Stáhněte si nejnovější release z [GitHub Releases](https://github.com/yourusername/photogallery-cms/releases)
2. Spusťte `PhotoGallery-Installer.exe` (Windows)
3. Postupujte podle průvodce instalací

### Manuální instalace

1. Naklonujte repozitář:
```bash
git clone https://github.com/yourusername/photogallery-cms.git
cd photogallery-cms
```

2. Zkopírujte `.env.example` na `.env` a upravte nastavení:
```bash
cp .env.example .env
```

3. Vytvořte databázi a upravte `.env`:
```env
DB_HOST=localhost
DB_USER=your_username
DB_PASS=your_password
DB_NAME=photogallery
```

4. Nahrajte soubory na web server

5. Spusťte instalátor v prohlížeči: `http://yourdomain.com/install.php`

## ⚙️ Konfigurace

### Základní nastavení

Vytvořte `.env` soubor s následujícími proměnnými:

```env
# Databáze
DB_HOST=localhost
DB_USER=your_username
DB_PASS=your_password
DB_NAME=photogallery

# Aplikace
APP_NAME="Moje Fotogalerie"
APP_URL=http://yourdomain.com
APP_LANG=cs
APP_TIMEZONE=Europe/Prague

# Bezpečnost
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your_secure_password
SECRET_KEY=your_random_secret_key

# FTP/SSH (pro desktopovou aplikaci)
FTP_HOST=your_ftp_host
FTP_USER=your_ftp_user
FTP_PASS=your_ftp_password
FTP_PATH=/public_html/
```

### Vlastní vzhled

Upravte `config/appearance.php`:

```php
<?php
return [
    'theme' => 'light', // light, dark, auto
    'primary_color' => '#3B82F6',
    'secondary_color' => '#1F2937',
    'logo' => 'data/logo.png',
    'navbar_style' => 'transparent', // transparent, solid, glass
    'enabled_pages' => [
        'index' => true,
        'gallery' => true,
        'photogallery' => true,
        'about' => false
    ]
];
```

## 📱 Desktopová aplikace

Desktopová aplikace poskytuje:

- **Instalátor** - průvodce instalací
- **Správa galerie** - nahrávání, organizace fotek
- **Konfigurace** - úprava vzhledu a nastavení
- **Deploy** - nasazení na hosting přes FTP/SFTP/SSH
- **Aktualizace** - automatické stahování z GitHubu

### Sestavení desktopové aplikace

```bash
cd exe/
npm install
npm run build
```

## 🔒 Bezpečnost

- **CSRF ochrana** na všech formulářích
- **Rate limiting** pro prevenci DDoS útoků
- **GDPR compliance** - cookie lišta, anonymizace EXIF dat
- **Bezpečné nahrávání** - validace typů souborů
- **Admin autentifikace** s hashováním hesel

## 📸 Podporované formáty

- **Vstupní**: JPG, PNG, WebP, GIF, BMP
- **Výstupní**: WebP (automatická konverze)
- **EXIF data**: Automaticky odstraněna pro soukromí

## 🌐 Vícejazyčnost

Systém podporuje češtinu a angličtinu. Překlady najdete v `lang/` složce.

## 🚀 Nasazení

### FTP/SFTP
```bash
# Použijte desktopovou aplikaci nebo
lftp -c "open -u username,password ftp.yourhost.com; mirror -R . /public_html/"
```

### SSH
```bash
rsync -avz --exclude='.git' --exclude='node_modules' ./ user@yourhost.com:/var/www/html/
```

## 📝 Changelog

Viz [CHANGELOG.md](CHANGELOG.md) pro kompletní historii změn.

## 🤝 Přispívání

1. Fork repozitáře
2. Vytvořte feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit změn (`git commit -m 'Add some AmazingFeature'`)
4. Push do branch (`git push origin feature/AmazingFeature`)
5. Otevřete Pull Request

## 📄 Licence

Tento projekt je licencován pod MIT licencí - viz [LICENSE](LICENSE) soubor pro detaily.

## 🆘 Podpora

- **Issues**: [GitHub Issues](https://github.com/yourusername/photogallery-cms/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/photogallery-cms/discussions)
- **Wiki**: [GitHub Wiki](https://github.com/yourusername/photogallery-cms/wiki)

