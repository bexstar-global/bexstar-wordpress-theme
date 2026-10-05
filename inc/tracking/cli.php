<?php
namespace Bexstar\Tracking;
if (!defined('ABSPATH')) { exit; }

// Private server-shell command only. Global public tracking gates are never changed.
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('bexstar tracking-test-kqd',new KqdValidationCommand(),[
        'shortdesc'=>'Validate one approved, explicitly mapped KQD reference through TrackingService.',
        'synopsis'=>[
            ['type'=>'positional','name'=>'reference','optional'=>false],
            ['type'=>'flag','name'=>'approved','optional'=>false],
            ['type'=>'flag','name'=>'bypass-cache','optional'=>true],
        ],
    ]);
}
