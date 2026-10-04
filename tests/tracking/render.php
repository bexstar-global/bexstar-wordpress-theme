<?php
/** Render synthetic API/UI fixture for local browser testing only. */
if (!defined('BEXSTAR_TRACKING_TEST') || PHP_SAPI!=='cli') { exit; }
ob_start();require __DIR__.'/api.php';ob_end_clean();
putenv('BEXSTAR_TRACKING_API_ENABLED=1');
echo json_encode(['html'=>bexstar_render_tracking_page(),'outputs'=>$GLOBALS['api_contract_outputs']],JSON_THROW_ON_ERROR);
