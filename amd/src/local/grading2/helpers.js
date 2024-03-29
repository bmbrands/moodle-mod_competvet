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
 * Small helper functions for the UI.
 *
 * @module     mod_competvet/local/grading2/helpers
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const gradingApp = document.querySelector('[data-region="grading-app"]');
/**
 * Activate show more / less for comments.
 */
const activateShowMoreLess = () => {
    const comments = gradingApp.querySelectorAll('[data-region="commenttext"]');
    comments.forEach((comment) => {
        const showMore = comment.querySelector('[data-action="showmore"]');
        const showLess = comment.querySelector('[data-action="showless"]');
        const shortText = comment.querySelector('[data-region="shorttext"]');
        const fullText = comment.querySelector('[data-region="fulltext"]');
        if (shortText.innerHTML.length != fullText.innerHTML.length) {
            showMore.classList.remove('d-none');
        }
        showMore.addEventListener('click', (event) => {
            event.preventDefault();
            shortText.classList.add('d-none');
            fullText.classList.remove('d-none');
            showMore.classList.add('d-none');
            showLess.classList.remove('d-none');
        });
        showLess.addEventListener('click', (event) => {
            event.preventDefault();
            shortText.classList.remove('d-none');
            fullText.classList.add('d-none');
            showMore.classList.remove('d-none');
            showLess.classList.add('d-none');
        });
    });
};

activateShowMoreLess();