<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains class used to prepare a popup notification for display.
 *
 * @package   message_popup
 * @copyright 2016 Ryan Wyllie <ryan@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace message_popup\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');

use renderable;
use templatable;
use moodle_url;
use core_user;

/**
 * Class to prepare a popup notification for display.
 *
 * @package   message_popup
 * @copyright 2016 Ryan Wyllie <ryan@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class popup_notification implements templatable, renderable {

    /**
     * @var \stdClass The notification.
     */
    protected $notification;

    /**
     * Constructor.
     *
     * @param \stdClass $notification
     */
    public function __construct($notification) {
        $this->notification = $notification;
    }

    public function export_for_template(\renderer_base $output) {
        $context = clone $this->notification;
        // Converse timestamp to text
        $timeDifference = time() - $context->timecreated;
        $formattedTime = '';
        if (time() - $context->timecreated < (24 * 60 * 60)) { // Check if time is less than 24 hours (in seconds)
            $hours = floor($timeDifference / 3600); // Convert seconds to hours
            $minutes = round(($timeDifference % 3600) / 60); // Convert remaining seconds to minutes
            if ($hours > 0) {
                $formattedTime = $hours . get_string('notihours', 'theme_moove');
                if ($minutes > 0) {
                    $formattedTime .= " " . $minutes . get_string('notiminutes', 'theme_moove');
                }
                $formattedTime .= get_string('notiago', 'theme_moove');
            } elseif ($minutes > 0) {
                $formattedTime .= $minutes . get_string('notiminutes', 'theme_moove') . get_string('notiago', 'theme_moove');
            } else {
                $formattedTime = "just now";
            }
        } else {
            $formattedTime = date("H:i d-m-Y", $context->timecreated);
        }
        $context->timecreatedpretty = $formattedTime;
        $context->text = message_format_message_text($context);
        $context->read = $context->timeread ? true : false;

        // Need to strip any HTML from these.
        $context->subject = clean_param($context->subject, PARAM_TEXT);
        $context->contexturlname = clean_param($context->contexturlname, PARAM_TEXT);
        $context->shortenedsubject = shorten_text($context->subject, 125);

        if (!empty($context->component) && substr($context->component, 0, 4) == 'mod_') {
            $iconurl = $output->image_url('monologo', $context->component);
        } else {
            $iconurl = $output->image_url('i/marker', 'core');
        }

        $context->iconurl = $iconurl->out();

        return $context;
    }
}
