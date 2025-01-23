Kanban Board app using Vue JS + Laravel

Initially planneed to host this with Docker but had issues with vue-router.

Commands to run:

Backend:
cd kanban-back
composer install
php artisan migrate (Set your own MySQL config in the .env)
php artisan serve

Frontend:
cd kanban-front
npm install
npm run dev
