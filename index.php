<?php
/**
 * Front Controller - all requests are routed through here via .htaccess
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Session.php';
require_once __DIR__ . '/app/core/CSRF.php';
require_once __DIR__ . '/app/core/RateLimiter.php';
require_once __DIR__ . '/app/core/Validator.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/core/App.php';
require_once __DIR__ . '/app/helpers/helpers.php';

App::run();
