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

declare(strict_types=1);

namespace mod_competvet\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\{column, filter};
use lang_string;

/**
 * Planning entity
 *
 * @package   mod_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planning extends base {
    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'competvet_planning' => 'plan',
            'competvet_situation' => 'situation',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('entity:planning', 'mod_competvet');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $planningalias = $this->get_table_alias('competvet_planning');

        $columns[] = (new column(
            'startdate',
            new lang_string('startdate', 'mod_competvet'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$planningalias}.startdate")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);
        $columns[] = (new column(
            'enddate',
            new lang_string('enddate', 'mod_competvet'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$planningalias}.enddate")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);
        $columns[] = (new column(
            'session',
            new lang_string('planning:session', 'mod_competvet'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$planningalias}.session")
            ->set_is_sortable(true);
        $columns[] = (new column(
            'groupid',
            new lang_string('planning:session', 'mod_competvet'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$planningalias}.groupid")
            ->set_is_sortable(true);
        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $planningalias = $this->get_table_alias('competvet_planning');

        $filters[] = (new filter(
            date::class,
            'startdate',
            new lang_string('startdate', 'mod_competvet'),
            $this->get_entity_name(),
            "{$planningalias}.startdate"
        ))->add_joins($this->get_joins());
        $filters[] = (new filter(
            date::class,
            'enddate',
            new lang_string('enddate', 'mod_competvet'),
            $this->get_entity_name(),
            "{$planningalias}.enddate"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'session',
            new lang_string('planning:session', 'mod_competvet'),
            $this->get_entity_name(),
            "{$planningalias}.session"
        ))->add_joins($this->get_joins());

        return $filters;
    }
}