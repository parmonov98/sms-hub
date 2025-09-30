#!/bin/bash

# SMS Hub Scheduler
# This script runs the Laravel scheduler to check SMS delivery status

echo "Starting SMS Hub Scheduler..."
echo "This will run every minute to check SMS delivery status"
echo "Press Ctrl+C to stop"

# Run the scheduler every minute
while true; do
    echo "$(date): Running SMS delivery status check..."
    docker exec sms-hub-app php artisan schedule:run
    sleep 60
done
