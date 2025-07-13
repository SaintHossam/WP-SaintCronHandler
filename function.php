<?php 

// <!-- كود لحذف المهام المكتملة دفعة واحدة -->
// Delete completed actions in bulk once on admin init
add_action('admin_init', 'delete_completed_actions_once');
function delete_completed_actions_once() {
    if (!current_user_can('manage_options')) return;

    if (!class_exists('ActionScheduler_Store')) return;

    $store = ActionScheduler_Store::instance();

    $completed = $store->query_actions([
        'status'   => 'complete',
        'per_page' => 1000, // عدّل الرقم لو العدد كبير // Adjust this number if you have a large count
    ]);

    foreach ($completed as $action_id) {
        $store->delete_action($action_id);
    }

    error_log("✅ تم حذف " . count($completed) . " من المهام المكتملة.");
}


/************************************************************************/
/* for cron (RUN) */
// إضافة جدول مخصص كل 5 دقائق
// Add a custom schedule every 5 minutes
add_filter('cron_schedules', 'custom_cron_intervals');
function custom_cron_intervals($schedules) {
    $schedules['every_five_minutes'] = array(
        'interval' => 300,
        'display'  => __('كل 5 دقائق'),
    );
    return $schedules;
}

// جدولة الحدث عند أول تحميل
// Schedule the cron event on the first load
if (!wp_next_scheduled('process_pending_actions')) {
    wp_schedule_event(time(), 'every_five_minutes', 'process_pending_actions');
}

// الكولباك اللي بينفذ المهام المعلقة
// Callback to process pending actions
add_action('process_pending_actions', 'run_pending_actions');
function run_pending_actions() {
    if (!class_exists('ActionScheduler_Store') || !class_exists('ActionScheduler_QueueRunner')) {
        error_log('❌ ActionScheduler classes not found.');
        return;
    }

    $store  = ActionScheduler_Store::instance();
    $runner = new ActionScheduler_QueueRunner();

    $actions = $store->query_actions([
        'status'   => 'pending',
        'per_page' => 100,
    ]);

    if (empty($actions)) {
        error_log('ℹ️ لا توجد مهام معلقة حالياً.');
        return;
    }

    foreach ($actions as $action_id) {
        try {
            $runner->process_action($action_id, 'Auto Cron');
            error_log("✅ تم تنفيذ المهمة تلقائيًا ID: $action_id");
        } catch (Exception $e) {
            error_log("❌ خطأ في تنفيذ المهمة ID: $action_id - " . $e->getMessage());
        }
    }
}
