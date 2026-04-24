php artisan module:migrate Admission --fresh
php artisan module:migrate Admission --refresh
php artisan db:seed --class="Modules\Admission\database\seeders\DatabaseSeeder"
php artisan storage:link
./run-queue.sh
pm2 restart laravel-queue-ntd
php artisan optimize:clear