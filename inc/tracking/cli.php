<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

// No REST/debug URL. Loaded only in an authenticated server-shell WP-CLI process.
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('bexstar tracking-test-kqd', function($args, $assoc) {
        if (KqdSettings::value('BEXSTAR_KQD_LIVE_TEST_ENABLED') !== '1' || count($args)!==1 || !isset($assoc['approved'])) {
            \WP_CLI::error('Controlled tracking test is disabled or explicit approval is missing.');
        }
        // Limit one invocation to at most eleven six-second requests. No persistence or logging.
        try {
            KqdSettings::credentials();
            $result=(new KqdAdapter(new WordPressKqdTransport()))->fetch(['bexstar_reference'=>$args[0]]);
            \WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            $error=$e instanceof TrackingError ? $e->publicError() : (new TrackingError('unavailable'))->publicError();
            \WP_CLI::error($error['message']);
        }
    });
}
