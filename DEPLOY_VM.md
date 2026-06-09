# Deploy ke VM

Target port aplikasi: `8120`.

## 1. Siapkan source

```bash
sudo mkdir -p /var/www/ppa-api-old
sudo chown -R $USER:www-data /var/www/ppa-api-old
cd /var/www/ppa-api-old
git pull
```

Jika deploy via upload zip, ekstrak project ke `/var/www/ppa-api-old`.

## 2. Install dependency

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

## 3. Siapkan `.env`

```bash
cp deploy/env.production.example .env
php artisan key:generate
php artisan jwt:secret
```

Edit `.env`, minimal isi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://YOUR_VM_IP:8120
DB_HOST=127.0.0.1
DB_DATABASE=new_itportal
DB_USERNAME=ppa_api
DB_PASSWORD=change_me
```

## 4. Permission Laravel

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache
```

## 5. Database dan cache production

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. Nginx port 8120

```bash
sudo cp deploy/nginx/ppa-api.conf /etc/nginx/sites-available/ppa-api
sudo ln -s /etc/nginx/sites-available/ppa-api /etc/nginx/sites-enabled/ppa-api
sudo nginx -t
sudo systemctl reload nginx
```

Pastikan firewall VM membuka port:

```bash
sudo ufw allow 8120/tcp
```

Aplikasi akan tersedia di:

```text
http://YOUR_VM_IP:8120
```

## 7. Queue worker

```bash
sudo cp deploy/systemd/ppa-api-queue.service /etc/systemd/system/ppa-api-queue.service
sudo systemctl daemon-reload
sudo systemctl enable --now ppa-api-queue
sudo systemctl status ppa-api-queue
```

## Catatan

- Template Nginx memakai socket `php8.2-fpm`: `/run/php/php8.2-fpm.sock`.
- Kalau VM memakai PHP versi lain, sesuaikan `fastcgi_pass` di `deploy/nginx/ppa-api.conf`.
- Setelah mengubah `.env` di production, jalankan `php artisan config:cache` ulang.
