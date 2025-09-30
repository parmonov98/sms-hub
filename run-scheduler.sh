#!/bin/bash

# Laravel Scheduler Runner
# This script should be run every minute via cron

cd /var/www
php artisan schedule:run >> /var/www/storage/logs/scheduler.log 2>&1
