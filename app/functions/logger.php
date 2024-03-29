<?php
use TriTan\Common\Container as c;
use TriTan\Common\Options\Options;
use TriTan\Common\Options\OptionsMapper;
use TriTan\Common\Context\HelperContext;
use TriTan\Common\Date;
use TriTan\Common\Mailer;
use Qubus\Hooks\ActionFilterHook;

/**
 * Qubus CMS Logging Functions
 *
 * @license GPLv3
 *
 * @since 1.0.0
 * @package Qubus CMS
 * @author Joshua Parker <josh@joshuaparker.blog>
 */
use Cascade\Cascade;

$qudb = app()->qudb;

$config = [
    'version' => 1,
    'disable_existing_loggers' => false,
    'formatters' => [
        'spaced' => [
            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'include_stacktraces' => true
        ],
        'dashed' => [
            'format' => "%datetime%-%channel%.%level_name% - %message% - %context% - %extra%\n"
        ],
        'exception' => [
            'format' => "[%datetime%] %message% %context% %extra%\n",
            'include_stacktraces' => true
        ]
    ],
    'handlers' => [
        'console' => [
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'DEBUG',
            'formatter' => 'exception',
            'stream' => 'php://stdout'
        ],
        'info_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'INFO',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-info.txt'
        ],
        'error_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'ERROR',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-error.txt'
        ],
        'notice_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'NOTICE',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-notice.txt'
        ],
        'critical_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'CRITICAL',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-critical.txt'
        ],
        'alert_file_handler' => [
            'class' => '\TriTan\MailHandler',
            'level' => 'ALERT',
            'formatter' => 'exception',
            'mailer' => new Mailer(),
            'message' => 'This message will be replaced with the real one.',
            'email_to' => ActionFilterHook::getInstance()->applyFilter(
                'system_alert_email',
                (
                    new Options(
                        new OptionsMapper(
                            $qudb,
                            new HelperContext()
                        )
                    )
                )->read('admin_email')
            ),
            'subject' => esc_html__('Qubus CMS System Alert!')
        ]
    ],
    'processors' => [
        'tag_processor' => [
            'class' => 'Monolog\Processor\TagProcessor'
        ]
    ],
    'loggers' => [
        'info' => [
            'handlers' => ['console', 'info_file_handler']
        ],
        'error' => [
            'handlers' => ['console', 'error_file_handler']
        ],
        'notice' => [
            'handlers' => ['console', 'notice_file_handler']
        ],
        'critical' => [
            'handlers' => ['console', 'critical_file_handler']
        ],
        'system_email' => [
            'handlers' => ['console', 'alert_file_handler']
        ]
    ]
];

Cascade::fileConfig(ActionFilterHook::getInstance()->applyFilter('monolog_cascade_config', $config));

/**
 * Default Error Handler
 *
 * Sets the default error handler to handle
 * PHP errors and exceptions.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 */
function ttcms_error_handler($type, $string, $file, $line)
{
    $logger = _ttcms_logger();
    $logger->logError($type, $string, $file, $line);
}

/**
 * Write Activity Logs to Database.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 */
function ttcms_logger_activity_log_write($action, $process, $record, $uname)
{
    $logger = _ttcms_logger();
    $logger->writeLog($action, $process, $record, $uname);
}

/**
 * Purges the error log of old records.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 */
function ttcms_logger_error_log_purge()
{
    $logger = _ttcms_logger();
    $logger->purgeErrorLog();
}

/**
 * Purges the activity log of old records.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 */
function ttcms_logger_activity_log_purge()
{
    $logger = _ttcms_logger();
    $logger->purgeActivityLog();
}

/**
 * Custom error log function for better PHP logging.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 * @param string $name      Log channel and log file prefix.
 * @param string $message   Message printed to log.
 */
function ttcms_monolog($name, $message)
{
    $log = new \Monolog\Logger(trim($name));
    $log->pushHandler(
        new \Monolog\Handler\StreamHandler(
            c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . trim($name) . '.' . (
                new Date()
            )->format('m-d-Y', 'now') . '.txt'
        )
    );
    $log->addError($message);
}

/**
 * Set the system environment.
 *
 * @file app/functions/logger.php
 *
 * @since 1.0.0
 */
function ttcms_set_environment()
{
    /**
     * Error log setting
     */
    if (APP_ENV == 'DEV') {
        /**
         * Print errors to the screen.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'On');
    } else {
        /**
         * Log errors to a file.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set(
            'error_log',
            c::getInstance()->get('site_path') . 'files' . DS . 'logs' . DS . 'ttcms-error-' . (
            new Date()
            )->format('Y-m-d', 'now') . '.txt'
        );
        set_error_handler('ttcms_error_handler', E_ALL & ~E_NOTICE);
    }
}
