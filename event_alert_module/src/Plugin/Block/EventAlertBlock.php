<?php

namespace Drupal\event_alert_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\event_alert_module\Service\EventDateService;
use Drupal\node\Entity\Node;

/**
 * Provides an 'Event Alert' Block.
 *
 * @Block(
 *   id = "event_alert_block",
 *   admin_label = @Translation("Event Alert Block"),
 * )
 */
class EventAlertBlock extends BlockBase implements ContainerFactoryPluginInterface
{

    /**
     * The event date service.
     *
     * @var \Drupal\event_alert_module\Service\EventDateService
     */
    protected $eventDateService;

    /**
     * Constructs the EventAlertBlock.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin ID for the block.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\event_alert_module\Service\EventDateService $event_date_service
     *   The event date service.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDateService $event_date_service)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->eventDateService = $event_date_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('event_alert_module.event_date_service') // Correctly inject the service.
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        // Default message if the block is not on an event page.
        $response = [
            '#markup' => $this->t('This block is only available on event pages.'),
            '#cache' => [
                'max-age' => 0, // Disable caching for this block.
            ],
        ];

        // Load the current node from the route.
        $node = \Drupal::routeMatch()->getParameter('node');

        // Ensure the node is an event type node.
        if ($node instanceof Node && $node->getType() === 'event') {
            $message = $this->t('This event does not have a date value.');
            $event_date_field = 'field_date';

            // Check if the event node has a date field with a value.
            if ($node->hasField($event_date_field) && !$node->get($event_date_field)->isEmpty()) {
                $event_date_value = $node->get($event_date_field)->value;

                // Use the service to get the message.
                $message = $this->eventDateService->getEventMessage($event_date_value);
            }

            $response['#markup'] = $message;
        }

        return $response;
    }
}
