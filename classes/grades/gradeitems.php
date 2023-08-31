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
namespace mod_competvet\grades;

use core_grades\component_gradeitems;
use core_grades\local\gradeitem\advancedgrading_mapping;
use core_grades\local\gradeitem\fieldname_mapping;
use core_grades\local\gradeitem\itemnumber_mapping;

/**
 * Grade item mappings for the activity.
 *
 * @package   mod_competvet
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradeitems implements itemnumber_mapping, fieldname_mapping, advancedgrading_mapping {

    /**
     * Return the list of grade item mappings for the assign.
     *
     * @return array
     */
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => 'eval',
            1 => 'list',
            2 => 'caselog',
        ];
    }

    /**
     * Get the list of advanced grading item names for this component.
     *
     * @return array
     */
    public static function get_advancedgrading_itemnames(): array {
        return [
            'eval',
            'list',
            'caselog',
        ];
    }

    /**
     * Get the suffixed field name for an activity field mapped from its itemnumber.
     *
     * For legacy reasons, the first itemnumber has no suffix on field names.
     *
     * @param string $component The component that the grade item belongs to
     * @param int $itemnumber The grade itemnumber
     * @param string $fieldname The name of the field to be rewritten
     * @return string The translated field name
     */
    public static function get_field_name_for_itemnumber(string $component, int $itemnumber, string $fieldname): string {
        $itemname = component_gradeitems::get_itemname_from_itemnumber($component, $itemnumber);

        if ($itemname) {
            return "{$itemname}{$fieldname}";
        }

        return $fieldname;
    }
}
