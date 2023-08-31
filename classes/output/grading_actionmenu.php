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
namespace mod_competvet\output;

use templatable;
use renderable;
use moodle_url;

/**
 * Output the grading actionbar for this activity.
 *
 * @package    mod_competvet
 * @copyright  2023 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grading_actionmenu implements templatable, renderable {
    /**
     * Constructor for this object.
     *
     * @param int $cmid Course module ID.
     */
    public function __construct(protected int $cmid) {
    }

    /**
     * Data to render in a template.
     *
     * @param \renderer_base $output renderer base output.
     * @return array Data to render.
     */
    public function export_for_template(\renderer_base $output): array {
        return [
            'back' => (new moodle_url('/mod/competvet/view.php', ['id' => $this->cmid]))->out(false),
        ];
    }
}
