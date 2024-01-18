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
 * CLI script for mod_competet.
 *
 * @package   mod_competvet
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'name' => null,
], [
    'h' => 'help',
]);

$possiblefunctions = ['create_update_roles', 'setup_update_tags', 'create_reports', 'update_all_capabilities'];

$usage = "Run different setup script for testing purpose

Usage:
    # php setups.php --name=<functionname>
    # php setups.php [--help|-h]

Options:
    -h --help                   Print this help.
    --name=<frankenstyle> (" . implode(',', $possiblefunctions) . ")       Name of the function to test/run";

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL . '  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($usage);
    exit(2);
}

if ($options['name'] === null) {
    $options['name'] = $possiblefunctions[0];
}

if (in_array($options['name'], $possiblefunctions)) {
    \mod_competvet\setup::{$options['name']}();
} else {
    print ('Called function not in the list (' . implode(',', $possiblefunctions) . ')');
}
