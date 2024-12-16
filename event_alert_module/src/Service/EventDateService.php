<?php

namespace Drupal\event_alert_module\Service;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides date-related utilities for the Event Alert module.
 */
class EventDateService
{

    /**
     * Get the message based on the given event date.
     *
     * @param string $event_date_value
     *   The event date in string format (Y-m-d H:i:s).
     *
     * @return string
     *   The appropriate message based on the event date.
     */
    public function getEventMessage($event_date_value)
    {
        // Parse the event date and current date.
        $event_date = new DrupalDateTime($event_date_value);
        $current_date = new DrupalDateTime();

        // Calculate the difference in days.
        $difference = $current_date->diff($event_date)->days;
        $is_future = $event_date > $current_date;

        // Generate the appropriate message.
        if ($difference === 0) {
            return ('This event is happening today.');
        } elseif ($is_future) {
            return ($difference . ' days left until the event starts.');
        } else {
            return ('This event already passed.');
        }
    }
}
