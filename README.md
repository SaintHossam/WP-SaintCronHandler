# WP-SaintCronHandler

This file provides a custom WordPress cron job handler designed to enhance scheduled task execution, especially for WooCommerce and Action Scheduler-based plugins.

🔧 Features:
Registers a custom cron interval (every 5 minutes).

Automatically processes pending scheduled actions using ActionScheduler_QueueRunner.

Cleans up completed actions in bulk to reduce database bloat.

Includes basic logging via error_log() for monitoring and debugging.

🧩 Use Cases:
Optimizing WooCommerce webhook/task execution reliability.

Maintaining a cleaner Action Scheduler table by auto-deleting completed jobs.

Works well with server-level cron jobs for consistent background task execution.

💡 Notes:
Ideal for performance-heavy or high-traffic WooCommerce sites.

Can be placed in a custom plugin or the theme's functions.php.
