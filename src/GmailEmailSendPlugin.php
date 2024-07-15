<?php
declare(strict_types=1);

namespace GmailEmailSend;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Database\TypeFactory;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\I18n\Middleware\LocaleSelectorMiddleware;
use Cake\Log\Log;
use Cake\Mailer\TransportFactory;
use Cake\Routing\RouteBuilder;
use GmailEmailSend\Database\Type\EncryptedType;
use GmailEmailSend\Mailer\Transport\GmailApiTransport;
use GmailEmailSend\Model\Table\GmailAuthTable;
use GmailEmailSend\Service\GmailAuth;
use League\Container\ReflectionContainer;

/**
 * Plugin for GmailEmailSend
 */
class GmailEmailSendPlugin extends BasePlugin
{
    /**
     * Load all the plugin configuration and bootstrap logic.
     *
     * The host application is provided as an argument. This allows you to load
     * additional plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        // DatabaseType config
        TypeFactory::map('encrypted', EncryptedType::class);

        // Load the Plugin configuration
        Configure::load('GmailEmailSend.gmail_email_send_config');

        // Logging
        Log::setConfig('email', Configure::consume('GmailEmailSend.emailLog'));

        // Email Transport Config
        TransportFactory::setConfig('gmailApi', [
            'className' => GmailApiTransport::class,
            'username' => 'toggen.yt@gmail.com',
        ],);
    }

    /**
     * Add routes for the plugin.
     *
     * If your plugin has many routes and you would like to isolate them into a separate file,
     * you can create `$plugin/config/routes.php` and delete this method.
     *
     * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
     * @return void
     */
    public function routes(RouteBuilder $routes): void
    {
        $routes->plugin(
            'GmailEmailSend',
            ['path' => '/gmail'],
            function (RouteBuilder $builder): void {
                // Add custom routes here
                $builder->connect(
                    '/',
                    ['controller' => 'Auth', 'action' => 'index']
                );
                $builder->connect(
                    '/view/{id}',
                    ['controller' => 'Auth', 'action' => 'view'],
                    ['id' => '\d+', 'pass' => ['id']]
                );

                $builder->connect(
                    '/test/{id}',
                    ['controller' => 'Auth', 'action' => 'test',],
                    ['id' => '\d+', 'pass' => ['id']]
                );
                $builder->connect(
                    '/get-token',
                    ['controller' => 'Auth', 'action' => 'getToken']
                );
                $builder->connect(
                    '/code',
                    ['controller' => 'Auth', 'action' => 'code']
                );
                $builder->connect(
                    '/change-credentials/{id}',
                    ['controller' => 'Auth', 'action' => 'changeCredentials'],
                    ['id' => '\d+', 'pass' => ['id']]
                );
                $builder->connect(
                    '/delete/{id}',
                    ['controller' => 'Auth', 'action' => 'delete'],
                    ['id' => '\d+', 'pass' => ['id']]
                );
            }
        );
        parent::routes($routes);
    }

    /**
     * Add middleware for the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to update.
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // Add your middlewares here

        $middlewareQueue->add(new LocaleSelectorMiddleware(['en_AU', 'en_US']));

        return $middlewareQueue;
    }

    /**
     * Add commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Add your commands here

        $commands = parent::console($commands);

        return $commands;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
        $container->delegate(
            new ReflectionContainer(Configure::read('debug'))
        );

        // Add your services here
        $container->add(GmailAuth::class)
            ->addArgument(ServerRequest::class)
            ->addArgument(GmailAuthTable::class);
    }
}
